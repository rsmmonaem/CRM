<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class CallTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'lead_detail_id',
        'phone_number',
        'call_started_at',
        'call_ended_at',
        'call_duration_seconds',
        'call_status',
        'call_summary',
        'audio_recording_path',
        'call_id',
        'call_metadata',
        'device_type',
        'device_id',
        'is_auto_dialed',
    ];

    protected $casts = [
        'call_started_at' => 'datetime',
        'call_ended_at' => 'datetime',
        'call_metadata' => 'array',
        'is_auto_dialed' => 'boolean',
    ];

    /**
     * Get the lead that owns the call tracking
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user that made the call
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lead detail associated with this call tracking
     */
    public function leadDetail()
    {
        return $this->belongsTo(LeadDetail::class);
    }

    /**
     * Get formatted call duration
     */
    public function getFormattedDurationAttribute()
    {
        $seconds = $this->call_duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Check if call is currently active
     */
    public function getIsActiveAttribute()
    {
        return in_array($this->call_status, ['initiated', 'ringing', 'answered']) &&
               $this->call_started_at &&
               !$this->call_ended_at;
    }

    /**
     * Calculate call duration automatically
     */
    public function calculateDuration()
    {
        if ($this->call_started_at && $this->call_ended_at) {
            $this->call_duration_seconds = $this->call_started_at->diffInSeconds($this->call_ended_at);
            $this->save();
        }
    }

    /**
     * Mark call as started
     */
    public function markAsStarted()
    {
        $this->update([
            'call_started_at' => now(),
            'call_status' => 'ringing'
        ]);
    }

    /**
     * Mark call as answered
     */
    public function markAsAnswered()
    {
        $this->update([
            'call_status' => 'answered'
        ]);
    }

    /**
     * Mark call as completed
     */
    public function markAsCompleted($summary = null, $nextCallDate = null)
    {
        $this->update([
            'call_ended_at' => now(),
            'call_status' => 'completed',
            'call_summary' => $summary
        ]);
        $this->calculateDuration();

        // Create or update lead detail
        $this->createOrUpdateLeadDetail($summary, $nextCallDate);
    }

    /**
     * Create or update lead detail when call is completed
     */
    public function createOrUpdateLeadDetail($summary = null, $nextCallDate = null)
    {
        $leadDetail = LeadDetail::create([
            'lead_id' => $this->lead_id,
            'call_followup_date' => now(),
            'call_followup_summary' => $summary ?: $this->call_summary,
            'next_call_date' => $nextCallDate,
            'called_at' => now(),
            'created_by' => $this->user_id,
            'assigned_to' => $this->user_id,
        ]);

        // Link the call tracking to the lead detail
        $this->update(['lead_detail_id' => $leadDetail->id]);

        return $leadDetail;
    }

    /**
     * Mark call as cancelled
     */
    public function markAsCancelled()
    {
        $this->update([
            'call_ended_at' => now(),
            'call_status' => 'cancelled'
        ]);
        $this->calculateDuration();
    }

    /**
     * Scope for active calls
     */
    public function scopeActive($query)
    {
        return $query->whereIn('call_status', ['initiated', 'ringing', 'answered'])
                    ->whereNotNull('call_started_at')
                    ->whereNull('call_ended_at');
    }

    /**
     * Scope for completed calls
     */
    public function scopeCompleted($query)
    {
        return $query->where('call_status', 'completed');
    }

    /**
     * Scope for calls by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for calls by lead
     */
    public function scopeByLead($query, $leadId)
    {
        return $query->where('lead_id', $leadId);
    }
}

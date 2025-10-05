<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class LeadDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'call_followup_date',
        'call_followup_summary',
        'next_call_date',
        'called_at',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'call_followup_date' => 'datetime',
        'next_call_date' => 'datetime',
        'called_at' => 'datetime',
    ];

    /** 🔹 Relationship: Lead belongs to Lead */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /** 🔹 Relationship: Creator */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** 🔹 Relationship: Assigned User */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** 🔹 Scope: Today's Calls (next_call_date = today and called_at IS NULL) */
    public function scopeTodaysCalls($query)
    {
        return $query
            ->whereDate('next_call_date', Carbon::today())
            ->whereNull('called_at');
    }

    /** 🔹 Scope: Pending Calls (next_call_date < today and called_at IS NULL) */
    public function scopePendingCalls($query)
    {
        return $query
            ->whereDate('next_call_date', '<', Carbon::today())
            ->whereNull('called_at');
    }

    /** 🔹 Scope: Upcoming Calls (next_call_date > today and called_at IS NULL) */
    public function scopeUpcomingCalls($query)
    {
        return $query
            ->whereDate('next_call_date', '>', Carbon::today())
            ->whereNull('called_at');
    }

    /** 🔹 Scope: Completed Calls (called_at IS NOT NULL) */
    public function scopeCompletedCalls($query)
    {
        return $query
            ->whereNotNull('called_at');
    }

    /** 🔹 Mark call as completed */
    public function markAsCalled($summary = null, $nextCallDate = null)
    {
        $this->update([
            'called_at' => Carbon::now(),
            'call_followup_summary' => $summary,
            'next_call_date' => $nextCallDate,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'location',
        'phone',
        'email',
        'service_id',
        'status_id',
        'assigned_user_id',
        'created_by',
    ];

    /**
     * Get the service for this lead
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the status for this lead
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the assigned user for this lead
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get the user who created this lead
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get lead details for this lead
     */
    public function leadDetails()
    {
        return $this->hasMany(LeadDetail::class);
    }

    /**
     * Get today's calls for this lead
     */
    public function todaysCalls()
    {
        return $this->leadDetails()->whereDate('call_followup_date', today());
    }

    /**
     * Get pending calls for this lead
     */
    public function pendingCalls()
    {
        return $this->leadDetails()->whereDate('call_followup_date', '<=', today());
    }

    /**
     * Get call trackings for this lead
     */
    public function callTrackings()
    {
        return $this->hasMany(CallTracking::class);
    }

    /**
     * Get the latest call tracking for this lead
     */
    public function latestCallTracking()
    {
        return $this->hasOne(CallTracking::class)->latest();
    }
}

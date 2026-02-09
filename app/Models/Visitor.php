<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_hash',
        'user_agent_hash',
        'path',
        'referer',
        'user_id',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the user associated with this visitor record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for getting visitors from the last 7 days.
     */
    public function scopeLast7Days($query)
    {
        return $query->where('visited_at', '>=', now()->subDays(7));
    }

    /**
     * Scope for getting visitors from the last 30 days.
     */
    public function scopeLast30Days($query)
    {
        return $query->where('visited_at', '>=', now()->subDays(30));
    }

    /**
     * Scope for getting visitors from the last 12 months.
     */
    public function scopeLast12Months($query)
    {
        return $query->where('visited_at', '>=', now()->subMonths(12));
    }

    /**
     * Scope for getting unique visitors (distinct IP + User Agent hashes).
     */
    public function scopeUnique($query)
    {
        return $query->distinct('ip_hash', 'user_agent_hash');
    }

    /**
     * Scope for getting live visitors (last 15 minutes).
     */
    public function scopeLive($query)
    {
        return $query->where('visited_at', '>=', now()->subMinutes(15));
    }
}

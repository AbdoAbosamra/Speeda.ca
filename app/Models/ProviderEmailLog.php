<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks every automated onboarding email sent to a service provider.
 *
 * @property int         $id
 * @property int         $service_provider_id
 * @property string      $email_type          One of the EMAIL_* constants in ProviderEmailJourneyService
 * @property int         $attempt_number      1 = first send, 2 = first resend, etc.
 * @property \Carbon\Carbon $sent_at
 * @property \Carbon\Carbon|null $next_send_at   When to fire the next resend (null = no more)
 * @property \Carbon\Carbon|null $completed_at   When the provider finished this step
 */
class ProviderEmailLog extends Model
{
    use HasFactory;

    protected $table = 'provider_email_logs';

    protected $fillable = [
        'service_provider_id',
        'email_type',
        'attempt_number',
        'sent_at',
        'next_send_at',
        'completed_at',
    ];

    protected $casts = [
        'sent_at'        => 'datetime',
        'next_send_at'   => 'datetime',
        'completed_at'   => 'datetime',
        'attempt_number' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /** Logs where the provider has NOT yet completed the step. */
    public function scopePending($query)
    {
        return $query->whereNull('completed_at');
    }

    /** Logs that are due for a resend right now. */
    public function scopeDueForResend($query)
    {
        return $query->whereNotNull('next_send_at')
                     ->where('next_send_at', '<=', now())
                     ->whereNull('completed_at');
    }
}

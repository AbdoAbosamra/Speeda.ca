<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the next release safe to deploy without re-sending email.
 *
 * 1. Provider onboarding reminders (photo, services, description, experience,
 *    gallery, profile complete) are retired: their scheduled resends are
 *    cancelled, any copies still waiting in the queue are removed, and their
 *    saved template overrides are dropped. Their log rows stay as history.
 *
 * 2. Send-once flags are back-filled for accounts that already exist, so an
 *    account created before this release never gets an email it was (or would
 *    already have been) sent: the client welcome email, and any review
 *    milestone (1st / 5th) the account has already passed. New accounts and
 *    future milestones keep working exactly as before.
 *
 * Every step only fills empty values, so running it again changes nothing.
 */
return new class extends Migration
{
    private const RETIRED_TYPES = ['photo', 'services', 'bio', 'experience', 'gallery', 'complete'];

    private const RETIRED_MAILABLES = [
        'AddProfilePhotoEmail', 'AddServicesEmail', 'WriteDescriptionEmail',
        'AddExperienceEmail', 'ShowcaseWorkEmail', 'ProfileCompleteEmail',
    ];

    public function up(): void
    {
        $now = now();

        // ---- 1. Retire the onboarding reminders
        if (Schema::hasTable('provider_email_logs')) {
            DB::table('provider_email_logs')
                ->whereIn('email_type', self::RETIRED_TYPES)
                ->update(['next_send_at' => null]);

            DB::table('provider_email_logs')
                ->whereIn('email_type', self::RETIRED_TYPES)
                ->whereNull('completed_at')
                ->update(['completed_at' => $now]);
        }

        if (Schema::hasTable('jobs')) {
            foreach (self::RETIRED_MAILABLES as $class) {
                DB::table('jobs')->where('payload', 'like', '%' . $class . '%')->delete();
            }
        }

        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->whereIn('key', self::RETIRED_TYPES)->delete();
        }

        // ---- 2. Back-fill send-once flags for existing accounts
        if (Schema::hasColumn('users', 'client_welcome_email_sent_at')) {
            DB::table('users')
                ->where('role', 'client')
                ->whereNull('client_welcome_email_sent_at')
                ->update(['client_welcome_email_sent_at' => $now]);
        }

        $reviewTable = Schema::hasTable('service_provider_reviews') ? 'service_provider_reviews' : (Schema::hasTable('reviews') ? 'reviews' : null);

        if ($reviewTable) {
            $this->backfillMilestones('users', 'client_id', $reviewTable, [
                1 => 'first_review_email_sent_at',
                5 => 'fifth_review_email_sent_at',
            ], $now);

            $this->backfillMilestones('service_providers', 'service_provider_id', $reviewTable, [
                1 => 'first_review_received_email_sent_at',
                5 => 'fifth_review_received_email_sent_at',
            ], $now);
        }
    }

    /**
     * Mark milestones an account has already reached (counting approved
     * reviews) as sent.
     */
    private function backfillMilestones(string $table, string $foreignKey, string $reviewTable, array $milestones, $now): void
    {
        if (! Schema::hasColumn($reviewTable, $foreignKey) || ! Schema::hasColumn($reviewTable, 'is_active')) {
            return;
        }

        foreach ($milestones as $count => $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $reached = DB::table($reviewTable)
                ->select($foreignKey)
                ->where('is_active', true)
                ->whereNotNull($foreignKey)
                ->groupBy($foreignKey)
                ->havingRaw('COUNT(*) >= ?', [$count]);

            DB::table($table)
                ->whereIn('id', $reached)
                ->whereNull($column)
                ->update([$column => $now]);
        }
    }

    public function down(): void
    {
        // Nothing to undo: the flags only record that an email is no longer due.
    }
};

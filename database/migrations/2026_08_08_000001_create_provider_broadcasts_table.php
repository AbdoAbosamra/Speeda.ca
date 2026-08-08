<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-composed broadcast emails sent to service providers.
 *
 * Two tables on purpose:
 *  - provider_broadcasts holds the copy the admin wrote, exactly once.
 *  - provider_broadcast_recipients is the per-provider delivery ledger, so a
 *    send can be resumed, retried, or audited without ever mailing the same
 *    provider twice (enforced by the unique index below).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_broadcasts', function (Blueprint $table) {
            $table->id();

            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body');
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();

            // draft    → editable, never mailed
            // queued   → send requested, jobs being dispatched
            // sending  → at least one recipient job has run
            // sent     → every recipient reached a terminal state
            $table->string('status', 20)->default('draft')->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            // Denormalised counters so the index screen never has to aggregate
            // the recipients table for every row.
            $table->unsignedInteger('recipients_total')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            $table->timestamps();
        });

        Schema::create('provider_broadcast_recipients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('provider_broadcast_id')
                ->constrained('provider_broadcasts')
                ->cascadeOnDelete();

            $table->foreignId('service_provider_id')
                ->constrained('service_providers')
                ->cascadeOnDelete();

            // Snapshot of the address at send time: if the provider later
            // changes their email, the ledger still shows where it actually went.
            $table->string('email');
            $table->string('name')->nullable();

            $table->string('status', 20)->default('pending')->index();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // The guarantee that a provider cannot receive the same broadcast
            // twice, even if the send is triggered again.
            $table->unique(['provider_broadcast_id', 'service_provider_id'], 'broadcast_provider_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_broadcast_recipients');
        Schema::dropIfExists('provider_broadcasts');
    }
};

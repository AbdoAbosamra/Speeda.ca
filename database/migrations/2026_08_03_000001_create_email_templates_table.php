<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-editable copy for the automated emails.
 *
 * The wording used to live hardcoded inside the Blade templates and the
 * Mailable subjects, so changing a single sentence required a deploy. Each row
 * here overrides the built-in default for one email; deleting the row (or
 * unticking is_active) restores the shipped copy.
 *
 * Deliberately a set of NAMED TEXT FIELDS rather than a raw HTML body: the
 * email layout is responsive and inlined for mail clients, and letting an admin
 * paste arbitrary markup would break rendering and open an injection hole. The
 * fields map onto the existing template anatomy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();

            // welcome, photo, services, ... , client_first_review
            $table->string('key')->unique();

            $table->string('subject');

            // Content blocks (all optional except headline/body).
            $table->string('badge')->nullable();          // "Step 1 of 6"
            $table->string('headline');                   // main-headline
            $table->text('lead')->nullable();             // lead-text

            $table->string('next_step_label')->nullable();
            $table->string('next_step_title')->nullable();
            $table->text('next_step_desc')->nullable();

            $table->string('why_label')->nullable();
            $table->text('why_text')->nullable();

            $table->string('cta_label')->nullable();
            $table->string('cta_subtext')->nullable();

            $table->text('quote')->nullable();

            // When false the built-in default copy is used instead.
            $table->boolean('is_active')->default(true);

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};

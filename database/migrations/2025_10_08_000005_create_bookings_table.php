<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            
            $table->string('booking_reference')->unique();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            $table->text('service_description');
            $table->text('client_requirements')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            
            $table->datetime('preferred_date');
            $table->datetime('confirmed_date')->nullable();
            $table->datetime('completed_date')->nullable();
            
            $table->string('service_address');
            $table->string('client_phone');
            $table->text('special_instructions')->nullable();
            
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer'])->nullable();
            
            $table->text('service_provider_notes')->nullable();
            $table->text('client_feedback')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('preferred_date');
            $table->index('booking_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};


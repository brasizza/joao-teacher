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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_name');
            $table->string('tagline')->nullable();
            $table->string('headline');
            $table->text('subheadline')->nullable();
            $table->string('photo_path')->nullable();
            $table->json('highlights')->nullable();
            $table->string('about_title')->nullable();
            $table->text('about_text')->nullable();
            $table->text('methodology_intro')->nullable();
            $table->text('lessons_intro')->nullable();
            $table->text('testimonials_intro')->nullable();
            $table->text('schedule_intro')->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->text('whatsapp_general_message')->nullable();
            $table->text('whatsapp_booking_message')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('email')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->unsignedSmallInteger('slot_interval_minutes')->default(30);
            $table->unsignedSmallInteger('booking_window_days')->default(14);
            $table->unsignedSmallInteger('minimum_notice_hours')->default(12);
            $table->unsignedSmallInteger('pending_hold_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

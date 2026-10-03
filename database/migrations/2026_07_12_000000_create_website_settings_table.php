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
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page', 50)->default('home');
            
            // Hero/Promotion section (for carousel)
            $table->json('hero_images')->nullable();
            
            // Page-specific content
            $table->json('content')->nullable();
            
            // Booking cards (for home page - 6 cards with title, description, image)
            $table->json('booking_cards')->nullable();
            
            // Header and Footer
            $table->json('header_data')->nullable();
            $table->json('footer_data')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};

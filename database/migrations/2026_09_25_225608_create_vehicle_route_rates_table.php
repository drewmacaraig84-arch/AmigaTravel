<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_route_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_rate_id')
                ->nullable()
                ->constrained('vehicle_rates')
                ->cascadeOnDelete();
            $table->foreignId('vehicle_brand_id')
                ->nullable()
                ->constrained('vehicle_brands')
                ->cascadeOnDelete();
            $table->foreignId('vehicle_model_id')
                ->nullable()
                ->constrained('vehicle_models')
                ->cascadeOnDelete();
            $table->string('route_key', 200)->comment('Format: origin|destination');
            $table->string('origin', 120);
            $table->string('destination', 120);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Each category can have at most one price per route
            $table->unique(['vehicle_rate_id', 'route_key'], 'vrr_rate_route_unique');
            // Each brand can have at most one price per route
            $table->unique(['vehicle_brand_id', 'route_key'], 'vrr_brand_route_unique');
            // Each model can have at most one price override per route
            $table->unique(['vehicle_model_id', 'route_key'], 'vrr_model_route_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_route_rates');
    }
};

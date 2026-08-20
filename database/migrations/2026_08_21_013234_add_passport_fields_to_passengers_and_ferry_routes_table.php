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
        Schema::table('passengers', function (Blueprint $table) {
            $table->string('passport_country', 100)->nullable()->after('id_number');
            $table->string('passport_number', 50)->nullable()->after('passport_country');
            $table->date('passport_issuance_date')->nullable()->after('passport_number');
            $table->date('passport_expiry_date')->nullable()->after('passport_issuance_date');
        });

        Schema::table('ferry_routes', function (Blueprint $table) {
            $table->boolean('is_international')->default(false)->after('mode');
        });

        // Auto-flag existing international routes
        $intlDestinations = [
            'Hong Kong', 'Tokyo (Narita)', 'Tokyo', 'Singapore', 'Seoul (Incheon)', 'Seoul',
            'Bangkok', 'Taipei', 'Kuala Lumpur', 'Sydney', 'Dubai', 'Osaka', 'Nagoya', 'Fukuoka'
        ];

        DB::table('ferry_routes')
            ->where('mode', 'airline')
            ->where(function ($q) use ($intlDestinations) {
                $q->whereIn('origin', $intlDestinations)
                  ->orWhereIn('destination', $intlDestinations);
            })
            ->update(['is_international' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropColumn([
                'passport_country',
                'passport_number',
                'passport_issuance_date',
                'passport_expiry_date',
            ]);
        });

        Schema::table('ferry_routes', function (Blueprint $table) {
            $table->dropColumn('is_international');
        });
    }
};


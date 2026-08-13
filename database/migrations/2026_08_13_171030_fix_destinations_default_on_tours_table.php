<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make `destinations` nullable with an empty-string default so that
     * tours created without specifying destinations do not violate the
     * NOT NULL constraint on the Railway (production) database.
     */
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->string('destinations')->nullable()->default('')->change();
        });

        // Patch any existing NULLs left over before the fix
        DB::table('tours')->whereNull('destinations')->update(['destinations' => '']);
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->string('destinations')->nullable(false)->default(null)->change();
        });
    }
};

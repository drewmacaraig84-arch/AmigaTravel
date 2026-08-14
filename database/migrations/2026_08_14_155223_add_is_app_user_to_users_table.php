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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_app_user')->default(false)->after('api_token');
        });

        // Backfill existing users who have used the app.
        // We consider them app users if they have an api_token or any sanctum tokens.
        \Illuminate\Support\Facades\DB::table('users')
            ->whereNotNull('api_token')
            ->orWhereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('personal_access_tokens')
                      ->whereColumn('personal_access_tokens.tokenable_id', 'users.id')
                      ->where('personal_access_tokens.tokenable_type', \App\Models\User::class);
            })
            ->update(['is_app_user' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_app_user');
        });
    }
};

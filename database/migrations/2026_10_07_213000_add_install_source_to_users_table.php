<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'install_source')) {
                $table->string('install_source', 50)->nullable()->after('is_app_user');
            }
            if (!Schema::hasColumn('users', 'installer_package')) {
                $table->string('installer_package', 255)->nullable()->after('install_source');
            }
        });

        // 1. Tag Huawei / AppGallery accounts
        DB::table('users')
            ->where(function ($q) {
                $q->where('email', 'like', '%huawei%')
                  ->orWhere('name', 'like', '%huawei%');
            })
            ->update([
                'install_source' => 'app_gallery',
                'installer_package' => 'com.huawei.appmarket',
            ]);

        // 2. Tag Google Play Store audit / test accounts
        DB::table('users')
            ->where(function ($q) {
                $q->where('email', 'like', '%appaudit%')
                  ->orWhere('email', 'like', '%playstore%')
                  ->orWhere('name', 'like', '%playstore%');
            })
            ->whereNull('install_source')
            ->update([
                'install_source' => 'play_store',
                'installer_package' => 'com.android.vending',
            ]);

        // 3. Scan login history User-Agents for Huawei / HMS devices
        $huaweiUserIds = DB::table('user_login_histories')
            ->where(function ($q) {
                $q->where('user_agent', 'like', '%huawei%')
                  ->orWhere('user_agent', 'like', '%hms%')
                  ->orWhere('user_agent', 'like', '%harmony%')
                  ->orWhere('user_agent', 'like', '%honor%');
            })
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();

        if ($huaweiUserIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $huaweiUserIds)
                ->whereNull('install_source')
                ->update([
                    'install_source' => 'app_gallery',
                    'installer_package' => 'com.huawei.appmarket',
                ]);
        }

        // 4. Scan login history User-Agents for iOS devices
        $iosUserIds = DB::table('user_login_histories')
            ->where(function ($q) {
                $q->where('user_agent', 'like', '%iphone%')
                  ->orWhere('user_agent', 'like', '%ipad%')
                  ->orWhere('user_agent', 'like', '%cfnetwork%');
            })
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();

        if ($iosUserIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $iosUserIds)
                ->whereNull('install_source')
                ->update([
                    'install_source' => 'app_store',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'installer_package')) {
                $table->dropColumn('installer_package');
            }
            if (Schema::hasColumn('users', 'install_source')) {
                $table->dropColumn('install_source');
            }
        });
    }
};

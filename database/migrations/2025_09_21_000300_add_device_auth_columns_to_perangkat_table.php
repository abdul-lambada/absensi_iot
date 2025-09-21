<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            $table->string('device_uid', 191)->unique()->after('status_perangkat');
            $table->string('api_key', 191)->after('device_uid');
        });
    }

    public function down(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            $table->dropColumn(['device_uid', 'api_key']);
        });
    }
};
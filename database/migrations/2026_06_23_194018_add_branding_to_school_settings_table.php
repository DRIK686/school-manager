<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('sidebar_color')->default('#0f766e')->after('theme_color');
            $table->string('accent_color')->default('#14b8a6')->after('sidebar_color');
            $table->string('motto')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['sidebar_color', 'accent_color', 'motto']);
        });
    }
};

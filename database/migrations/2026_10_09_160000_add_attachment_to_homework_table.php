<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homework', function (Blueprint $table) {
            if (! Schema::hasColumn('homework', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('description');
            }
            if (! Schema::hasColumn('homework', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homework', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name']);
        });
    }
};

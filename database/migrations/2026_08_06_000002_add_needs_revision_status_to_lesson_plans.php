<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE lesson_plans MODIFY status ENUM('draft','submitted','needs_revision','reviewed') NOT NULL DEFAULT 'draft'");
    }
    public function down(): void {
        DB::statement("ALTER TABLE lesson_plans MODIFY status ENUM('draft','submitted','reviewed') NOT NULL DEFAULT 'draft'");
    }
};

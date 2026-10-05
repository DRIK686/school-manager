<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // End of Term 1, Mock, BECE Trial
            $table->string('description')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('exam_types'); }
};

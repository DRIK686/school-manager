<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->unsignedTinyInteger('week_no');
            $table->string('topic');
            $table->text('objectives')->nullable();
            $table->text('activities')->nullable();
            $table->text('resources')->nullable();
            $table->enum('status', ['draft','submitted','reviewed'])->default('draft');
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lesson_plans'); }
};

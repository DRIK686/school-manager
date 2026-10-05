<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_type_id')->constrained('exam_types')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->date('exam_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room')->nullable();
            $table->decimal('max_marks', 5, 2)->default(100);
            $table->decimal('passing_marks', 5, 2)->default(50);
            $table->timestamps();
            $table->unique(['exam_type_id','class_id','subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exam_schedules'); }
};

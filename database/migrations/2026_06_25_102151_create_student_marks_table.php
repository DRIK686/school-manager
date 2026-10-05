<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->cascadeOnDelete();
            $table->decimal('marks_obtained', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_absent')->default(false);
            $table->timestamps();
            $table->unique(['student_id','exam_schedule_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('student_marks'); }
};

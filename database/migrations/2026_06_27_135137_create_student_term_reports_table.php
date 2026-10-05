<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_term_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_type_id')->constrained('exam_types')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('conduct')->nullable();
            $table->string('talent_interest')->nullable();
            $table->text('teacher_remarks')->nullable();
            $table->string('promoted_to')->nullable();
            $table->unsignedSmallInteger('attendance_present')->nullable();
            $table->unsignedSmallInteger('attendance_total')->nullable();
            $table->date('vacation_date')->nullable();
            $table->date('reopening_date')->nullable();
            $table->string('class_teacher')->nullable();
            $table->string('principal')->nullable();
            $table->unique(['student_id','exam_type_id','academic_year_id'], 'student_term_unique');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('student_term_reports'); }
};

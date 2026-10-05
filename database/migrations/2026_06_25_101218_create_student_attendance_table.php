<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present','absent','late','holiday'])->default('present');
            $table->string('remarks')->nullable();
            $table->foreignId('marked_by')->constrained('users');
            $table->timestamps();
            $table->unique(['student_id','date']);
        });
    }
    public function down(): void { Schema::dropIfExists('student_attendance'); }
};

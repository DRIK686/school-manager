<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('section_id')->nullable()->constrained();
            $table->string('roll_no')->nullable();
            $table->enum('status', ['promoted','repeated','graduated','withdrawn'])->default('promoted');
            $table->timestamps();
            $table->unique(['student_id','academic_year_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('student_enrollments'); }
};

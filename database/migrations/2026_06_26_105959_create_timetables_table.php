<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');  // 1=Mon ... 5=Fri
            $table->foreignId('slot_id')->constrained('timetable_slots')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('custom_label')->nullable(); // for opening activities: "Silent Reading", "Worship" etc
            $table->timestamps();
            $table->unique(['academic_year_id','class_id','section_id','day_of_week','slot_id'], 'timetable_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('timetables'); }
};

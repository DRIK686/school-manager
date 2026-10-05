<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promotion_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_academic_year_id')->constrained('academic_years');
            $table->foreignId('from_class_id')->constrained('classes');
            $table->foreignId('from_section_id')->nullable()->constrained('sections');
            $table->enum('recommended_action', ['promote','repeat','graduate','withdraw']);
            $table->foreignId('recommended_class_id')->nullable()->constrained('classes');
            $table->foreignId('recommended_section_id')->nullable()->constrained('sections');
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending','actioned'])->default('pending');
            $table->timestamps();
            $table->unique(['student_id','from_academic_year_id'], 'promo_rec_student_year_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('promotion_recommendations'); }
};

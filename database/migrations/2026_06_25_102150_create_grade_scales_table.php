<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('grade'); // A1, B2, C4, etc
            $table->decimal('min_mark', 5, 2);
            $table->decimal('max_mark', 5, 2);
            $table->integer('points')->default(1);
            $table->string('remark')->nullable(); // Excellent, Good, Pass, Fail
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('grade_scales'); }
};

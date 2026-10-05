<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->decimal('sba_score', 5, 2)->nullable()->after('marks_obtained');
            $table->decimal('exam_score', 5, 2)->nullable()->after('sba_score');
            $table->decimal('class_average', 5, 2)->nullable()->after('exam_score');
        });
    }
    public function down(): void {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropColumn(['sba_score','exam_score','class_average']);
        });
    }
};

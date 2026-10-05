<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('document_type'); // birth_certificate, report_card, etc
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('student_documents'); }
};

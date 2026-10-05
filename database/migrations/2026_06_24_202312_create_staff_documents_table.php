<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('staff_documents'); }
};

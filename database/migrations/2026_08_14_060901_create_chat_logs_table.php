<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('ip_address')->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->boolean('was_grounded')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('chat_logs'); }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('pin');           // bcrypt hashed
            $table->string('plain_pin', 8); // stored temporarily for printing, cleared after first login
            $table->boolean('is_first_login')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('student_pins'); }
};

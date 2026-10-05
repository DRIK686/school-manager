<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_id')->unique();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('qualification')->nullable();
            $table->string('experience')->nullable();
            $table->enum('gender', ['male','female','other'])->default('male');
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->enum('marital_status', ['single','married','divorced','widowed'])->default('single');
            $table->string('phone')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('address')->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('staff_profiles'); }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('fine_paid', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->date('payment_date');
            $table->enum('payment_mode', ['cash', 'bank', 'mobile_money', 'cheque'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('collected_by')->constrained('users');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('fee_payments'); }
};

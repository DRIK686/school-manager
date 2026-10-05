<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fee_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 8, 2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('fee_discounts'); }
};

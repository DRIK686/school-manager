<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_budgets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('academic_year_id');
            $t->foreignId('finance_category_id')->constrained('finance_categories');
            $t->decimal('amount', 14, 2)->default(0);
            $t->timestamps();
            $t->unique(['academic_year_id', 'finance_category_id']);
        });
        Schema::table('school_settings', function (Blueprint $t) {
            $t->decimal('finance_approval_threshold', 14, 2)->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $t) {
            $t->dropColumn('finance_approval_threshold');
        });
        Schema::dropIfExists('finance_budgets');
    }
};

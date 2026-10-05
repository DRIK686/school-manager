<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('money_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->string('type', 20)->default('cash'); // cash | bank | mobile_money
            $t->decimal('opening_balance', 14, 2)->default(0);
            $t->date('opening_date')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('finance_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->string('type', 10); // expense | income
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('finance_transactions', function (Blueprint $t) {
            $t->id();
            $t->string('type', 10); // expense | income
            $t->foreignId('money_account_id')->constrained('money_accounts');
            $t->foreignId('finance_category_id')->constrained('finance_categories');
            $t->date('txn_date');
            $t->decimal('amount', 14, 2);
            $t->string('payee', 150)->nullable();
            $t->string('reference_no', 100)->nullable();
            $t->text('description')->nullable();
            $t->string('attachment_path')->nullable();
            $t->string('status', 10)->default('active');
            $t->timestamp('voided_at')->nullable();
            $t->unsignedBigInteger('voided_by')->nullable();
            $t->string('void_reason')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index(['type', 'txn_date']);
            $t->index('status');
        });

        Schema::create('account_transfers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('from_account_id')->constrained('money_accounts');
            $t->foreignId('to_account_id')->constrained('money_accounts');
            $t->decimal('amount', 14, 2);
            $t->date('transfer_date');
            $t->string('reference_no', 100)->nullable();
            $t->string('note')->nullable();
            $t->string('status', 10)->default('active');
            $t->timestamp('voided_at')->nullable();
            $t->unsignedBigInteger('voided_by')->nullable();
            $t->string('void_reason')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::table('fee_payments', function (Blueprint $t) {
            $t->unsignedBigInteger('money_account_id')->nullable()->after('payment_mode');
            $t->string('status', 10)->default('active')->after('money_account_id');
            $t->timestamp('voided_at')->nullable();
            $t->unsignedBigInteger('voided_by')->nullable();
            $t->string('void_reason')->nullable();
            $t->index('status');
        });

        // Default accounts
        $now = now();
        $ids = [];
        foreach ([['Cash on Hand', 'cash'], ['Bank Account', 'bank'], ['Mobile Money', 'mobile_money']] as [$n, $ty]) {
            $ids[$ty] = DB::table('money_accounts')->insertGetId([
                'name' => $n, 'type' => $ty, 'opening_balance' => 0, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // Default categories
        $expense = ['Salaries & Wages', 'Utilities (Electricity & Water)', 'Teaching & Learning Materials',
            'Repairs & Maintenance', 'Transport & Fuel', 'Food & Catering', 'Stationery & Office',
            'Communication & Internet', 'Events & Activities', 'Bank Charges', 'Other Expenses'];
        $income = ['Donations & Grants', 'Uniform & Book Sales', 'Canteen Sales', 'Rentals', 'Other Income'];
        foreach ($expense as $n) {
            DB::table('finance_categories')->insert(['name' => $n, 'type' => 'expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ($income as $n) {
            DB::table('finance_categories')->insert(['name' => $n, 'type' => 'income', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Backfill existing fee payments to an account by payment mode
        DB::table('fee_payments')->where('payment_mode', 'cash')->update(['money_account_id' => $ids['cash']]);
        DB::table('fee_payments')->whereIn('payment_mode', ['bank', 'cheque'])->update(['money_account_id' => $ids['bank']]);
        DB::table('fee_payments')->where('payment_mode', 'mobile_money')->update(['money_account_id' => $ids['mobile_money']]);
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $t) {
            $t->dropIndex(['status']);
            $t->dropColumn(['money_account_id', 'status', 'voided_at', 'voided_by', 'void_reason']);
        });
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_categories');
        Schema::dropIfExists('money_accounts');
    }
};

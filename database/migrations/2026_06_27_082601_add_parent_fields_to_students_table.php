<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->string('guardian_name')->nullable()->after('previous_class');
            $table->string('guardian_relationship')->nullable()->after('guardian_name');
            $table->string('guardian_phone')->nullable()->after('guardian_relationship');
            $table->string('guardian_email')->nullable()->after('guardian_phone');
            $table->string('guardian_occupation')->nullable()->after('guardian_email');
        });
    }
    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['guardian_name','guardian_relationship','guardian_phone','guardian_email','guardian_occupation']);
        });
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'guardian_name'))
                $table->dropColumn(['guardian_name','guardian_relationship','guardian_phone','guardian_email','guardian_occupation']);
        });
    }
    public function down(): void {}
};

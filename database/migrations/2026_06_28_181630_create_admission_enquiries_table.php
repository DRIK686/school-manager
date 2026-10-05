<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('admission_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('child_name');
            $table->date('dob')->nullable();
            $table->string('grade');
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->string('parent_email')->nullable();
            $table->text('message')->nullable();
            $table->enum('status',['new','contacted','enrolled','declined'])->default('new');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('admission_enquiries'); }
};

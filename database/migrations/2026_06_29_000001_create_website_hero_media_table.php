<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('website_hero_media', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->enum('type', ['image','video'])->default('image');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_hero_media'); }
};

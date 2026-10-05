<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('website_items', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // teacher, gallery, testimonial, program, feature
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('badge')->nullable(); // e.g. "Ages 3-5", "⭐⭐⭐⭐⭐"
            $table->string('link')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable(); // extra fields
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_items'); }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('slot_no');   // 1-11 + breaks
            $table->string('label');                   // "Period 1", "Break", "Lunch"
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_break')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('timetable_slots'); }
};

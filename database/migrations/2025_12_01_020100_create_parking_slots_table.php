<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_area_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('slot_number');
            $table->enum('status', ['vacant', 'reserved', 'occupied'])->default('vacant');
            $table->timestamps();

            $table->unique(['parking_area_id', 'slot_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_slots');
    }
};

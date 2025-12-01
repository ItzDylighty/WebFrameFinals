<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('parking_slot_id')->nullable()->constrained('parking_slots')->nullOnDelete()->after('parking_no');
            $table->timestamp('checked_in_at')->nullable()->after('reservation_time');
            $table->timestamp('checked_out_at')->nullable()->after('checked_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['parking_slot_id']);
            $table->dropColumn(['parking_slot_id', 'checked_in_at', 'checked_out_at']);
        });
    }
};

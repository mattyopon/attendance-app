<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_correction_request_rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stamp_correction_request_id')
                ->constrained('stamp_correction_requests', 'id', 'scr_rests_scr_id_foreign')
                ->cascadeOnDelete();
            $table->datetime('rest_start');
            $table->datetime('rest_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_correction_request_rests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_id')->constrained('rekaps')->cascadeOnDelete();
            $table->foreignId('pekerja_id')->nullable()->constrained('pekerjas')->nullOnDelete();
            $table->string('nama');
            $table->string('jabatan');
            $table->decimal('hari', 4, 1);
            $table->unsignedInteger('gaji_harian');
            $table->unsignedBigInteger('total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_items');
    }
};

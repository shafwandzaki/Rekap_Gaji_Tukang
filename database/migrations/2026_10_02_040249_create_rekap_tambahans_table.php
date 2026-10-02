<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_tambahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_id')->constrained('rekaps')->cascadeOnDelete();
            $table->string('keterangan');
            $table->unsignedBigInteger('nominal');
            $table->enum('tipe', ['tambah', 'potong'])->default('tambah');
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('rekap_tambahans');
    }
};

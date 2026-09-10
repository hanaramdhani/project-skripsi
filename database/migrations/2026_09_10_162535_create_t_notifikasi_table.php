<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTNotifikasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_notifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('tipe', 50)->default('stok_minus');
            $table->string('kd_barang', 50)->nullable();
            $table->string('no_transaksi', 50)->nullable();
            $table->string('judul', 150);
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_notifikasi');
    }
}

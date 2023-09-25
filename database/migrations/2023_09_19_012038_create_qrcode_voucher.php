<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qrcode_voucher', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_guest_list');
            $table->string('code', 255)->unique();
            $table->integer('status')->default(0);
            $table->bigInteger('nominal')->nullable();
            $table->string('remark')->nullable();
            $table->date('expired_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qrcode_voucher');
    }
};

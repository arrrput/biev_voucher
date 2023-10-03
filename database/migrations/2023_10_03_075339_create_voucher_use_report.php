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
        Schema::create('voucher_use_report', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_guest_list');
            $table->string('name');
            $table->string('position'); 
            $table->bigInteger('nominal');
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_use_report');
    }
};

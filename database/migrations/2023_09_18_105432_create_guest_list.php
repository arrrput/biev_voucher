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
        Schema::create('guest_list', function (Blueprint $table) {
            $table->id();
            $table->string('shift_pattern');
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('position')->nullable();
            $table->string('bento_box')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_list');
    }
};

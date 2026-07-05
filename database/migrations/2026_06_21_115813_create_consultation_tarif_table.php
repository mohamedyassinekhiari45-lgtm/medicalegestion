<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultation_tarif', function (Blueprint $table) {
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignId('tarif_id')->constrained()->onDelete('cascade');
            $table->primary(['consultation_id', 'tarif_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultation_tarif');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('code_nomenclature')->unique();
            $table->string('acte');
            $table->decimal('montant_ht', 10, 2);
            $table->decimal('tva', 5, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tarifs');
    }
};

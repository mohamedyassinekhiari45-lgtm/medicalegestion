<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dossier_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_medical_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medecin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('autorise_par')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['dossier_medical_id', 'medecin_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dossier_authorizations');
    }
};

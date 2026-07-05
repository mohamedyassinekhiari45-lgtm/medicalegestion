<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('share_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_medical_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_medecin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_medecin_id')->constrained('users')->cascadeOnDelete();
            $table->json('notes_partagees')->nullable();
            $table->json('documents_partagees')->nullable();
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('share_requests');
    }
};

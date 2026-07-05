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
        Schema::create('documents_medicaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_medical_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['ordonnance', 'bilan', 'radio', 'compte_rendu', 'autre'])->default('autre');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('fichier')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents_medicaux');
    }
};

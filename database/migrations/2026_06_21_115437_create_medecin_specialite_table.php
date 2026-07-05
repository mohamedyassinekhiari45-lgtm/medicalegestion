<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medecin_specialite', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('specialite_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'specialite_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('medecin_specialite');
    }
};

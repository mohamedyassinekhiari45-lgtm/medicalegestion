<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE rendez_vous MODIFY COLUMN statut ENUM('planifie', 'confirme', 'en_cours', 'annule', 'termine') NOT NULL DEFAULT 'planifie'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE rendez_vous MODIFY COLUMN statut ENUM('planifie', 'confirme', 'annule', 'termine') NOT NULL DEFAULT 'planifie'");
    }
};

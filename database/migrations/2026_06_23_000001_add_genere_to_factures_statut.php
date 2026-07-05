<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE factures MODIFY COLUMN statut_paiement ENUM('genere','impaye','partiel','paye') NOT NULL DEFAULT 'genere'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE factures MODIFY COLUMN statut_paiement ENUM('impaye','partiel','paye') NOT NULL DEFAULT 'impaye'");
    }
};

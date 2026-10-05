<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('share_requests')) {
            return;
        }

        DB::statement("ALTER TABLE `share_requests`
            MODIFY `statut` ENUM('en_attente','acceptee','refusee','annulee')
            NOT NULL DEFAULT 'en_attente'");
    }

    public function down()
    {
        if (!Schema::hasTable('share_requests')) {
            return;
        }

        DB::table('share_requests')->where('statut', 'annulee')->update(['statut' => 'refusee']);

        DB::statement("ALTER TABLE `share_requests`
            MODIFY `statut` ENUM('en_attente','acceptee','refusee')
            NOT NULL DEFAULT 'en_attente'");
    }
};
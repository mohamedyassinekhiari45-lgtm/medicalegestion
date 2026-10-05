<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * L'autorisation porte desormais sur un perimetre : les notes seules,
     * les documents seuls, ou les deux. Les lignes existantes ont ete creees
     * avant l'introduction du perimetre : on les considere comme "les deux".
     */
    public function up(): void
    {
        Schema::table('dossier_authorizations', function (Blueprint $table) {
            $table->boolean('partage_notes')->default(true)->after('autorise_par');
            $table->boolean('partage_documents')->default(true)->after('partage_notes');
        });
    }

    public function down(): void
    {
        Schema::table('dossier_authorizations', function (Blueprint $table) {
            $table->dropColumn(['partage_notes', 'partage_documents']);
        });
    }
};
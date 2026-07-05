<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('receptionniste')->after('password');
            $table->string('telephone')->nullable()->after('role');
            $table->string('prenom')->nullable()->after('name');
            $table->boolean('statut')->default(true)->after('telephone');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'telephone', 'prenom', 'statut']);
        });
    }
};

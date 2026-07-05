<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('share_requests', function (Blueprint $table) {
            $table->foreignId('requester_id')->nullable()->after('to_medecin_id')->constrained('users')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('share_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requester_id');
        });
    }
};

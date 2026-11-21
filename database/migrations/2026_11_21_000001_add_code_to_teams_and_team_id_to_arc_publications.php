<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('id');
        });

        Schema::table('arc_publications', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('arc_publications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};

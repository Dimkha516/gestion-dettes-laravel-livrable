<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dettes', function (Blueprint $table) {
            // $table->date('dateEcheance')->nullable();
            $table->date('dateEcheance')->nullable()->change();
        });

        // Mettre à jour les enregistrements existants avec une date par défaut
        DB::statement("UPDATE dettes SET dateEcheance = CURDATE() WHERE dateEcheance IS NULL");

        Schema::table('dettes', function (Blueprint $table) {
            $table->date('dateEcheance')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettes', function (Blueprint $table) {
            $table->dropColumn('dateEcheance');
        });
    }
};

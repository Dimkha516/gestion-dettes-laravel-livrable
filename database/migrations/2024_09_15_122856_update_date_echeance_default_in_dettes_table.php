<?php

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
            $table->date('dateEcheance')->nullable()->change();
        });

        // Mise à jour des enregistrements existants avec une date d'échéance par défaut (3 jours après la création)
        DB::statement("UPDATE dettes SET dateEcheance = DATE_ADD(created_at, INTERVAL 3 DAY) WHERE dateEcheance IS NULL");

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
            //
        });
    }
};

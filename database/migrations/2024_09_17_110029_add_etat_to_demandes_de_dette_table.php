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
        Schema::table('demandes_de_dette', function (Blueprint $table) {
            $table->enum('etat', ['encours', 'annule', 'valide'])
                ->default('encours')
                ->after('montant_total'); // Ajoute la colonne après `montant_total`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_de_dette', function (Blueprint $table) {
            $table->dropColumn('etat');

        });
    }
};

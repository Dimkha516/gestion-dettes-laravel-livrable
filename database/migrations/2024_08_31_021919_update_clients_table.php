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
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['nom', 'prenom']); // Supprimer les colonnes `nom` et `prenom`
            $table->string('surname')->unique()->after('id'); // Ajouter la colonne `surname` avec la contrainte `unique`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('nom')->after('id');
            $table->string('prenom')->after('nom');
            $table->dropUnique(['surname']);
            $table->dropColumn('surname');
        });
    }
};

<?php

namespace App\Console\Commands;

use App\Models\DemandeDeDette;
use Illuminate\Console\Command;

class UpdateOldDebtRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-old-debt-requests';
    protected $description = "Mettre à jour les anciennes demandes de dettes avec l\'état encours";



    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        // Mettre à jour toutes les anciennes demandes avec l'état "encours"
        DemandeDeDette::whereNull('etat')->update(['etat' => DemandeDeDette::ETAT_ENCOURS]);

        $this->info('Toutes les anciennes demandes de dette ont été mises à jour.');
    }
}

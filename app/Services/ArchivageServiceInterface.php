<?php
namespace App\Services;

interface ArchivageServiceInterface
{
    public function archiverDette($dette);
    public function supprimerDette($detteId);
}

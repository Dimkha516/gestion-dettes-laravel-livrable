<?php
namespace App\Repositories;

interface DetteArchivageInterface
{
    public function getAllArchivedDebts($filters = []);
}

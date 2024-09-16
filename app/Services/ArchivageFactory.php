<?php
namespace App\Services;

class ArchivageFactory
{
    public static function create()
    {
        if (config('app.archive_driver') === 'firebase') {
            return new FirebaseArchivageService();
        }

        return new MongoArchivageService();
    }
}

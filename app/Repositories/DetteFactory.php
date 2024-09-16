<?php
namespace App\Repositories;

class DetteFactory
{
    public static function create()
    {
        if (config('app.archive_driver') === 'firebase') {
            return new FirebaseDetteArchivageService();
        }

        return new MongoDetteArchivageService();
    }
}

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(App\Post::class, function (Faker $faker) {
    return [
        'result' => random_int(0,1),
        'files' => '{"2":"BAZA.NTX","3":"BAZV.NTX","4":"DISP.DBF"}',
        'error' => 'Помилка запуску скрипта https://www.vopas.com.ua/module/vD4lsIqNd9.php -> No file',
        'category_id' => random_int(1,3),
        'alias' => $faker->randomElement($array = array (
            'city',
            'free',
            'reg',
            'ftp',
            'update',
            'upload'
        )),
        'station_id' => random_int(1,4),
        'created_at' => $faker->dateTimeBetween($startDate = '-10 hours', $endDate = 'now', $timezone = null),  //-1 days -10 hours
    ];
});

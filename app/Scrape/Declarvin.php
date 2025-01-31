<?php

namespace Scrape;

use Base;
use Web;

class Declarvin extends Web
{
    const API_URL = 'https://fakerapi.it/api/v2/persons';

    public function retrieveInfo($identifiant)
    {
        $url = self::API_URL.'?'.http_build_query(['_quantity' => 1, '_locale' => 'fr_FR']);
        $json = self::request($url);

        $data = (json_decode($json['body'])->data[0]);

        return [
            'RAISON_SOCIALE' => 'SCEA '.$data->firstname.' '.$data->lastname,
            'CVI' => rand(1000000000, 9000000000),
            'NUMCIVP' => $identifiant,
            'EMAIL' => $data->email,
            'TELEPHONE' => $data->phone
        ];
    }
}

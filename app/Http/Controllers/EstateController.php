<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EstateController extends Controller
{
    const WEBFLOWKEY = '7ebc1841deaffdde6003695eaeda6d283a616935f5b0d9c073e0901b244ed6a2';
    const SITEID = '649ac9d90cc8b35d7b157926';
    const ESTATEID = '649ac9d90cc8b35d7b15793b';
    const PHOTOID = '64a1382d709a48bfa6ad14cd';



    public function __invoke()
    {

        $estateData = $this->getEstateData();

        foreach ($estateData as $data) {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => 'Bearer ' . self::WEBFLOWKEY,
            ])->withBody(json_encode(['fields' => $data]))
                ->post('https://api.webflow.com/collections/' . self::ESTATEID . '/items?live=true');

            if ($response->status() != 200) {
                dd($response->json());
            }
        }

        return response()->json('done');
    }


    public function getEstateData()
    {
        $baseURL = 'https://api.whise.eu/';
        $user = 'vincent@studio27.be';
        $pass = 'Studio27*WHISE';

        $token = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->withBody(json_encode([
            'username' => $user,
            'password' => $pass,
        ]))->post($baseURL . 'token')
            ->json()['token'];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'authorization' => 'Bearer ' . $token,
        ])->withBody(json_encode([
            'Page' => [
                'Limit' => 100,
                'Offset' => 0
            ]
        ]))->post($baseURL . 'v1/estates/list');

        $estates = $response->json()['estates'];

        foreach ($estates as $estate) {
            $str = $estate['sms'][0]['content'] ?? '';
            $substr = Str::substr($str, 0, 100);
            $oneLine = str_replace(["\n\r", "\n", "\r", PHP_EOL], '', $substr);

            foreach ($estate['pictures'] as $picture) {
                $photoData[] = [
                    'name' => 'Estate Photo',
                    'slug' => 'estate-photo' . now()->timestamp,
                    'photolink' => $picture['urlLarge'],
                    'photoxxl' => $picture['urlXXL'],
                    'photos' => $picture['urlSmall'],
                    '_archived' => false,
                    '_draft' => false,
                ];

                sleep(1);
            }

            foreach ($photoData as $data) {
                $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'authorization' => 'Bearer ' . self::WEBFLOWKEY,
                ])->withBody(json_encode(['fields' => $data]))
                    ->post('https://api.webflow.com/collections/' . self::PHOTOID . '/items?live=true');


                if ($response->status() != 200) {
                    dd($response->json());
                } else {
                    $ids[] = $response->json()['_id'];
                }
            }

            $estateData[] = [
                'name' => $estate['name'],
                'slug' => Str::slug($estate['name']),
                'postcode-gemeente' => $estate['zip'] . ' ' . $estate['city'],
                'hoofdafbeelding-link' => $estate['pictures'][0]['urlLarge'] ?? null,
                'beknopte-omschrijving-max-100-karakters' => $oneLine,
                'vraagprijs-uitgedrukt-in-eu-2' => (string) $estate['price']  ?? '',
                'woonoppervlakte-uitgedrukt-in-m2' => $estate['area']  ?? $estate['maxArea'] ?? null,
                'perceeloppervlakte-uitgedrukt-in-m2' => $estate['groundArea']  ?? null,
                'aantal-slaapkamers' => $estate['rooms']  ?? null,
                'uitgebreide-omschrijving' => $estate['shortDescription'][0]['content']  ?? null,
                'extra-afbeeldingen-link' => $ids,
                '_archived' => false,
                '_draft' => false,
            ];
        }

        return $estateData;
    }
}

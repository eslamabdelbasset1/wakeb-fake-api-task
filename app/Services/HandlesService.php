<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
class HandlesService
{
    private $baseUrl = 'https://test.api.amadeus.com/v1';


    private $clientId;
    private $clientSecret;
    private $accessTokenCacheKey = 'amadeus_access_token';

    public function __construct()
    {
        $this->clientId = config('services.amadeus.client_id');
        $this->clientSecret = config('services.amadeus.client_secret');
    }

    public function getHotelsData()
    {
        $client = new Client();
//  ''https://test.api.amadeus.com/v1/reference-data/locations/hotels/by-city?cityCode=PAR
//         dd($this->getAccessToken()['access_token']);
        $response = $client->get($this->baseUrl . '/reference-data/locations/hotels/by-city?cityCode=LON', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()['access_token'],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

//        dd($data['data']);

        return array_map(function ($hotel) {
//            dd($hotel);
            return ([
                'name' => $hotel['name'],
                'address' => $hotel['address'],
                'id' => $hotel['dupeId'],
                'price' => $this->generateRandomPrice(),
                'rate' => $this->generateRandomRating(),
                'discount' => $this->generateRandomDiscount()
            ]);
        }, $data['data']);
    }

    private function getAccessToken()
    {
        // Check if the token is still valid
        if ($this->tokenIsNotExpired()) {
            return Cache::get($this->accessTokenCacheKey);
        }


        // If the token is expired or not present, obtain a new one
        $response = Http::asForm()->post('https://test.api.amadeus.com/v1/security/oauth2/token',
            [
                "grant_type" => "client_credentials",
                "client_id" => $this->clientId,
                "client_secret" => $this->clientSecret
            ]);

        return $response->json();

        $data = $response->json();

        // Check if the 'access_token' key exists in the response
        if (isset($data['access_token'])) {
            // Cache the new access token with its expiration time
            $expiresIn = $data['expires_in'];
            Cache::put($this->accessTokenCacheKey, $data['access_token'], now()->addSeconds($expiresIn - 60));

            return $data['access_token'];
        } else {
            // Handle the case where 'access_token' key is missing in the response
            // Log an error, throw an exception, or implement a fallback strategy
            // depending on your application's requirements
            return null;
        }
    }

    private function tokenIsNotExpired()
    {
        // Check if the token exists and is not expired
        return Cache::has($this->accessTokenCacheKey);
    }



    function generateRandomPrice($minPrice = 50, $maxPrice = 500) {
        // Generate a random price within the specified range
        $randomPrice = rand($minPrice * 100, $maxPrice * 100) / 100; // To have two decimal places

        return number_format($randomPrice, 2); // Format the number with two decimal places
    }


    function generateRandomRating($minRating = 3.0, $maxRating = 5.0) {
        // Generate a random rating within the specified range
        $randomRating = rand($minRating * 10, $maxRating * 10) / 10; // To have one decimal place

        return $randomRating;
    }


    function generateRandomDiscount($minPercentage = 5, $maxPercentage = 20) {
        // Ensure that the minimum percentage is not greater than the maximum
        if ($minPercentage > $maxPercentage) {
            list($minPercentage, $maxPercentage) = array($maxPercentage, $minPercentage);
        }

        // Generate a random discount percentage within the specified range
        $randomDiscount = mt_rand($minPercentage * 10, $maxPercentage * 10) / 10;

        return $randomDiscount;
    }
}

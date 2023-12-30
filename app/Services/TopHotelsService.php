<?php

namespace App\Services;

use App\Http\Resources\HotelResource;
use Illuminate\Support\Facades\Http;

class TopHotelsService extends HandlesService
{
//    public static function getHotels($from, $to, $city, $adultsCount)
//    {
//        $response = Http::get('https://tophotelsapi.com/hotels', [
//            'from' => $from,
//            'to' => $to,
//            'city' => $city,
//            'adultsCount' => $adultsCount,
//        ]);
//
//        if ($response->successful()) {
//            $topHotelsData = $response->json();
//
//            return self::transformData($topHotelsData);
//        }
//
//        // Handle API request failure, return an empty array or throw an exception
//        return [];
//    }

    public function transformData()
    {
        $data = $this->getHotelsData();
        return collect($data)->map(function ($hotel) {
            return new HotelResource([
                'provider' => 'TopHotels',
                'hotelName' => $hotel['name'],
                'price' => $hotel['price'],
                'rate' => $hotel['rate'],
                'discount' => $hotel['discount'],
            ]);
        });
    }
}

<?php

namespace App\Services;

use App\Http\Resources\HotelResource;

class BestHotelsService extends HandlesService
{
    public function transformData()
    {
        $data = $this->getHotelsData();
        return collect($data)->map(function ($hotel) {
            return new HotelResource([
                'provider' => 'BestHotels', // Modify as needed
                'hotelName' => $hotel['name'],
                'price' => $hotel['price'],
                'rate' => $hotel['rate'],
                'discount' => $hotel['discount'],
            ]);
        });
    }
}

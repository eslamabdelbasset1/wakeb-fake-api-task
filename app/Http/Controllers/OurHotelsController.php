<?php

namespace App\Http\Controllers;

use App\Http\Resources\HotelResource;
use App\Services\BestHotelsService;
use App\Services\TopHotelsService;
use Illuminate\Http\Request;

class OurHotelsController extends Controller
{
    public function __construct(public BestHotelsService $bestHotels, public TopHotelsService $topHotels)
    {
    }

    public function index()
    {
        // Fetch data from providers
        $bestHotelsData = $this->bestHotels->transformData();
        $topHotelsData = $this->topHotels->transformData();

//        dd($bestHotelsData, $topHotelsData);

        // Merge and sort data by hotel rate
        $allHotels = $bestHotelsData->concat($topHotelsData)->toArray();
        usort($allHotels, function ($a, $b) {
            return $a['rate'] <=> $b['rate'];
        });

        // Transform data using API resources
        $transformedHotels = HotelResource::collection($allHotels);

        // Return transformed data as JSON response
        return response()->json($transformedHotels);
    }
}

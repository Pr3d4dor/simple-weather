<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherInfoController extends Controller
{
    public function __invoke(Request $request)
    {
        $lat = $request->query->get('lat');
        $long = $request->query->get('lng');
        $units = $request->query->has('units')
            ? $request->query->get('units')
            : 'metric';

        $cacheKey = sprintf(
            "openweather-(%s,%s)/%s",
            $lat,
            $long,
            $units,
        );

        if (Cache::has($cacheKey)) {
            return response()->json(
                json_decode(Cache::get($cacheKey), true)
            );
        }

        $response = Http::get(
            config('services.openweather.endpoint') . '/onecall',
            [
                'lat' => $lat,
                'lon' => $long,
                'appId' => config('services.openweather.key'),
                'units' => $units,
            ]
        );

        if ($response->successful()) {
            Cache::put(
                $cacheKey,
                json_encode($response->json()),
                now()->addMinutes(30),
            );
        }

        return JsonResponse::create($response->json(), $response->status());
    }
}

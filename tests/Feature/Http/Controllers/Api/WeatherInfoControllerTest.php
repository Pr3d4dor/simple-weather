<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * @covers \App\Http\Controllers\Controller\Api\WeatherInfoController;
 */
class WeatherInfoControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            config('services.openweather.endpoint') . '/*' => Http::response(
                json_decode(
                    $this->response('example_weather_info.json'),
                    true
                ),
                200,
            )
        ]);
    }

    /** @test */
    public function it_can_retrive_weather_info_by_latitude_and_longitude()
    {
        $latitude = -25.3951;
        $longitude = -51.4622;

        $response = $this->get(
            route(
                'weather.index',
                [
                    'lat' => $latitude,
                    'lng' => $longitude,
                ]
            )
        );

        $response->assertOk();
        $response->assertJson(json_decode(
            $this->response('example_weather_info.json'),
            true
        ));
    }

    /** @test */
    public function it_caches_weather_info()
    {
        // @see https://github.com/laravel/framework/issues/10803#issuecomment-401611084%23issuecomment-401611084
        $cacheDriver = app('cache')->driver();
        Cache::shouldReceive('driver')->andReturn($cacheDriver);

        $latitude = -25.3951;
        $longitude = -51.4622;
        $units = 'metric';

        $cacheKey = sprintf(
            "openweather-(%s,%s)/%s",
            $latitude,
            $longitude,
            $units,
        );

        Cache::shouldReceive('has')
            ->once()
            ->with($cacheKey)
            ->andReturn(false);

        Cache::shouldReceive('put')
            ->once()
            ->with(
                $cacheKey,
            \Mockery::any(),
            \Mockery::any(),
            )
            ->andReturn(true);

        $response = $this->get(
            route(
                'weather.index',
                [
                    'lat' => $latitude,
                    'lng' => $longitude,
                ]
            )
        );

        $response->assertOk();
        $response->assertJson(json_decode(
            $this->response('example_weather_info.json'),
            true
        ));
    }

    /** @test */
    public function it_can_retrive_and_return_cached_weather_data()
    {
        $latitude = -25.3951;
        $longitude = -51.4622;
        $units = 'metric';

        $cacheKey = sprintf(
            "openweather-(%s,%s)/%s",
            $latitude,
            $longitude,
            $units,
        );

        Cache::put($cacheKey, $this->response('example_weather_info.json'));

        // @see https://github.com/laravel/framework/issues/10803#issuecomment-401611084%23issuecomment-401611084
        $cacheDriver = app('cache')->driver();
        Cache::shouldReceive('driver')->andReturn($cacheDriver);

        Cache::shouldReceive('has')
            ->once()
            ->with($cacheKey)
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn($this->response('example_weather_info.json'));

        $response = $this->get(
            route(
                'weather.index',
                [
                    'lat' => $latitude,
                    'lng' => $longitude,
                ]
            )
        );

        $response->assertOk();
        $response->assertJson(json_decode(
            $this->response('example_weather_info.json'),
            true)
        );
    }
}

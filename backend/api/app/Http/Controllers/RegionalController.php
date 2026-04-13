<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RegionalController extends Controller
{
    private const BASE_URL = 'https://emsifa.github.io/api-wilayah-indonesia/api';
    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Get all provinces
     */
    public function getProvinces(): JsonResponse
    {
        $data = Cache::remember('provinces', self::CACHE_TTL, function () {
            $response = Http::get(self::BASE_URL . '/provinces.json');
            return $response->successful() ? $response->json() : [];
        });

        return response()->json($data);
    }

    /**
     * Get regencies/cities by province ID
     */
    public function getRegencies(string $provinceId): JsonResponse
    {
        $cacheKey = "regencies_{$provinceId}";
        
        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($provinceId) {
            $response = Http::get(self::BASE_URL . "/regencies/{$provinceId}.json");
            return $response->successful() ? $response->json() : [];
        });

        return response()->json($data);
    }

    /**
     * Get districts by regency ID
     */
    public function getDistricts(string $regencyId): JsonResponse
    {
        $cacheKey = "districts_{$regencyId}";
        
        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($regencyId) {
            $response = Http::get(self::BASE_URL . "/districts/{$regencyId}.json");
            return $response->successful() ? $response->json() : [];
        });

        return response()->json($data);
    }

    /**
     * Get villages by district ID
     */
    public function getVillages(string $districtId): JsonResponse
    {
        $cacheKey = "villages_{$districtId}";
        
        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($districtId) {
            $response = Http::get(self::BASE_URL . "/villages/{$districtId}.json");
            return $response->successful() ? $response->json() : [];
        });

        return response()->json($data);
    }
}

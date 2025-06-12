<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Currency;
use Illuminate\Http\Request;

class GeoLocationHelper
{
    /**
     * Get country code from IP address
     *
     * @param string $ip
     * @return string|null
     */
    public static function getCountryFromIP($ip)
    {
        // Skip for local IPs
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return 'US'; // Default to US for local development
        }

        // Try to get from cache first
        $cacheKey = 'ip_country_' . $ip;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Using ipapi.co service (free tier)
            $response = Http::get("https://ipapi.co/{$ip}/json/");
            
            if ($response->successful()) {
                $data = $response->json();
                $countryCode = $data['country_code'] ?? null;
                
                if ($countryCode) {
                    // Cache for 24 hours
                    Cache::put($cacheKey, $countryCode, 60 * 60 * 24);
                    return $countryCode;
                }
            }
        } catch (\Exception $e) {
            \Log::error('IP Geolocation failed: ' . $e->getMessage());
        }

        return 'US'; // Default to US if geolocation fails
    }

    /**
     * Get default currency for a country
     *
     * @param string $countryCode
     * @return string
     */
    public static function getDefaultCurrencyForCountry($countryCode)
    {
        // Common country-currency mappings
        $countryCurrencies = [
            'US' => 'USD',
            'GB' => 'GBP',
            'EU' => 'EUR',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'IN' => 'INR',
            'JP' => 'JPY',
            'CN' => 'CNY',
            'BR' => 'BRL',
            'RU' => 'RUB',
            'ZA' => 'ZAR',
            'MX' => 'MXN',
            'SG' => 'SGD',
            'AE' => 'AED',
            'SA' => 'SAR',
            'ZM' => 'ZMW',
            // Add more mappings as needed
        ];

        return $countryCurrencies[$countryCode] ?? 'USD';
    }

    /**
     * Get currency based on IP address
     *
     * @return string
     */
    public static function getCurrencyFromIP()
    {
        $request = request();
        
        // Get IP address from various possible headers
        $ip = $request->header('X-Forwarded-For');
        if (!$ip) {
            $ip = $request->header('X-Real-IP');
        }
        if (!$ip) {
            $ip = $request->ip();
        }
        
        // If multiple IPs are present, get the first one
        if (strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }
        // dd($/ip);

        \Log::info('Detected IP: ' . $ip); // For debugging
        
        $countryCode = self::getCountryFromIP($ip);
        $currency = self::getDefaultCurrencyForCountry($countryCode);
        
        \Log::info('Detected Currency: ' . $currency); // For debugging
        
        return $currency;
    }
} 
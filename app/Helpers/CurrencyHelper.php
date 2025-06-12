<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use App\Models\Currency;
use Worksome\Exchange\ExchangeRateProvider;
use Worksome\Exchange\ExchangeRate;

class CurrencyHelper
{
    /**
     * Convert amount from one currency to another
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public static function convert($amount, $fromCurrency, $toCurrency)
    {
        // If currencies are the same, return original amount
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        try {
            // Get exchange rate using Worksome Exchange
            $exchangeRate = app(ExchangeRateProvider::class)->getExchangeRate($fromCurrency, $toCurrency);
            
            // Convert the amount
            $convertedAmount = $amount * $exchangeRate->getRate();
            
            // Round to 2 decimal places
            return round($convertedAmount, 2);
        } catch (\Exception $e) {
            \Log::error('Currency conversion failed: ' . $e->getMessage());
            return $amount; // Return original amount if conversion fails
        }
    }

    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @param string $currencyCode
     * @return string
     */
    public static function formatAmount($amount, $currencyCode)
    {
        try {
            $currency = Currency::where('code', $currencyCode)->first();
            if (!$currency) {
                return $amount . ' ' . $currencyCode;
            }

            return $currency->symbol . ' ' . number_format($amount, 2);
        } catch (\Exception $e) {
            \Log::error('Currency formatting failed: ' . $e->getMessage());
            return $amount . ' ' . $currencyCode;
        }
    }
} 
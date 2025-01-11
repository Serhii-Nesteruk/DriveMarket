<?php

class CurrencyConvertor
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiUrl = "https://v6.exchangerate-api.com/v6/";
        $this->apiKey = $apiKey;
    }

    public function convert(float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        try {
            $rates = $this->getExchangeRates($fromCurrency);

            if (!isset($rates[$toCurrency])) {
                throw new Exception("Currency code not supported: $toCurrency");
            }

            return round($amount * $rates[$toCurrency], 2);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    private function getExchangeRates(string $baseCurrency): array
    {
        $url = $this->apiUrl . $this->apiKey . "/latest/" . strtoupper($baseCurrency);
        $response = file_get_contents($url);

        if ($response === false) {
            throw new Exception("Failed to fetch exchange rates.");
        }

        $data = json_decode($response, true);

        if (!isset($data['conversion_rates'])) {
            throw new Exception("Invalid API response: " . json_encode($data));
        }

        return $data['conversion_rates'];
    }
}
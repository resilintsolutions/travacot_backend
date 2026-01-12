<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class HotelbedsService
{
    protected Client $client;
    protected string $apiKey;
    protected string $secret;
    protected string $base;
    protected string $listEndpoint;

    public function __construct()
    {
        // Prefer config/services.php, fall back to .env
        $this->apiKey = config('services.hotelbeds.api_key') ?: env('HOTELBEDS_API_KEY');
        $this->secret = config('services.hotelbeds.secret')  ?: env('HOTELBEDS_SECRET');

        // Base: https://api.test.hotelbeds.com (no trailing /)
        $this->base   = config('services.hotelbeds.base_url')
            ?: env('HOTELBEDS_BASE', 'https://api.test.hotelbeds.com');

        // Default list endpoint (Availability)
        $this->listEndpoint = config('services.hotelbeds.list_endpoint', '/hotel-api/1.0/hotels');

        $this->client = new Client([
            'base_uri' => $this->base,
            'timeout'  => 30,
            // 'proxy' => 'http://user:pass@proxy:port', // only if you really use a proxy
        ]);
    }

    /**
     * Build Hotelbeds authentication headers.
     */
    protected function signatureHeaders(): array
    {
        $ts  = (string) time(); // simpler & 100% UTC safe
        $sig = hash('sha256', $this->apiKey . $this->secret . $ts);

        return [
            'Api-key'       => $this->apiKey,
            'X-Signature'   => $sig,
            'Accept'        => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Low-level POST wrapper with logging & error handling.
     */
    protected function post(string $uri, array $json = []): array
    {
        try {
            $resp = $this->client->post($uri, [
                'headers' => $this->signatureHeaders(),
                'json'    => $json,
            ]);

            return json_decode((string) $resp->getBody(), true) ?? [];
        } catch (RequestException $e) {
            Log::error('Hotelbeds POST request failed', [
                'uri'     => $uri,
                'message' => $e->getMessage(),
                'request' => $json,
                'status'  => $e->getResponse()?->getStatusCode(),
                'body'    => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            // Our own error wrapper
            return [
                'success' => false,
                'error'   => [
                    'message' => $e->getMessage(),
                    'type'    => 'RequestException',
                ],
                'raw'     => $e->hasResponse()
                    ? (string) $e->getResponse()->getBody()
                    : null,
            ];
        }
    }

    /**
     * Low-level GET wrapper with logging & error handling.
     * Used for Content API calls (hotel details, images, etc.).
     */
    protected function get(string $uri, array $query = []): array
    {
        try {
            $resp = $this->client->get($uri, [
                'headers' => $this->signatureHeaders(),
                'query'   => $query,
            ]);

            return json_decode((string) $resp->getBody(), true) ?? [];
        } catch (RequestException $e) {
            Log::error('Hotelbeds GET request failed', [
                'uri'     => $uri,
                'message' => $e->getMessage(),
                'query'   => $query,
                'status'  => $e->getResponse()?->getStatusCode(),
                'body'    => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            return [
                'success' => false,
                'error'   => [
                    'message' => $e->getMessage(),
                    'type'    => 'RequestException',
                ],
                'raw'     => $e->hasResponse()
                    ? (string) $e->getResponse()->getBody()
                    : null,
            ];
        }
    }

    /**
     * Availability request (Hotel API).
     * POST https://api.test.hotelbeds.com/hotel-api/1.0/hotels
     */
    public function availability(array $payload): array
    {
        return $this->post('/hotel-api/1.0/hotels', $payload);
    }

    /**
     * Same as availability but using configurable endpoint.
     */
    public function listHotels(array $payload = []): array
    {
        return $this->post($this->listEndpoint, $payload);
    }

    /**
     * Check rate (Hotel API).
     * POST /hotel-api/1.0/checkrates
     */
    public function checkRate(array $payload): array
    {
        return $this->post('/hotel-api/1.0/checkrates', $payload);
    }

    /**
     * Book (Hotel API).
     * POST /hotel-api/1.0/bookings
     */
    public function book(array $payload): array
    {
        return $this->post('/hotel-api/1.0/bookings', $payload);
    }

    /**
     * Get Booking Details (Hotel API).
     *
     * Used to:
     * - Check booking status
     * - Sync supplier confirmation
     * - Verify pending / failed bookings
     *
     * GET /hotel-api/1.0/bookings/{bookingReference}
     */
    public function getBooking(string $bookingReference): array
    {
        if (empty($bookingReference)) {
            return [
                'success' => false,
                'error' => [
                    'message' => 'Booking reference is required',
                    'type'    => 'ValidationError',
                ],
            ];
        }

        $uri = "/hotel-api/1.0/bookings/{$bookingReference}";

        try {
            $resp = $this->client->get($uri, [
                'headers' => $this->signatureHeaders(),
            ]);

            return json_decode((string) $resp->getBody(), true) ?? [];

        } catch (RequestException $e) {

            Log::error('Hotelbeds GET booking failed', [
                'bookingReference' => $bookingReference,
                'message'          => $e->getMessage(),
                'status'           => $e->getResponse()?->getStatusCode(),
                'body'             => $e->hasResponse()
                    ? (string) $e->getResponse()->getBody()
                    : null,
            ]);

            return [
                'success' => false,
                'error' => [
                    'message' => 'Failed to fetch booking from Hotelbeds',
                    'type'    => 'RequestException',
                ],
                'raw' => $e->hasResponse()
                    ? (string) $e->getResponse()->getBody()
                    : null,
            ];
        }
    }


    /**
     * Helper: determine if a Content API response is valid.
     */
    protected function isValidContentResponse(array $resp): bool
    {
        $hasHotel    = !empty($resp['hotel']);
        $hasErrorKey = isset($resp['error']);
        $hasErrorFlag = isset($resp['success']) && $resp['success'] === false;

        return $hasHotel && !$hasErrorKey && !$hasErrorFlag;
    }

    /**
     * Content API - Get static content for a single hotel (incl. images, description, facilities).
     *
     * Tries:
     *   /hotel-content-api/1.0/hotels/{code}/details
     * then, if needed:
     *   /hotel-content-api/1.0/hotels/{code}
     */
    public function getHotelContent(int|string $hotelCode, string $language = 'ENG'): array
    {
        // Try /details endpoint
        $pathDetails = "/hotel-content-api/1.0/hotels/{$hotelCode}/details";

        $resp = $this->get($pathDetails, [
            'language' => $language,
        ]);

        if ($this->isValidContentResponse($resp)) {
            return $resp;
        }

        // Fallback: try without /details
        $pathSimple = "/hotel-content-api/1.0/hotels/{$hotelCode}";

        $resp2 = $this->get($pathSimple, [
            'language' => $language,
        ]);

        if ($this->isValidContentResponse($resp2)) {
            return $resp2;
        }

        // If both failed, return the "best" we have (resp2 if non-empty, else resp)
        return $resp2 ?: $resp;
    }

        /**
     * Low-level DELETE wrapper with logging & error handling.
     * Used for Booking Cancellation.
     */
    protected function delete(string $uri, array $query = []): array
    {
        try {
            $resp = $this->client->delete($uri, [
                'headers' => $this->signatureHeaders(),
                'query'   => $query,
            ]);

            return json_decode((string) $resp->getBody(), true) ?? [];
        } catch (RequestException $e) {
            Log::error('Hotelbeds DELETE request failed', [
                'uri'     => $uri,
                'message' => $e->getMessage(),
                'query'   => $query,
                'status'  => $e->getResponse()?->getStatusCode(),
                'body'    => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            return [
                'success' => false,
                'error'   => [
                    'message' => $e->getMessage(),
                    'type'    => 'RequestException',
                ],
                'raw'     => $e->hasResponse()
                    ? (string) $e->getResponse()->getBody()
                    : null,
            ];
        }
    }

    /**
     * Booking Cancellation (Hotel API).
     * DELETE /hotel-api/1.0/bookings/{bookingId}?cancellationFlag=CANCELLATION&language=ENG
     *
     * $bookingId is Hotelbeds reference like "52-1274417"
     */
    public function cancelBooking(string $bookingId, string $cancellationFlag = 'CANCELLATION', string $language = 'ENG'): array
    {
        $uri = "/hotel-api/1.0/bookings/{$bookingId}";

        $query = [
            'cancellationFlag' => $cancellationFlag, // CANCELLATION or SIMULATION
            'language'         => $language,
        ];

        return $this->delete($uri, $query);
    }
    
    public function getHotelbedsCountries(): array
    {
        $resp = $this->get('/hotel-content-api/1.0/locations/countries', [
            'language' => 'ENG',
            'offset'   => 0,
            'limit'    => 1000,
        ]);

        if (!empty($resp['countries'])) {
            return [
                'success' => true,
                'data'    => collect($resp['countries'])
                    ->map(fn ($c) => [
                        'code' => $c['code'],
                        'name' => $c['name'] ?? $c['description']['content'] ?? $c['code'],
                    ])
                    ->values()
                    ->toArray(),
            ];
        }

        return [
            'success' => false,
            'data'    => $this->getStaticCountries(),
        ];
    }


    public function getHotelbedsDestinations(array $params = []): array
    {
        if (empty($params['countryCode'])) {
            return [
                'success' => false,
                'data'    => [],
                'message' => 'countryCode is required',
            ];
        }

        $resp = $this->get('/hotel-content-api/1.0/locations/destinations', [
            'countryCodes' => strtoupper($params['countryCode']),
            'language'     => 'ENG',
            'offset'       => 0,
            'limit'        => 1000,
        ]);

        if (!empty($resp['destinations'])) {
            return [
                'success' => true,
                'data'    => collect($resp['destinations'])
                    ->filter(fn ($d) =>
                        !empty($d['code']) &&
                        !empty($d['name']) &&
                        strlen($d['code']) <= 5 // removes regions
                    )
                    ->map(fn ($d) => [
                        'code'        => $d['code'],
                        'name'        => $d['name'],
                        'countryCode'=> $d['countryCode'],
                    ])
                    ->values()
                    ->toArray(),
            ];
        }

        // fallback only if API fails
        return [
            'success' => false,
            'data'    => $this->getStaticDestinations($params['countryCode']),
        ];
    }

    /**
     * Get rate comments (tax rules, special conditions, etc.)
     *
     * Used when availability response provides rateCommentsId
     * Example ID: "278|1069|3"
     *
     * GET /hotel-content-api/1.0/ratecomments
     */
    public function getRateComments(string $rateCommentsId, string $language = 'ENG'): array
    {
        if (empty($rateCommentsId)) {
            return [];
        }

        try {
            $resp = $this->get('/hotel-content-api/1.0/ratecomments', [
                'rateCommentsId' => $rateCommentsId,
                'language'       => $language,
            ]);

            /*
            Expected response structure:
            {
            "rateComments": [
                {
                "description": {
                    "content": "City tax is not included and must be paid at the hotel"
                }
                }
            ]
            }
            */

            $comments = data_get($resp, 'rateComments', []);

            if (!is_array($comments) || empty($comments)) {
                return [];
            }

            // Merge all descriptions into one readable text
            $descriptions = collect($comments)
                ->map(fn ($c) => data_get($c, 'description.content'))
                ->filter()
                ->values()
                ->toArray();

            return [
                'description' => implode(' ', $descriptions),
                'raw'         => $comments,
            ];

        } catch (\Throwable $e) {
            Log::warning('Hotelbeds rate comments fetch failed', [
                'rateCommentsId' => $rateCommentsId,
                'error'          => $e->getMessage(),
            ]);

            return [];
        }
    }


    /**
     * STATIC fallback: Hotelbeds-compatible list of countries.
     */
    public function getStaticCountries(): array
    {
        return [
            [ "code" => "ES", "name" => "Spain" ],
            [ "code" => "US", "name" => "United States" ],
            [ "code" => "FR", "name" => "France" ],
            [ "code" => "IT", "name" => "Italy" ],
            [ "code" => "GB", "name" => "United Kingdom" ],
            [ "code" => "AE", "name" => "United Arab Emirates" ],
            [ "code" => "IN", "name" => "India" ],
            [ "code" => "TH", "name" => "Thailand" ],
            [ "code" => "SG", "name" => "Singapore" ],
            [ "code" => "JP", "name" => "Japan" ],
            [ "code" => "CN", "name" => "China" ],
            [ "code" => "DE", "name" => "Germany" ],
            [ "code" => "NL", "name" => "Netherlands" ],
            [ "code" => "CH", "name" => "Switzerland" ],
            [ "code" => "AU", "name" => "Australia" ],
        ];
    }

    /**
     * STATIC fallback: Hotelbeds-compatible list of destinations.
     *
     * @param string|null $countryCode Filter by country code (optional)
     * @return array
     */
    public function getStaticDestinations(string $countryCode = null): array
    {
        $destinations = [

            // Spain
            [ "code" => "BCN", "name" => "Barcelona",       "countryCode" => "ES", "language" => "ENG" ],
            [ "code" => "MAD", "name" => "Madrid",          "countryCode" => "ES", "language" => "ENG" ],
            [ "code" => "PMI", "name" => "Mallorca",        "countryCode" => "ES", "language" => "ENG" ],
            [ "code" => "IBZ", "name" => "Ibiza",           "countryCode" => "ES", "language" => "ENG" ],
            [ "code" => "TFS", "name" => "Tenerife South",  "countryCode" => "ES", "language" => "ENG" ],

            // USA
            [ "code" => "NYC", "name" => "New York",        "countryCode" => "US", "language" => "ENG" ],
            [ "code" => "MIA", "name" => "Miami",           "countryCode" => "US", "language" => "ENG" ],
            [ "code" => "LAX", "name" => "Los Angeles",     "countryCode" => "US", "language" => "ENG" ],
            [ "code" => "SFO", "name" => "San Francisco",   "countryCode" => "US", "language" => "ENG" ],
            [ "code" => "LAS", "name" => "Las Vegas",       "countryCode" => "US", "language" => "ENG" ],

            // Italy
            [ "code" => "ROM", "name" => "Rome",            "countryCode" => "IT", "language" => "ENG" ],
            [ "code" => "MIL", "name" => "Milan",           "countryCode" => "IT", "language" => "ENG" ],
            [ "code" => "VCE", "name" => "Venice",          "countryCode" => "IT", "language" => "ENG" ],
            [ "code" => "FLR", "name" => "Florence",        "countryCode" => "IT", "language" => "ENG" ],

            // France
            [ "code" => "PAR", "name" => "Paris",           "countryCode" => "FR", "language" => "ENG" ],
            [ "code" => "NCE", "name" => "Nice",            "countryCode" => "FR", "language" => "ENG" ],
            [ "code" => "LYS", "name" => "Lyon",            "countryCode" => "FR", "language" => "ENG" ],

            // UK
            [ "code" => "LON", "name" => "London",          "countryCode" => "GB", "language" => "ENG" ],
            [ "code" => "MAN", "name" => "Manchester",      "countryCode" => "GB", "language" => "ENG" ],
            [ "code" => "EDI", "name" => "Edinburgh",       "countryCode" => "GB", "language" => "ENG" ],

            // UAE
            [ "code" => "DXB", "name" => "Dubai",           "countryCode" => "AE", "language" => "ENG" ],
            [ "code" => "AUH", "name" => "Abu Dhabi",       "countryCode" => "AE", "language" => "ENG" ],

            // India
            [ "code" => "DEL", "name" => "New Delhi",       "countryCode" => "IN", "language" => "ENG" ],
            [ "code" => "BOM", "name" => "Mumbai",          "countryCode" => "IN", "language" => "ENG" ],
            [ "code" => "BLR", "name" => "Bangalore",       "countryCode" => "IN", "language" => "ENG" ],
            [ "code" => "GOI", "name" => "Goa",             "countryCode" => "IN", "language" => "ENG" ],

            // Japan
            [ "code" => "TYO", "name" => "Tokyo",           "countryCode" => "JP", "language" => "ENG" ],
            [ "code" => "OSA", "name" => "Osaka",           "countryCode" => "JP", "language" => "ENG" ],
            [ "code" => "KIX", "name" => "Kansai",          "countryCode" => "JP", "language" => "ENG" ],
        ];

        if ($countryCode) {
            return array_values(array_filter($destinations, function ($d) use ($countryCode) {
                return strtoupper($d['countryCode']) === strtoupper($countryCode);
            }));
        }

        return $destinations;
    }

}

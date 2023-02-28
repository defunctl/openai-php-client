<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;

final class OpenAI
{
    /**
     * Creates a new Open AI Client with the given API token.
     */
    public static function client(string $apiKey, string|null $organization = null, int|null|Closure $concurrency = null): Client
    {
        $apiKey = ApiKey::from($apiKey);

        $baseUri = BaseUri::from('api.openai.com/v1');

        $headers = Headers::withAuthorization($apiKey);

        if ($organization !== null) {
            $headers = $headers->withOrganization($organization);
        }

        $client = new GuzzleClient();

        $transporter = new HttpTransporter($client, $client, $baseUri, $headers, $concurrency);

        return new Client($transporter);
    }
}

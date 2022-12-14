<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiToken;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;

final class OpenAI
{
    /**
     * Creates a new Open AI Client with the given API token.
     *
     * @param  string  $apiToken  The OpenAI API token.
     * @param  string|null  $organization  Use an OpenAI organization identifier other than the default set in your account.
     * @param  int|null|Closure  $concurrency  Limit Guzzle's allowed number of outstanding concurrent requests when using parallel execution.
     */
    public static function client(
        string $apiToken,
        string|null $organization = null,
        int|null|Closure $concurrency = null
    ): Client {
        $apiToken = ApiToken::from($apiToken);
        $baseUri = BaseUri::from('api.openai.com/v1');
        $headers = Headers::withAuthorization($apiToken);

        if ($organization !== null) {
            $headers = $headers->withOrganization($organization);
        }

        $client = new GuzzleClient();

        $transporter = new HttpTransporter($client, $client, $baseUri, $headers, $concurrency);

        return new Client($transporter);
    }
}

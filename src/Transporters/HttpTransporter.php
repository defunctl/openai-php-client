<?php

declare(strict_types=1);

namespace OpenAI\Transporters;

use Closure;
use Generator;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use JsonException;
use OpenAI\Contracts\Transporter;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Exceptions\UnserializableResponse;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use OpenAI\ValueObjects\Transporter\Payload;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * @internal
 */
final class HttpTransporter implements Transporter
{
    /**
     * Creates a new Http Transporter instance.
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly \GuzzleHttp\ClientInterface $parallelClient,
        private readonly BaseUri $baseUri,
        private readonly Headers $headers,
        private readonly int|null|Closure $concurrency = null
    ) {
        // ..
    }

    /**
     * {@inheritDoc}
     */
    public function requestObject(Payload $payload): array
    {
        $request = $payload->toRequest($this->baseUri, $this->headers);

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw new TransporterException($clientException);
        }

        $contents = (string) $response->getBody();

        try {
            /** @var array{error?: array{message: string, type: string, code: string}} $response */
            $response = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new UnserializableResponse($jsonException);
        }

        if (isset($response['error'])) {
            throw new ErrorException($response['error']);
        }

        return $response;
    }

    /**
     * Parallel Proof of Concept.
     *
     * @TODO handle errors properly.
     * @TODO see if we can abstract out the direct Pool object reference.
     *
     * {@inheritDoc}
     */
    public function requestObjects(array $payloads): array
    {
        $requests = function () use ($payloads): Generator {
            foreach ($payloads as $key => $payload) {
                yield $key => function () use ($payload): PromiseInterface {
                    $request = $payload->toRequest($this->baseUri, $this->headers);

                    return $this->parallelClient->sendAsync($request);
                };
            }
        };

        $responses = [];

        (new Pool($this->parallelClient, $requests(), [
            'concurrency' => $this->concurrency,
            'fulfilled' => function (Response $response, $index) use (&$responses): void {
                $contents = $response->getBody()->getContents();

                try {
                    /** @var array{error?: array{message: string, type: string, code: string}} $response */
                    $response = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $jsonException) {
                    throw new UnserializableResponse($jsonException);
                }

                $responses[$index] = $response;
            },
            'rejected' => function (RequestException $reason, $index): void {
                dump($reason);
            },
        ]))->promise()->wait();

        return $responses;
    }

    /**
     * {@inheritDoc}
     */
    public function requestContent(Payload $payload): string
    {
        $request = $payload->toRequest($this->baseUri, $this->headers);

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw new TransporterException($clientException);
        }

        $contents = $response->getBody()->getContents();

        try {
            /** @var array{error?: array{message: string, type: string, code: string}} $response */
            $response = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

            if (isset($response['error'])) {
                throw new ErrorException($response['error']);
            }
        } catch (JsonException) {
            // ..
        }

        return $contents;
    }
}

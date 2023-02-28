<?php

use OpenAI\Client;
use OpenAI\Contracts\Transporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use OpenAI\ValueObjects\Transporter\Payload;

function mockClient(string $method, string $resource, array $params, array|string $response, string $methodName = 'requestObject', int $times = 1)
{
    $transporter = Mockery::mock(Transporter::class);

    if ($methodName === 'requestObjects') {
        $transporter
            ->shouldReceive($methodName)
            ->times($times)
            ->withArgs(function (array $payloads) use ($method, $resource) {
                foreach ($payloads as $payload) {
                    $baseUri = BaseUri::from('api.openai.com/v1');
                    $headers = Headers::withAuthorization(ApiKey::from('foo'));

                    $request = $payload->toRequest($baseUri, $headers);

                    if ($request->getMethod() !== $method
                           || $request->getUri()->getPath() !== "/v1/$resource"
                    ) {
                        return false;
                    }
                }

                return true;
            })->andReturn($response);
    } else {
        $transporter
            ->shouldReceive($methodName)
            ->once()
            ->withArgs(function (Payload $payload) use ($method, $resource) {
                $baseUri = BaseUri::from('api.openai.com/v1');
                $headers = Headers::withAuthorization(ApiKey::from('foo'));

                $request = $payload->toRequest($baseUri, $headers);

                return $request->getMethod() === $method
                       && $request->getUri()->getPath() === "/v1/$resource";
            })->andReturn($response);
    }

    return new Client($transporter);
}

function mockContentClient(string $method, string $resource, array $params, string $response)
{
    return mockClient($method, $resource, $params, $response, 'requestContent');
}

function mockParallelClient(string $method, string $resource, array $params, array $response, int $times = 1)
{
    return mockClient($method, $resource, $params, $response, 'requestObjects', $times);
}

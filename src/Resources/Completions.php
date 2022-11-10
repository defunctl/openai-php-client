<?php

declare(strict_types=1);

namespace OpenAI\Resources;

use OpenAI\Contracts\Parallel;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Exceptions\UnserializableResponse;
use OpenAI\Responses\Completions\CreateResponse;
use OpenAI\ValueObjects\Transporter\Payload;

/**
 * @implements Parallel<array>
 * @implements Parallel<list<CreateResponse>>
 */
final class Completions implements Parallel
{
    use Concerns\Transportable;

    /**
     * Creates a completion for the provided prompt and parameters
     *
     * @see https://beta.openai.com/docs/api-reference/completions/create-completion
     *
     * @param  array<string, mixed>  $parameters
     */
    public function create(array $parameters): CreateResponse
    {
        $payload = Payload::create('completions', $parameters);

        /** @var array{id: string, object: string, created: int, model: string, choices: array<int, array{text: string, index: int, logprobs: array{tokens: array<int, string>, token_logprobs: array<int, float>, top_logprobs: array<int, string>|null, text_offset: array<int, int>}|null, finish_reason: string}>, usage: array{prompt_tokens: int, completion_tokens: int, total_tokens: int}} $result */
        $result = $this->transporter->requestObject($payload);

        return CreateResponse::from($result);
    }

    public function createParallel(array $parameters): self
    {
        $this->payloads[] = Payload::create('completions', $parameters);

        return $this;
    }

    /**
     * @return CreateResponse[]
     *
     * @throws ErrorException
     * @throws TransporterException
     * @throws UnserializableResponse
     */
    public function run(): array
    {
        /** @var array<array-key, array{id: string, object: string, created: int, model: string, choices: array<int, array{text: string, index: int, logprobs: array{tokens: array<int, string>, token_logprobs: array<int, float>, top_logprobs: array<int, string>|null, text_offset: array<int, int>}|null, finish_reason: string}>, usage: array{prompt_tokens: int, completion_tokens: int, total_tokens: int}}> $responses */
        $responses = $this->transporter->requestObjects($this->payloads);

        return array_map(static fn (array $result): CreateResponse => CreateResponse::from($result), $responses);
    }
}

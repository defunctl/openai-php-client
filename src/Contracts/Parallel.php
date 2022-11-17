<?php

declare(strict_types=1);

namespace OpenAI\Contracts;

use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Exceptions\UnserializableResponse;

/**
 * @template TResponse of Response[]
 *
 * @interal
 */
interface Parallel
{
    /**
     * Queue up parallel requests.
     *
     * @param  array<string, mixed>  $parameters  The OpenAI request parameters.
     * @param  array-key|null  $key  The index to store the promise in.
     * @return $this
     */
    public function createParallel(array $parameters, int|string|null $key = null): self;

    /**
     * Execute all parallel requests.
     *
     * @return TResponse
     *
     * @throws TransporterException
     * @throws UnserializableResponse
     * @throws ErrorException
     */
    public function run(): array;
}

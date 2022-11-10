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
     * @param  array<string, mixed>  $parameters
     *
     * @return $this
     */
    public function createParallel(array $parameters): self;

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

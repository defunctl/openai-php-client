<?php

declare(strict_types=1);

namespace OpenAI\Resources\Concerns;

use OpenAI\Contracts\Transporter;
use OpenAI\ValueObjects\Transporter\Payload;

trait Transportable
{

    /**
     * The collection of payloads to run via a concurrent pool.
     *
     * @var Payload[]
     */
    private array $payloads = [];

    /**
     * Creates a Client instance with the given API token.
     */
    public function __construct(private readonly Transporter $transporter)
    {
        // ..
    }

}

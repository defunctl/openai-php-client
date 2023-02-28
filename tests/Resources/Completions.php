<?php

use OpenAI\Responses\Completions\CreateResponse;
use OpenAI\Responses\Completions\CreateResponseChoice;
use OpenAI\Responses\Completions\CreateResponseUsage;

test('create', function () {
    $client = mockClient('POST', 'completions', [
        'model' => 'da-vince',
        'prompt' => 'hi',
    ], completion());

    $result = $client->completions()->create([
        'model' => 'da-vince',
        'prompt' => 'hi',
    ]);

    expect($result)
        ->toBeInstanceOf(CreateResponse::class)
        ->id->toBe('cmpl-5uS6a68SwurhqAqLBpZtibIITICna')
        ->object->toBe('text_completion')
        ->created->toBe(1664136088)
        ->model->toBe('davinci')
        ->choices->toBeArray()->toHaveCount(1)
        ->choices->each->toBeInstanceOf(CreateResponseChoice::class)
        ->usage->toBeInstanceOf(CreateResponseUsage::class);

    expect($result->choices[0])
        ->text->toBe("el, she elaborates more on the Corruptor's role, suggesting K")
        ->index->toBe(0)
        ->logprobs->toBe(null)
        ->finishReason->toBe('length');

    expect($result->usage)
        ->promptTokens->toBe(1)
        ->completionTokens->toBe(16)
        ->totalTokens->toBe(17);
});

test('create parallel', function () {
    $client = mockParallelClient('POST', 'completions', [
        [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
        [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
        [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
    ], completions(3));

    $result = $client->completions()
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ])
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ])
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ])
        ->run();

    expect($result)
        ->toHaveCount(3)
        ->each(static function ($response) {
            $response
                ->toBeInstanceOf(CreateResponse::class)
                ->id->toBe('cmpl-5uS6a68SwurhqAqLBpZtibIITICna')
                ->object->toBe('text_completion')
                ->created->toBe(1664136088)
                ->model->toBe('davinci')
                ->usage->toBeInstanceOf(CreateResponseUsage::class)
                ->usage->promptTokens->toBe(1)
                ->usage->completionTokens->toBe(16)
                ->usage->totalTokens->toBe(17)
                ->choices->toBeArray()->toHaveCount(1)
                ->choices->each->toBeInstanceOf(CreateResponseChoice::class)
                ->choices->each(static function ($choice) {
                    $choice
                        ->text->toBe("el, she elaborates more on the Corruptor's role, suggesting K")
                        ->index->toBe(0)
                        ->logprobs->toBe(null)
                        ->finishReason->toBe('length');
                });
        });
});

test('create parallel requests with custom keys', function () {
    $client = mockParallelClient('POST', 'completions', [
        'test-1' => [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
        'test-2' => [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
        'test-3' => [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
    ], completions(3, [
        'test-1',
        'test-2',
        'test-3',
    ]));

    $result = $client->completions()
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ], 'test-1')
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ], 'test-2')
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ], 'test-3')
        ->run();

    expect($result)
        ->toHaveKeys(['test-1', 'test-2', 'test-3']);

    expect($result)
        ->toHaveCount(3)
        ->each(static function ($response) {
            $response
                ->toBeInstanceOf(CreateResponse::class)
                ->id->toBe('cmpl-5uS6a68SwurhqAqLBpZtibIITICna')
                ->object->toBe('text_completion')
                ->created->toBe(1664136088)
                ->model->toBe('davinci')
                ->usage->toBeInstanceOf(CreateResponseUsage::class)
                ->usage->promptTokens->toBe(1)
                ->usage->completionTokens->toBe(16)
                ->usage->totalTokens->toBe(17)
                ->choices->toBeArray()->toHaveCount(1)
                ->choices->each->toBeInstanceOf(CreateResponseChoice::class)
                ->choices->each(static function ($choice) {
                    $choice
                        ->text->toBe("el, she elaborates more on the Corruptor's role, suggesting K")
                        ->index->toBe(0)
                        ->logprobs->toBe(null)
                        ->finishReason->toBe('length');
                });
        });
});

test('throws duplicate key exception', function () {
    $client = mockParallelClient('POST', 'completions', [
        'test-1' => [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
        'test-2' => [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
        'test-2' => [
            'model' => 'da-vince',
            'prompt' => 'hi',
        ],
    ], completions(3, [
        'test-1',
        'test-2',
        'test-2',
    ]), 0);

    $client->completions()
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ], 'test-1')
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ], 'test-2')
        ->createParallel([
            'model' => 'da-vince',
            'prompt' => 'hi',
        ], 'test-2')
        ->run();
})->throws(InvalidArgumentException::class, 'Duplicate array key detected');

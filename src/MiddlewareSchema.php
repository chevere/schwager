<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Schwager;

use Chevere\Common\Interfaces\ToArrayInterface;
use Chevere\Http\Interfaces\MiddlewareNameInterface;
use function Chevere\Http\requestAttribute;
use function Chevere\Http\responseAttribute;

final class MiddlewareSchema implements ToArrayInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $array = [];

    /**
     * @var array<string, array<string>>
     */
    private array $request = [];

    /**
     * @var array<int, array<int|string, mixed>>
     */
    private array $responses = [];

    public function __construct(MiddlewareNameInterface $middleware)
    {
        $name = $middleware->__toString();
        $context = shortName($name);
        $request = requestAttribute($name);
        $response = responseAttribute($name);
        $this->responses = [];
        $statuses = $response->status->toArray();
        $statuses = array_fill_keys($statuses, [
            'context' => $context,
        ]);
        foreach ($statuses as $code => $array) {
            if ($code === $response->status->primary) {
                $array['headers'] = $response->headers->toArray();
            }
            $this->responses[$code][] = $array;
        }
        ksort($this->responses);
        $this->request = [
            'headers' => $request->headers->toArray(),
        ];
        $this->array = [
            'request' => $this->request,
            'responses' => $this->responses,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->array;
    }

    /**
     * @return array<string, array<string>>
     */
    public function request(): array
    {
        return $this->request;
    }

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function responses(): array
    {
        return $this->responses;
    }
}

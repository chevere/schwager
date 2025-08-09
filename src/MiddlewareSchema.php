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

use Chevere\Http\Attributes\Request;
use Chevere\Http\Attributes\Response;
use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Chevere\Schwager\Interfaces\SchemaInterface;
use ReflectionClass;
use function Chevere\Http\requestAttribute;
use function Chevere\Http\responseAttribute;
use function Chevere\Parameter\getType;

final class MiddlewareSchema implements SchemaInterface
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
     * @var array<int|string, array<int|string, mixed>>
     */
    private array $responses = [];

    public function __construct(MiddlewareNameInterface $middleware)
    {
        $name = $middleware->__toString();
        $context = shortName($name);
        $arguments = [];
        foreach ($middleware->arguments() as $key => $value) {
            $value = is_scalar($value)
                ? (string) $value
                : getType($value);
            $arguments[] = <<<PLAIN
            {$key}:{$value}
            PLAIN;
        }
        if ($arguments !== []) {
            $context = "{$context} " . implode(', ', $arguments);
        }
        // @phpstan-ignore-next-line
        $reflection = new ReflectionClass($name);
        $requestHeaders = [];
        if ($this->hasAttribute($reflection, Request::class)) {
            $request = requestAttribute($name);
            $requestHeaders = $request?->headers->toArray() ?? [];
        }
        $this->responses = [];
        if ($this->hasAttribute($reflection, Response::class)) {
            $response = responseAttribute($name);
            $statuses = $response?->status->toArray() ?? [];
            $statuses = array_fill_keys($statuses, [
                'context' => $context,
            ]);
            foreach ($statuses as $code => $array) {
                if ($response && $code === $response->status->success()) {
                    $array['headers'] = $response->headers->toLines();
                }
                $this->responses[$code][] = $array;
            }
            ksort($this->responses);
        }

        $this->request = [
            'headers' => $requestHeaders,
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
     * @return array<int|string, array<int|string, mixed>>
     */
    public function responses(): array
    {
        return $this->responses;
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function hasAttribute(ReflectionClass $reflection, string $attribute): bool
    {
        $attributes = $reflection->getAttributes($attribute);

        return $attributes !== [];
    }
}

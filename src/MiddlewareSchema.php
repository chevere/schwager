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
use function Chevere\Http\classHeaders;
use Chevere\Http\Interfaces\MiddlewareNameInterface;
use function Chevere\Parameter\arrayp;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use function Chevere\Parameter\string;

final class MiddlewareSchema implements ToArrayInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $array = [];

    public function __construct(MiddlewareNameInterface $middleware)
    {
        $name = $middleware->__toString();
        $context = $this->getShortName($name);
        $this->array = [
            'context' => $context,
            'headers' => classHeaders($name)->toArray(),
            'body' => $this->acceptBody()->schema(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->array;
    }

    public function acceptBody(): ArrayParameterInterface
    {
        return arrayp(
            message: string(),
        )->withOptional(code: string());
    }

    private function getShortName(string $name): string
    {
        $explode = explode('\\', $name);

        return array_pop($explode);
    }
}

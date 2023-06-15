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
use Chevere\Router\Interfaces\VariableInterface;

final class VariableSchema implements ToArrayInterface
{
    public function __construct(
        private VariableInterface $variable,
        private string $description,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'required' => true,
        ] + [
            'type' => 'string',
            'description' => $this->description,
            'regex' => $this->variable->regex()->noDelimiters(),
        ];
    }
}

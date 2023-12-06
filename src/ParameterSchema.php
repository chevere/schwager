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

use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Schwager\Interfaces\SchemaInterface;

final class ParameterSchema implements SchemaInterface
{
    public function __construct(
        private ParameterInterface $parameter,
        private bool $isRequired,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'required' => $this->isRequired,
        ] + $this->parameter->schema();
    }
}

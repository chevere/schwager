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

use Chevere\Schwager\Attributes\Statuses;
use ReflectionClass;

function classStatuses(string $className): Statuses
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);
    $attributes = $reflection->getAttributes(Statuses::class);
    if ($attributes === []) {
        return new Statuses(200);
    }
    /** @var Statuses */
    return $attributes[0]->newInstance();
}

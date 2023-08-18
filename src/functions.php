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

function shortName(string $fqn): string
{
    $explode = explode('\\', $fqn);

    return array_pop($explode);
}

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

namespace Chevere\Tests\src;

use Chevere\Attributes\Description;
use Chevere\Attributes\Regex;
use Chevere\Http\Controller;

class PutController extends Controller
{
    public function run(
        #[Description('The user integer id')]
        #[Regex('/^[0-9]+$/')]
        string $id,
        #[Description('The user name')]
        #[Regex('/^[\w]+$/')]
        string $name
    ): array {
        return [];
    }
}

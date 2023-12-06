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

use Chevere\Http\Controller;
use Chevere\Parameter\Attributes\StringAttr;

class PutController extends Controller
{
    public function run(
        #[StringAttr('/^[0-9]+$/', 'The user integer id')]
        string $id,
        #[StringAttr('/^[\w]+$/', 'The user name')]
        string $name
    ): array {
        return [];
    }
}

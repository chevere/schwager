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

namespace Chevere\Tests\_resources\src;

use Chevere\Attribute\StringAttribute;
use Chevere\HttpController\HttpController;

class PutController extends HttpController
{
    public function run(
        #[StringAttribute('/^[0-9]+$/')]
        string $id,
        #[StringAttribute('/^[\w]+$/')]
        string $name
    ): array {
        return [];
    }
}

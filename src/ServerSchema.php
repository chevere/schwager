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

final class ServerSchema implements ToArrayInterface
{
    public function __construct(
        private string $url,
        private string $description,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'description' => $this->description,
        ];
    }
}

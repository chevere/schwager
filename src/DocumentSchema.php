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

final class DocumentSchema implements ToArrayInterface
{
    public function __construct(
        private string $api = 'chevere',
        private string $name = 'Chevere API',
        private string $version = '1.0.0',
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'api' => $this->api,
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}

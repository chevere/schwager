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

use Chevere\Schwager\Interfaces\SchemaInterface;

final class DocumentSchema implements SchemaInterface
{
    public function __construct(
        public readonly string $api = 'chevere',
        public readonly string $name = 'Chevere API',
        public readonly string $version = '1.0.0',
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

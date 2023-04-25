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

namespace Chevere\Tests;

use Chevere\Schwager\ServerSchema;
use PHPUnit\Framework\TestCase;

final class ServerSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $url = 'http://localhost';
        $description = 'Localhost';
        $schema = new ServerSchema($url, $description);
        $this->assertSame([
            'url' => $url,
            'description' => $description,
        ], $schema->toArray());
    }
}

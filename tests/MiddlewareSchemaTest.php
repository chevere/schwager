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

use Chevere\Http\MiddlewareName;
use Chevere\Schwager\MiddlewareSchema;
use Chevere\Tests\_resources\src\MiddlewareOne;
use PHPUnit\Framework\TestCase;

final class MiddlewareSchemaTest extends TestCase
{
    public function testConstruct(): void
    {
        $middlewareName = new MiddlewareName(MiddlewareOne::class);
        $schema = new MiddlewareSchema($middlewareName);
        $this->assertSame([
            'context' => 'MiddlewareOne',
            'headers' => [
                'Content-Disposition' => 'inline',
                'Content-Type' => 'application/json',
            ],
            'body' => $schema->acceptBody()->schema(),
        ], $schema->toArray());
    }
}
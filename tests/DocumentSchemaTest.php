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

use Chevere\Schwager\DocumentSchema;
use PHPUnit\Framework\TestCase;

final class DocumentSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $api = 'test';
        $name = 'Test API';
        $version = '1.0.0-test';
        $schema = new DocumentSchema(
            api: $api,
            name: $name,
            version: $version,
        );
        $this->assertSame([
            'api' => $api,
            'name' => $name,
            'version' => $version,
        ], $schema->toArray());
    }
}

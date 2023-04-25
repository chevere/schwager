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

use Chevere\Router\Wildcard;
use Chevere\Router\WildcardMatch;
use Chevere\Schwager\WildcardSchema;
use PHPUnit\Framework\TestCase;

final class WildcardSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $match = new WildcardMatch('[0-9]+');
        $wildcard = new Wildcard('id', $match);
        $description = 'Test';
        $schema = new WildcardSchema($wildcard, $description);
        $this->assertSame([
            'required' => true,
            'type' => 'string',
            'description' => $description,
            'regex' => $wildcard->match()->anchored(),
        ], $schema->toArray());
    }
}

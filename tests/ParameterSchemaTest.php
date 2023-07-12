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

use Chevere\Schwager\ParameterSchema;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\integer;

final class ParameterSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $parameter = integer(minimum: 0, maximum: 100);
        $isRequired = true;
        $schema = new ParameterSchema($parameter, $isRequired);
        $this->assertSame([
            'required' => $isRequired,
        ] + $parameter->schema(), $schema->toArray());
    }
}

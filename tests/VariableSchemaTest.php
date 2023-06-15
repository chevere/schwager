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

use Chevere\Router\Variable;
use Chevere\Router\VariableRegex;
use Chevere\Schwager\VariableSchema;
use PHPUnit\Framework\TestCase;

final class VariableSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $regex = new VariableRegex('[0-9]+');
        $variable = new Variable('id', $regex);
        $description = 'Test';
        $schema = new VariableSchema($variable, $description);
        $this->assertSame([
            'required' => true,
            'type' => 'string',
            'description' => $description,
            'regex' => $variable->regex()->noDelimiters(),
        ], $schema->toArray());
    }
}

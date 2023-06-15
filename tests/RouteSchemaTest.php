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

use Chevere\Http\Methods\GetMethod;
use Chevere\Http\Methods\PutMethod;
use function Chevere\Router\bind;
use Chevere\Router\Endpoint;
use function Chevere\Router\route;
use Chevere\Schwager\EndpointSchema;
use Chevere\Schwager\RouteSchema;
use Chevere\Schwager\VariableSchema;
use Chevere\Tests\_resources\src\GetController;
use Chevere\Tests\_resources\src\PutController;
use PHPUnit\Framework\TestCase;

final class RouteSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $route = route('/{name}/user/{id}');
        $getEndpoint = new Endpoint(
            new GetMethod(),
            bind(GetController::class)
        );
        $putEndpoint = new Endpoint(
            new PutMethod(),
            bind(PutController::class)
        );
        $parameters = $getEndpoint->bind()->controllerName()->__toString()::getParameters();
        $route = $route
            ->withEndpoint($putEndpoint)
            ->withEndpoint($getEndpoint);
        $group = 'test';
        $schema = new RouteSchema($route, $group);
        $idVariableSchema = new VariableSchema(
            $route->path()->variables()->get('id'),
            $parameters->get('id')->description()
        );
        $nameVariableSchema = new VariableSchema(
            $route->path()->variables()->get('name'),
            $parameters->get('name')->description()
        );
        $this->assertSame(
            [
                'name' => $route->name(),
                'group' => $group,
                'regex' => $route->path()->regex()->noDelimiters(),
                'variables' => [
                    'id' => $idVariableSchema->toArray(),
                    'name' => $nameVariableSchema->toArray(),
                ],
                'endpoints' => [
                    'GET' => (new EndpointSchema($getEndpoint))->toArray(),
                    'PUT' => (new EndpointSchema($putEndpoint))->toArray(),
                ],
            ],
            $schema->toArray()
        );
    }
}

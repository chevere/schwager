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
use Chevere\Http\MiddlewareName;
use Chevere\Http\Middlewares;
use Chevere\Router\Endpoint;
use Chevere\Schwager\EndpointSchema;
use Chevere\Schwager\MiddlewareSchema;
use Chevere\Tests\src\GetController;
use Chevere\Tests\src\MiddlewareOne;
use PHPUnit\Framework\TestCase;
use function Chevere\Http\responseAttribute;
use function Chevere\Router\bind;

final class EndpointSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $method = new GetMethod();
        $controllerName = GetController::class;
        $response = responseAttribute($controllerName);
        $controllerStatus = $response->status;
        $middlewareName = new MiddlewareName(MiddlewareOne::class);
        $response = responseAttribute(MiddlewareOne::class);
        $middlewareStatus = $response->status;
        $middlewares = new Middlewares($middlewareName);
        $bind = bind($controllerName, $middlewares);
        $endpoint = new Endpoint($method, $bind);
        $schema = new EndpointSchema($endpoint);
        $date = $controllerName::acceptQuery()->parameters()->get('date')->schema();
        $time = $controllerName::acceptQuery()->parameters()->get('time')->schema();
        $responses = [];
        $middlewareSchema = new MiddlewareSchema($middlewareName);
        $responses[$middlewareStatus->primary][] = $middlewareSchema->toArray();
        $headers = [
            'foo: bar',
            'esta: wea',
        ];
        $responses[$controllerStatus->primary][] = [
            'context' => 'GetController',
            'headers' => $headers,
            'body' => $controllerName::acceptResponse()->schema(),
        ];
        $responses[403][] = [
            'context' => 'GetController',
        ];
        ksort($responses);
        $this->assertSame([
            'description' => $endpoint->description(),
            'request' => [
                'headers' => [],
                'query' => [
                    'date' => [
                        'required' => true,
                    ] + $date,
                    'time' => [
                        'required' => false,
                    ] + $time,
                ],
                'body' => $controllerName::acceptBody()->schema(),
            ],
            'responses' => $responses,
        ], $schema->toArray());
    }
}

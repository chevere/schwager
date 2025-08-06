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
use Chevere\Router\Endpoint;
use Chevere\Schwager\EndpointSchema;
use Chevere\Tests\src\GetController;
use Chevere\Tests\src\MiddlewareOne;
use PHPUnit\Framework\TestCase;
use function Chevere\Http\responseAttribute;
use function Chevere\Router\headless;

final class EndpointSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $method = new GetMethod();
        $controllerName = GetController::class;
        $controllerResponse = responseAttribute($controllerName);
        $controllerStatus = $controllerResponse->status;
        $middlewareResponse = responseAttribute(MiddlewareOne::class);
        $middlewareStatus = $middlewareResponse->status;
        $bind = headless($controllerName, middleware: MiddlewareOne::class);
        $endpoint = new Endpoint($method, $bind);
        $schema = new EndpointSchema($endpoint);
        $date = $controllerName::acceptQuery()->parameters()->get('date')->schema();
        $time = $controllerName::acceptQuery()->parameters()->get('time')->schema();
        $responses = [];
        $responses[$middlewareStatus->success()][] = [
            'context' => 'MiddlewareOne',
            'headers' => [],
        ];
        $responses[$controllerStatus->success()][] = [
            'context' => 'GetController',
            'headers' => [
                'foo: bar',
                'esta: wea',
            ],
            'body' => $controllerName::return()->schema(),
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

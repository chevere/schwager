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
use function Chevere\Router\bind;
use Chevere\Router\Endpoint;
use function Chevere\Schwager\classStatuses;
use Chevere\Schwager\EndpointSchema;
use Chevere\Schwager\MiddlewareSchema;
use Chevere\Tests\_resources\src\GetController;
use Chevere\Tests\_resources\src\MiddlewareOne;
use PHPUnit\Framework\TestCase;

final class EndpointSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $method = new GetMethod();
        $controller = GetController::class;
        $middlewareName = new MiddlewareName(MiddlewareOne::class);
        $middlewareStatuses = classStatuses(MiddlewareOne::class);
        $middlewares = new Middlewares($middlewareName);
        $bind = bind($controller, $middlewares);
        $endpoint = new Endpoint($method, $bind);
        $schema = new EndpointSchema($endpoint);
        $date = $controller::acceptQuery()->parameters()->get('date')->schema();
        $time = $controller::acceptQuery()->parameters()->get('time')->schema();
        $response = [
            200 => [
                'headers' => $controller::responseHeaders(),
                'body' => $controller::acceptResponse()->schema(),
            ],
        ];
        $response[$middlewareStatuses->primary] = (new MiddlewareSchema($middlewareName))->toArray();
        ksort($response);
        $this->assertSame([
            'description' => $endpoint->description(),
            'query' => [
                'date' => [
                    'required' => true,
                ] + $date,
                'time' => [
                    'required' => false,
                ] + $time,
            ],
            'body' => $controller::acceptBody()->schema(),
            'response' => $response,
        ], $schema->toArray());
    }
}

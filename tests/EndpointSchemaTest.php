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
use function Chevere\Router\bind;
use Chevere\Router\Endpoint;
use Chevere\Schwager\EndpointSchema;
use Chevere\Tests\_resources\src\GetController;
use PHPUnit\Framework\TestCase;

final class EndpointSchemaTest extends TestCase
{
    public function testSchema(): void
    {
        $method = new GetMethod();
        $controller = GetController::class;
        $bind = bind($controller);
        $endpoint = new Endpoint($method, $bind);
        $schema = new EndpointSchema($endpoint);
        $date = $controller::acceptQuery()->items()->get('date')->schema();
        $time = $controller::acceptQuery()->items()->get('time')->schema();
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
            'response' => [
                $controller::statusSuccess() => [
                    'headers' => $controller::responseHeaders(),
                    'body' => $controller::acceptResponse()->schema(),
                ],
                $controller::statusError() => [
                    'headers' => $controller::responseHeaders(),
                    'body' => $controller::acceptError()->schema(),
                ],
            ],
        ], $schema->toArray());
    }
}

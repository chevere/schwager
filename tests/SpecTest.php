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
use Chevere\Schwager\DocumentSchema;
use Chevere\Schwager\ServerSchema;
use Chevere\Schwager\Spec;
use Chevere\Tests\src\GetController;
use PHPUnit\Framework\TestCase;
use function Chevere\Router\bind;
use function Chevere\Router\route;
use function Chevere\Router\router;
use function Chevere\Router\routes;

final class SpecTest extends TestCase
{
    public function testBuild(): void
    {
        $get = new GetMethod();
        $route = route('/user/{id}/{name}')
            ->withEndpoint(
                new Endpoint(
                    $get,
                    bind(GetController::class)
                )
            );
        $routeAlt = route('/customer/{id}/{name}')
            ->withEndpoint(
                new Endpoint(
                    $get,
                    bind(GetController::class)
                )
            );
        $router = router(routes($route, $routeAlt));
        $document = new DocumentSchema();
        $testServer = new ServerSchema('testServerUrl', 'test');
        $productionServer = new ServerSchema('productionServerUrl', 'test');
        $spec = new Spec($router, $document, $testServer, $productionServer);
        $this->assertSame($document, $spec->document());
        $this->assertSame([$testServer, $productionServer], $spec->servers());
        $array = $spec->toArray();
        $this->assertSame([
            $testServer->toArray(),
            $productionServer->toArray(),
        ], $array['servers']);
        $this->assertSame(
            ['/customer/{id}/{name}', '/user/{id}/{name}'],
            array_keys($array['paths'])
        );
    }
}

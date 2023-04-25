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
use Chevere\Router\Bind;
use Chevere\Router\Endpoint;
use function Chevere\Router\route;
use function Chevere\Router\router;
use function Chevere\Router\routes;
use Chevere\Schwager\DocumentSchema;
use Chevere\Schwager\ServerSchema;
use Chevere\Schwager\Spec;
use Chevere\Tests\_resources\src\GetController;
use PHPUnit\Framework\TestCase;

final class SpecTest extends TestCase
{
    public function testBuild(): void
    {
        $get = new GetMethod();
        $route = route('/user/{id}');
        $route = $route
            ->withEndpoint(
                new Endpoint(
                    $get,
                    new Bind(new GetController())
                )
            );
        $router = router(routes($route));
        $document = new DocumentSchema();
        $testServer = new ServerSchema('testServerUrl', 'test');
        $productionServer = new ServerSchema('productionServerUrl', 'test');
        $spec = new Spec($router, $document, $testServer, $productionServer);
        $array = $spec->toArray();
        $this->assertSame([
            $testServer->toArray(),
            $productionServer->toArray(),
        ], $array['servers']);
        $this->assertCount(1, $array['paths']);
    }
}

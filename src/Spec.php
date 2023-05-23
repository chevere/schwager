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

namespace Chevere\Schwager;

use Chevere\Common\Interfaces\ToArrayInterface;
use Chevere\Router\Interfaces\RouteInterface;
use Chevere\Router\Interfaces\RouterInterface;

final class Spec implements ToArrayInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $array;

    public function __construct(
        private RouterInterface $router,
        ToArrayInterface $document,
        ToArrayInterface ...$server
    ) {
        // @phpstan-ignore-next-line
        $this->array = $document->toArray();
        $this->array['servers'] = $this->getServers(...$server);
        foreach ($router->routes() as $id => $route) {
            $this->putPath($id, $route);
        }
        // @phpstan-ignore-next-line
        asort($this->array['paths']);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->array;
    }

    /**
     * @return array<int<0, max>, array<string, string>>
     */
    private function getServers(ToArrayInterface ...$server): array
    {
        $array = [];
        foreach ($server as $schema) {
            $array[] = $schema->toArray();
        }
        // @phpstan-ignore-next-line
        return $array;
    }

    private function putPath(string $id, RouteInterface $route): void
    {
        $group = $this->router->index()->getRouteGroup($id);
        $schema = new RouteSchema($route, $group);
        // @phpstan-ignore-next-line
        $this->array['paths'][$route->path()->handle()] = $schema->toArray();
    }
}

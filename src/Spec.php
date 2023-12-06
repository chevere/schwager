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

use Chevere\Router\Interfaces\RouteInterface;
use Chevere\Router\Interfaces\RouterInterface;

final class Spec
{
    /**
     * @var array<string, mixed>
     */
    private array $array;

    /**
     * @var array<ServerSchema>
     */
    private array $servers;

    public function __construct(
        private RouterInterface $router,
        private DocumentSchema $document,
        ServerSchema ...$server
    ) {
        $this->array = $document->toArray();
        $this->servers = $server;
        $this->array['servers'] = $this->getServers(...$server);
        foreach ($router->routes() as $id => $route) {
            $this->putPath($id, $route);
        }
        // @phpstan-ignore-next-line
        asort($this->array['paths']);
    }

    public function document(): DocumentSchema
    {
        return $this->document;
    }

    /**
     * @return array<ServerSchema>
     */
    public function servers(): array
    {
        return $this->servers;
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
    private function getServers(ServerSchema ...$server): array
    {
        $array = [];
        foreach ($server as $schema) {
            $array[] = $schema->toArray();
        }

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

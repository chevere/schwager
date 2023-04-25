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
use Chevere\Router\Interfaces\EndpointInterface;
use Chevere\Router\Interfaces\RouteInterface;

final class RouteSchema implements ToArrayInterface
{
    private EndpointInterface $firstEndpoint;

    public function __construct(
        private RouteInterface $route,
        private string $group
    ) {
        $iterator = $route->endpoints()->getIterator();
        $iterator->rewind(); // @codeCoverageIgnore
        $this->firstEndpoint = $iterator->current();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->route->name(),
            'group' => $this->group,
            'regex' => $this->route->path()->regex()->noDelimiters(),
            'wildcards' => $this->getWildcards($this->route),
            'endpoints' => $this->getEndpoints($this->route),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getEndpoints(RouteInterface $route): array
    {
        $array = [];
        foreach ($route->endpoints() as $endpoint) {
            $schema = new EndpointSchema($endpoint);
            $array[$endpoint->method()->name()] = $schema->toArray();
        }

        return $array;
    }

    /**
     *  @return array<string, mixed>
     */
    private function getWildcards(RouteInterface $route): array
    {
        $array = [];
        foreach ($route->path()->wildcards() as $name => $wildcard) {
            $parameters = $this->firstEndpoint->bind()->controller()->parameters();
            $description = $parameters->get($name)->description();
            $schema = new WildcardSchema($wildcard, $description);
            $array[$name] = $schema->toArray();
        }

        return $array;
    }
}

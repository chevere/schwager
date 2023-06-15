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
use function Chevere\Parameter\string;
use Chevere\Router\Interfaces\EndpointInterface;
use Chevere\Router\Interfaces\RouteInterface;

final class RouteSchema implements ToArrayInterface
{
    private EndpointInterface $firstEndpoint;

    /**
     * @var array<string, mixed>
     */
    private array $array;

    public function __construct(
        private RouteInterface $route,
        private string $group
    ) {
        $iterator = $route->endpoints()->getIterator();
        $iterator->rewind(); // @codeCoverageIgnore
        $this->firstEndpoint = $iterator->current();
        $this->array = [
            'name' => $this->route->name(),
            'group' => $this->group,
            'regex' => $this->route->path()->regex()->noDelimiters(),
            'variables' => $this->getVariables($this->route),
            'endpoints' => $this->getEndpoints($this->route),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->array;
    }

    /**
     * @return array<string, mixed>
     */
    private function getEndpoints(RouteInterface $route): array
    {
        $return = [];
        foreach ($route->endpoints() as $endpoint) {
            $schema = new EndpointSchema($endpoint);
            $return[$endpoint->method()->name()] = $schema->toArray();
        }
        ksort($return);

        return $return;
    }

    /**
     *  @return array<string, mixed>
     */
    private function getVariables(RouteInterface $route): array
    {
        $array = [];
        foreach ($route->path()->variables() as $name => $variable) {
            $parameters = $this->firstEndpoint->bind()->controllerName()->__toString()::getParameters();
            $description = $parameters->get($name)->description();
            $schema = new VariableSchema($variable, $description);
            $array[$name] = $schema->toArray();
        }
        ksort($array);

        return $array;
    }
}

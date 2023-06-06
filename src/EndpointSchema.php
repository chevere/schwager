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
use function Chevere\Http\classHeaders;
use function Chevere\Http\classStatus;
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\string;
use Chevere\Router\Interfaces\EndpointInterface;

final class EndpointSchema implements ToArrayInterface
{
    /**
     * @var array<string|int, array<string, mixed>>
     */
    private array $middlewares = [];

    public function __construct(
        private EndpointInterface $endpoint,
    ) {
        foreach ($endpoint->bind()->middlewares() as $middleware) {
            $class = $middleware->__toString();
            $status = classStatus($class);
            $schema = new MiddlewareSchema($middleware);
            $this->middlewares[$status->primary] = $schema->toArray();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $controller = $this->endpoint->bind()->controllerName()->__toString();
        $headers = classHeaders($controller);
        $status = classStatus($controller);
        $statuses = array_fill_keys($status->other, [
            'context' => $this->getShortName($controller),
        ]);
        /** @var ControllerInterface $controller */
        $return = [
            'description' => $this->endpoint->description(),
            'query' => $this->getQuerySchema(
                $controller::acceptQuery()->parameters()
            ),
            'body' => $controller::acceptBody()->schema(),
            'response' => [
                $status->primary => [
                    'headers' => $headers->toArray(),
                    'body' => $controller::acceptResponse()->schema(),
                ],
            ] + $statuses + $this->middlewares,
        ];
        ksort($return['response']);

        return $return;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getQuerySchema(ParametersInterface $parameters): array
    {
        $array = [];
        foreach ($parameters as $id => $parameter) {
            $schema = new ParameterSchema(
                $parameter,
                $parameters->isRequired($id),
            );
            $array[$id] = $schema->toArray();
        }

        return $array;
    }

    private function getShortName(string $name): string
    {
        $explode = explode('\\', $name);

        return array_pop($explode);
    }
}

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
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\string;
use Chevere\Router\Interfaces\EndpointInterface;
use ReflectionClass;

final class EndpointSchema implements ToArrayInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $middlewares = [];

    public function __construct(
        private EndpointInterface $endpoint,
    ) {
        foreach ($endpoint->bind()->middlewares() as $middleware) {
            $key = strval($middleware->__toString()::statusError());
            $schema = new MiddlewareSchema($middleware);
            $this->middlewares[$key] = $schema->toArray();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var ControllerInterface $controller */
        $controller = $this->endpoint->bind()->controllerName()->__toString();
        // $reflection = new ReflectionClass($controller);
        // $attributes = $reflection->getAttributes();
        // foreach ($attributes as $attribute) {
        //     vdd($attribute->newInstance());
        // }
        $return = [
            'description' => $this->endpoint->description(),
            'query' => $this->getQuerySchema(
                $controller::acceptQuery()->parameters()
            ),
            'body' => $controller::acceptBody()->schema(),
            'response' => [
                $controller::statusSuccess() => [
                    'headers' => $controller::responseHeaders(),
                    'body' => $controller::acceptResponse()->schema(),
                ],
            ] + $this->middlewares,
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
}

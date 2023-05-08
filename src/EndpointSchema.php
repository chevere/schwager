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
use Chevere\Http\Interfaces\MiddlewaresInterface;
use Chevere\HttpController\Interfaces\HttpControllerInterface;
use function Chevere\Parameter\arrayp;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\string;
use Chevere\Router\Interfaces\EndpointInterface;

final class EndpointSchema implements ToArrayInterface
{
    /**
     * @var array<int|string, array<string, mixed>>
     */
    private array $middlewares = [];

    public function __construct(
        private EndpointInterface $endpoint,
        MiddlewaresInterface $middlewares = null
    ) {
        if ($middlewares === null) {
            return;
        }
        foreach ($middlewares as $middleware) {
            $this->middlewares[$middleware->__toString()::statusError()] = $this->middlewareSchema();
        }
    }

    /**
     * @return array<string, mixed>
     * @infection-ignore-all
     */
    public function middlewareSchema(): array
    {
        return [
            'headers' => [
                'Content-Disposition' => 'inline',
                'Content-Type' => 'application/json',
            ],
            'body' => arrayp(
                code: string(),
                message: string()
            )->schema(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var HttpControllerInterface $controller */
        $controller = $this->endpoint->bind()->controllerName()->__toString();

        return [
            'description' => $this->endpoint->description(),
            'query' => $this->getQuerySchema(
                $controller::acceptQuery()->items()
            ),
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
            ] + $this->middlewares,
        ];
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

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
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Router\Interfaces\EndpointInterface;
use function Chevere\Http\getRequest;
use function Chevere\Http\getResponse;
use function Chevere\Parameter\string;

final class EndpointSchema implements ToArrayInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $array = [];

    /**
     * @var array<int, array<int|string, mixed>>
     */
    private array $responses;

    public function __construct(
        private EndpointInterface $endpoint,
    ) {
        $this->responses = [];
        foreach ($endpoint->bind()->middlewares() as $middleware) {
            $class = $middleware->__toString();
            // $request = getRequest($class);
            $response = getResponse($class);
            $schema = new MiddlewareSchema($middleware);
            $this->responses[$response->status->primary][] = $schema->toArray();
        }
        $controller = $this->endpoint->bind()->controllerName()->__toString();
        // $request = getRequest($controller);
        $response = getResponse($controller);
        $statuses = $response->status->toArray();
        $statuses = array_fill_keys($statuses, [
            'context' => $this->getShortName($controller),
            'headers' => $response->headers->toExport(),
        ]);
        foreach ($statuses as $code => $array) {
            if ($code === $response->status->primary) {
                $array['body'] = $controller::acceptResponse()->schema();
            }
            $this->responses[$code][] = $array;
        }
        ksort($this->responses);
        $this->array = [
            'description' => $this->endpoint->description(),
            'query' => $this->getQuerySchema(
                $controller::acceptQuery()->parameters()
            ),
            'body' => $controller::acceptBody()->schema(),
            'responses' => $this->responses,
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

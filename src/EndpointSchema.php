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
use function Chevere\Http\requestAttribute;
use function Chevere\Http\responseAttribute;
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
    private array $responses = [];

    public function __construct(
        private EndpointInterface $endpoint,
    ) {
        $requestHeaders = [];
        foreach ($endpoint->bind()->middlewares() as $middleware) {
            $class = $middleware->__toString();
            $request = requestAttribute($class);
            $schema = new MiddlewareSchema($middleware);
            $requestHeaders[] = $schema->request()['headers'];
            foreach ($schema->responses() as $code => $responses) {
                $this->responses[$code] = $responses;
            }
        }
        $controller = $this->endpoint->bind()->controllerName()->__toString();
        $request = requestAttribute($controller);
        $response = responseAttribute($controller);
        $statuses = $response->status->toArray();
        $statuses = array_fill_keys($statuses, [
            'context' => shortName($controller),
        ]);
        foreach ($statuses as $code => $array) {
            if ($code === $response->status->primary) {
                $array['headers'] = $response->headers->toArray();
                $array['body'] = $controller::acceptResponse()->schema();
            }
            $this->responses[$code][] = $array;
        }
        ksort($this->responses);
        $requestHeaders = array_filter($requestHeaders);
        array_push($requestHeaders, ...$request->headers->toArray());
        $this->array = [
            'description' => $this->endpoint->description(),
            'request' => [
                'headers' => $requestHeaders,
                'query' => $this->getQuerySchema(
                    $controller::acceptQuery()->parameters()
                ),
                'body' => $controller::acceptBody()->schema(),
            ],
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
}

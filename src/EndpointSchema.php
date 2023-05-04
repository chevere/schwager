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
use function Chevere\Parameter\integer;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Router\Interfaces\EndpointInterface;

final class EndpointSchema implements ToArrayInterface
{
    public function __construct(
        private EndpointInterface $endpoint,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $controller = $this->endpoint->bind()->controllerName();

        return [
            'description' => $this->endpoint->description(),
            'query' => $this->getQuerySchema(
                $controller::acceptQuery()->items()
            ),
            'body' => $controller::acceptBody()->schema(),
            'response' => [
                'success' => [
                    'code' => $controller::statusSuccess(),
                    'headers' => $controller::responseHeaders(),
                    'body' => $controller::acceptResponse()->schema(),
                ],
                'error' => [
                    'code' => integer(minimum: 400, maximum: 599)->schema(),
                    'headers' => $controller::responseHeaders(),
                    'body' => $controller::acceptError()->schema(),
                ],
            ],
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

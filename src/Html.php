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

use Stringable;
use function Chevere\Standard\arrayUnsetKey;

/**
 * @codeCoverageIgnore
 */
final class Html implements Stringable
{
    public const TEMPLATES_DIR = __DIR__ . '/Template/';

    private string $html;

    private string $descriptionHtml;

    private string $pathHtml;

    private string $variableHtml;

    private string $variableNameHtml;

    private string $statusCodeHtml;

    private string $variablesHtml;

    private string $requestHtml;

    private string $responseHtml;

    private string $responseDescriptionHtml;

    private string $responseListHtml;

    private string $responsesHtml;

    private string $endpointHtml;

    private string $endpointsHtml;

    private string $optionalHtml;

    private string $serverHtml;

    private string $serversHtml;

    private string $descriptionList;

    public function __construct(
        private Spec $spec,
        private array $array = []
    ) {
        $this->onConstruct();
        $servers = '';
        foreach ($this->spec->servers() as $server) {
            $search = [
                '%url%',
                '%description%',
            ];
            $replace = [
                $server->url,
                $server->description,
            ];
            $servers .= str_replace($search, $replace, $this->serverHtml);
        }
        $servers = str_replace('%servers%', $servers, $this->serversHtml);
        $this->html = str_replace('%servers.html%', $servers, $this->html);
        $paths = '';
        foreach ($this->array['paths'] as $uri => $path) {
            $variables = $this->variables($path['variables'] ?? []);
            $endpoints = $this->endpoints($path['name'], $path['endpoints']);
            $search = [
                '%path%',
                '%name%',
                '%regex%',
                '%variables.html%',
                '%endpoints.html%',
            ];
            $replace = [
                $uri,
                $path['name'],
                $path['regex'],
                $variables,
                $endpoints,
            ];
            $paths .= str_replace($search, $replace, $this->pathHtml);
        }
        $this->html = str_replace('%paths.html%', $paths, $this->html);
    }

    public function __toString()
    {
        return $this->html;
    }

    public function div(string $content, string $class = ''): string
    {
        return $this->tag('div', $class, $content);
    }

    public function code(string $content, string $class = ''): string
    {
        return $this->tag('code', $class, $content);
    }

    public function getTemplate(string $name): string
    {
        return file_get_contents(self::TEMPLATES_DIR . $name);
    }

    public function description(string $title, string $description): string
    {
        if ($description === '') {
            return '';
        }

        return str_replace(
            [
                '%dt%',
                '%dd%',
            ],
            [
                $title,
                $description,
            ],
            $this->descriptionHtml
        );
    }

    public function variables(array $variables): string
    {
        $return = '';
        foreach ($variables as $name => $variable) {
            $search = [
                '%name%',
                '%type%',
                '%regex%',
                '%description%',
            ];
            $replace = [
                str_replace('%name%', $name, $this->variableNameHtml),
                $this->description('Type', $this->code($variable['type'] ?? '')),
                $this->description('Regex', $this->code($variable['regex'] ?? '')),
                $this->description('Description', $variable['description'] ?? ''),
            ];
            $return .= str_replace($search, $replace, $this->variableHtml);
        }

        return str_replace('%variables%', $return, $this->variablesHtml);
    }

    public function query(array $query): string
    {
        $return = '';
        foreach ($query as $name => $string) {
            $properties = '';
            $map = arrayUnsetKey($string, 'required', 'type');
            foreach ($map as $property => $value) {
                $properties .= $this->description(
                    $property,
                    (string) ($value ?? '')
                );
            }
            $return .= $this->description(
                $name,
                $this->code($string['type'])
                    . ($string['required'] ? '' : $this->optionalHtml)
                    . $this->descriptionList($properties)
            );
        }

        return $return;
    }

    public function body(array $parameters): string
    {
        $body = '';
        foreach ($parameters as $name => $parameter) {
            $map = arrayUnsetKey($parameter, 'required', 'type');
            $properties = '';
            foreach ($map as $property => $value) {
                if (! is_scalar($value)) {
                    continue;
                }
                $properties .= $this->description(
                    $property,
                    $this->div((string) ($value ?? ''))
                );
            }
            $body .= $this->description(
                $name,
                $this->code($parameter['type'])
                    . (($parameter['required'] ?? true) ? '' : $this->optionalHtml)
                    . $this->descriptionList($properties)
            );
        }

        return $body;
    }

    public function request(array $endpoint): string
    {
        $search = [
            '%headers%',
            '%query%',
            '%body%',
        ];
        $query = $this->query($endpoint['query'] ?? []);
        $body = $this->body($endpoint['body']['parameters'] ?? []);
        $headers = $this->headers($endpoint['request']['headers'] ?? []);
        $replace = [
            $this->description('Headers', $headers),
            $this->description('Query', $this->code('array&lt;string&gt;'))
            . $this->descriptionList($query),
            '',
        ];
        if ($body !== '') {
            $replace[2] .= $this->description(
                'Body',
                $this->code($endpoint['body']['type'])
                . $this->div($endpoint['body']['description'] ?? '')
            )
            . $this->descriptionList($body);
        }

        return str_replace($search, $replace, $this->requestHtml);
    }

    // public function response(array $endpoint): string
    // {
    //     $search = [
    //         '%headers%',
    //         '%query%',
    //         '%body%',
    //     ];
    //     $body = $this->body($endpoint['body']['parameters'] ?? []);
    //     $replace = [
    //         $this->description('Headers', '__placeholder__'),
    //         $this->description('Query', $this->code('array&lt;string&gt;'))
    //         . $this->descriptionList($query ?? ''),
    //     ];

    //     if ($body !== '') {
    //         $replace[] .= $this->description(
    //             'Body',
    //             $this->code($endpoint['body']['type'])
    //             . $this->div($endpoint['body']['description'] ?? '')
    //         )
    //         . $this->descriptionList($body);
    //     }

    //     return str_replace($search, $replace, $this->responseHtml);
    // }

    public function headers(array $headers): string
    {
        $array = [];
        foreach ($headers as $name => $value) {
            $array[] = $name . ' ' . $value;
        }

        return implode('<br>', $array ?? []);
    }

    public function responses(array $array): string
    {
        $responses = '';
        foreach ($array as $code => $el) {
            $descriptions = '';
            $code = (string) $code;
            $search = [
                '%context%',
                '%headers%',
                '%body%',
            ];
            foreach ($el as $response) {
                $body = $this->body($response['body']['parameters'] ?? []);
                $headers = $this->headers($response['headers'] ?? []);
                $replace = [
                    $el['context'] ?? '',
                    $this->description('Headers', $headers),
                    '',
                ];
                if ($body !== '') {
                    $replace[2] .= $this->description(
                        'Body',
                        $this->code($response['body']['type'])
                        . $this->div($response['body']['description'] ?? '')
                    )
                    . $this->descriptionList($body);
                }
                $descriptions .= str_replace(
                    $search,
                    $replace,
                    $this->responseDescriptionHtml
                );
            }
            $responses .= str_replace(
                [
                    '%code%',
                    '%responses%',
                ],
                [
                    str_replace('%code%', $code, $this->statusCodeHtml),
                    $descriptions,
                ],
                $this->responseListHtml
            );
        }

        return str_replace('%response-list.html%', $responses, $this->responseHtml);
    }

    public function descriptionList(string $description): string
    {
        if ($description === '') {
            return '';
        }

        return str_replace('%list%', $description, $this->descriptionList);
    }

    public function endpoints(string $pathId, array $endpoints): string
    {
        $return = '';
        foreach ($endpoints as $method => $endpoint) {
            $request = $this->request($endpoint);
            $responses = $this->responses($endpoint['responses'] ?? []);
            $return .= str_replace(
                [
                    '%request.html%', '%responses.html%'],
                [$request, $responses],
                $this->endpointHtml
            );
            $replace = [
                '%method%' => $method,
                '%md5%' => md5($pathId . $method),
                '%description%' => $endpoint['description'],
            ];
            $return = strtr($return, $replace);
        }

        return str_replace('%endpoints%', $return, $this->endpointsHtml);
    }

    private function onConstruct(): void
    {
        if ($this->array === []) {
            $this->array = $this->spec->toArray();
        }
        $this->html = $this->getTemplate('main.html');
        $search = [
            '%name%',
            '%version%',
        ];
        $replace = [
            $this->spec->document()->name,
            $this->spec->document()->version,
        ];
        $this->html = str_replace($search, $replace, $this->html);
        $this->descriptionHtml = $this->getTemplate('description.html');
        $this->pathHtml = $this->getTemplate('path.html');
        $this->variableHtml = $this->getTemplate('variable.html');
        $this->variableNameHtml = $this->getTemplate('variable-name.html');
        $this->variablesHtml = $this->getTemplate('variables.html');
        $this->requestHtml = $this->getTemplate('request.html');
        $this->responseHtml = $this->getTemplate('response.html');
        $this->responseListHtml = $this->getTemplate('response-list.html');
        $this->responseDescriptionHtml = $this->getTemplate('response-description.html');
        $this->endpointHtml = $this->getTemplate('endpoint.html');
        $this->endpointsHtml = $this->getTemplate('endpoints.html');
        $this->statusCodeHtml = $this->getTemplate('status-code.html');
        $this->optionalHtml = $this->getTemplate('optional.html');
        $this->serverHtml = $this->getTemplate('server.html');
        $this->serversHtml = $this->getTemplate('servers.html');
        $this->descriptionList = $this->getTemplate('description-list.html');
    }

    private function tag(string $tag, string $class, string $content): string
    {
        $attribute = match ($class) {
            '' => '',
            default => <<<HTML
             class="{$class}"
            HTML
        };

        return match ($content) {
            '' => '',
            default => <<<HTML
            <{$tag}{$attribute}>{$content}</{$tag}>
            HTML,
        };
    }
}

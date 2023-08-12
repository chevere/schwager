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

    private string $variablesHtml;

    private string $requestHtml;

    private string $endpointHtml;

    private string $endpointsHtml;

    private string $optionalHtml;

    private string $serverHtml;

    private string $serversHtml;

    public function __construct(
        private Spec $spec,
        private array $array = []
    ) {
        $this->loadTemplates();
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
            $name = $path['name'];
            $variables = '';
            foreach ($path['variables'] ?? [] as $variableName => $variable) {
                $search = [
                    '%name%',
                    '%type%',
                    '%regex%',
                    '%description%',
                ];
                $replace = [
                    str_replace('%name%', $variableName, $this->variableNameHtml),
                    $this->description('Type', $this->code($variable['type'] ?? '')),
                    $this->description('Regex', $this->code($variable['regex'] ?? '')),
                    $this->description('Description', $variable['description'] ?? ''),
                ];
                $variables .= str_replace($search, $replace, $this->variableHtml);
            }
            $variables = str_replace('%variables%', $variables, $this->variablesHtml);

            $endpoints = '';
            foreach ($path['endpoints'] as $method => $endpoint) {
                $search = [
                    '%headers%',
                    '%query%',
                    '%body%',
                ];
                $query = '';
                foreach ($endpoint['query'] ?? [] as $queryName => $el) {
                    $properties = '';
                    $map = arrayUnsetKey($el, 'required', 'type');
                    foreach ($map as $property => $value) {
                        $properties .= $this->description(
                            $property,
                            (string) ($value ?? '')
                        );
                    }
                    $query .= $this->description(
                        $queryName,
                        $this->code($el['type'])
                            . ($el['required'] ? '' : $this->optionalHtml)
                            . '<dl class="row m-0 p-0">' . $properties . '</dl>'
                    );
                }
                $body = '';
                foreach ($endpoint['body']['parameters'] ?? [] as $elName => $el) {
                    $map = arrayUnsetKey($el, 'required', 'type');
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
                        $elName,
                        $this->code($el['type'])
                            . (($el['required'] ?? true) ? '' : $this->optionalHtml)
                            . '<dl class="row m-0 p-0">' . $properties . '</dl>'
                    );
                }

                $replace = [
                    $this->description('Headers', '__placeholder__'),
                    $this->description('Query', $this->code('array&lt;string&gt;'))
                    . '<dl class="row m-0 p-0">' . $query . '</dl>',
                ];

                if ($body !== '') {
                    $replace[1] .= $this->description(
                        'Body',
                        $this->code($endpoint['body']['type'])
                        . $this->div($endpoint['body']['description'] ?? '')
                    )
                    . '<dl class="row m-0 p-0">' . $body . '</dl>';
                }
                $request = str_replace($search, $replace, $this->requestHtml);

                $endpoints .= str_replace('%request.html%', $request, $this->endpointHtml);
                $replace = [
                    '%method%' => $method,
                    '%md5%' => md5($name . $method),
                    '%description%' => $endpoint['description'],
                ];
                $endpoints = strtr($endpoints, $replace);
            }

            $endpoints = str_replace('%endpoints%', $endpoints, $this->endpointsHtml);

            ////////////////////////////////

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

    private function loadTemplates(): void
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
        $this->endpointHtml = $this->getTemplate('endpoint.html');
        $this->endpointsHtml = $this->getTemplate('endpoints.html');
        $this->optionalHtml = $this->getTemplate('optional.html');
        $this->serverHtml = $this->getTemplate('server.html');
        $this->serversHtml = $this->getTemplate('servers.html');
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

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

    private string $descriptionTemplate;

    public function __construct(
        private Spec $spec
    ) {
        $this->descriptionTemplate = $this->getTemplate('dtdd.html');
        $array = $spec->toArray();
        $this->html = $this->getTemplate('main.html');
        $search = [
            '%name%',
            '%version%',
        ];
        $replace = [
            $spec->document()->name,
            $spec->document()->version,
        ];
        $this->html = str_replace($search, $replace, $this->html);

        $serverTemplate = $this->getTemplate('server.html');
        $servers = '';
        foreach ($spec->servers() as $server) {
            $search = [
                '%url%',
                '%description%',
            ];
            $replace = [
                $server->url,
                $server->description,
            ];
            $servers .= str_replace($search, $replace, $serverTemplate);
        }

        $serversTemplate = $this->getTemplate('servers.html');
        $servers = str_replace('%servers%', $servers, $serversTemplate);
        $this->html = str_replace('%servers.html%', $servers, $this->html);

        $pathTemplate = $this->getTemplate('path.html');
        $variableTemplate = $this->getTemplate('variable.html');
        $variablesTemplate = $this->getTemplate('variables.html');
        $requestTemplate = $this->getTemplate('request.html');
        $endpointTemplate = $this->getTemplate('endpoint.html');
        $endpointsTemplate = $this->getTemplate('endpoints.html');
        $optionalTemplate = $this->getTemplate('optional.html');
        $paths = '';
        foreach ($array['paths'] as $uri => $path) {
            $variables = '';
            foreach ($path['variables'] as $name => $variable) {
                $search = [
                    '%name%',
                    '%type%',
                    '%regex%',
                    '%description%',
                ];
                $replace = [
                    $this->getDtDd('Name', $name),
                    $this->getDtDd('Type', $this->wrap('code', $variable['type'] ?? '')),
                    $this->getDtDd('Regex', $this->wrap('code', $variable['regex'] ?? '')),
                    $this->getDtDd('Description', $variable['description'] ?? ''),
                ];
                $variables .= str_replace($search, $replace, $variableTemplate);
            }
            $variables = str_replace('%variables%', $variables, $variablesTemplate);

            $endpoints = '';
            foreach ($path['endpoints'] as $method => $endpoint) {
                $search = [
                    '%headers%',
                    '%query%',
                    '%body%',
                ];
                $query = '';
                foreach ($endpoint['query'] as $name => $el) {
                    $properties = '';
                    $map = arrayUnsetKey($el, 'required', 'type');
                    foreach ($map as $property => $value) {
                        $properties .= $this->getDtDd(
                            $property,
                            (string) ($value ?? '')
                        );
                    }
                    $query .= $this->getDtDd(
                        $name,
                        $this->wrap('code', $el['type'])
                            . ($el['required'] ? '' : $optionalTemplate)
                            . '<dl class="row m-0 p-0">' . $properties . '</dl>'
                    );
                }
                $body = '';
                foreach ($endpoint['body']['parameters'] ?? [] as $name => $el) {
                    $map = arrayUnsetKey($el, 'required', 'type');
                    $properties = '';
                    foreach ($map as $property => $value) {
                        if (! is_scalar($value)) {
                            continue;
                        }
                        $properties .= $this->getDtDd(
                            $property,
                            $this->wrap('div', (string) ($value ?? ''))
                        );
                    }
                    $body .= $this->getDtDd(
                        $name,
                        $this->wrap('code', $el['type'])
                            . ($el['required'] ? '' : $optionalTemplate)
                            . '<dl class="row m-0 p-0">' . $properties . '</dl>'
                    );
                }

                $replace = [
                    $this->getDtDd('Headers', '__placeholder__'),
                    $this->getDtDd('Query', $this->wrap('code', 'array&lt;string&gt;'))
                    . '<dl class="row m-0 p-0">' . $query . '</dl>',
                    $this->getDtDd(
                        'Body',
                        $this->wrap('code', $endpoint['body']['type'])
                        . $this->wrap('div', $endpoint['body']['description'] ?? '')
                    )
                    . '<dl class="row m-0 p-0">' . $body . '</dl>',
                ];
                $request = str_replace($search, $replace, $requestTemplate);

                $endpoints .= str_replace('%request.html%', $request, $endpointTemplate);
                $replace = [
                    '%method%' => $method,
                    '%md5%' => md5($name . $method),
                    '%description%' => $endpoint['description'],
                ];
                $endpoints = strtr($endpoints, $replace);
            }

            $endpoints = str_replace('%endpoints%', $endpoints, $endpointsTemplate);

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
            $paths .= str_replace($search, $replace, $pathTemplate);
        }

        $this->html = str_replace('%paths.html%', $paths, $this->html);
    }

    public function __toString()
    {
        return $this->html;
    }

    private function getDtDd(string $title, string $description): string
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
            $this->descriptionTemplate
        );
    }

    private function wrap(string $tag, string $content): string
    {
        return match ($content) {
            '' => '',
            default => <<<HTML
            <{$tag}>{$content}</{$tag}>
            HTML,
        };
    }

    private function getTemplate(string $name): string
    {
        return file_get_contents(self::TEMPLATES_DIR . $name);
    }
}

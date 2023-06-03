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

namespace Chevere\Tests\_resources\src;

use Chevere\Attribute\StringRegex;
use Chevere\Http\Controller;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\date;
use function Chevere\Parameter\float;
use function Chevere\Parameter\integer;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\ArrayTypeParameterInterface;
use function Chevere\Parameter\string;
use function Chevere\Parameter\time;
use Chevere\Schwager\Attributes\Statuses;

#[Statuses(200, 403)]
class GetController extends Controller
{
    public static function acceptQuery(): ArrayStringParameterInterface
    {
        return arrayString(
            date: date(),
        )->withOptional(
            time: time()
        );
    }

    public static function acceptBody(): ArrayTypeParameterInterface
    {
        return arrayp()->withOptional(
            rate: float(minimum: 16.5),
            hours: integer(minimum: 1, maximum: 8),
        );
    }

    public static function acceptResponse(): ArrayTypeParameterInterface
    {
        return arrayp(test: string('/^test$/'));
    }

    public function run(
        #[StringRegex('/^[0-9]+$/', 'The user integer id')]
        string $id,
        #[StringRegex('/^[\w]+$/', 'The user name')]
        string $name
    ): array {
        return [
            'test' => 'test',
        ];
    }
}

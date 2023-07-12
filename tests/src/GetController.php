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

namespace Chevere\Tests\src;

use Chevere\Attributes\Description;
use Chevere\Attributes\Regex;
use Chevere\Http\Attributes\Header;
use Chevere\Http\Attributes\Status;
use Chevere\Http\Controller;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\ArrayTypeParameterInterface;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\date;
use function Chevere\Parameter\float;
use function Chevere\Parameter\integer;
use function Chevere\Parameter\string;
use function Chevere\Parameter\time;

#[Status(222, 403)]
#[Header('foo', 'bar')]
#[Header('esta', 'wea')]
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
        #[Description('The user integer id')]
        #[Regex('/^[0-9]+$/')]
        string $id,
        #[Description('The user name')]
        #[Regex('/^[\w]+$/')]
        string $name
    ): array {
        return [
            'test' => 'test',
        ];
    }
}

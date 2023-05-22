<?php

/*
 * Copyright 2005 - 2023 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Tests\Core\Common\Domain;

use Core\Host\Application\Converter\HostEventConverter;
use Core\Host\Domain\Model\HostEvent;
use ValueError;


it('trim the legacy string when converting to enum array', function (): void {
    $events = HostEventConverter::fromString('  d ,  u  ');

    expect($events)->toBe([HostEvent::Down, HostEvent::Unreachable]);
});

it('remove duplicates when converting to enum array', function (): void {
    $events = HostEventConverter::fromString('d,u,d');

    expect($events)->toBe([HostEvent::Down, HostEvent::Unreachable]);
});

it('remove duplicates when converting to legacy string', function (): void {
    $events = HostEventConverter::toString([HostEvent::Down, HostEvent::Unreachable, HostEvent::Down]);

    expect($events)->toBe('d,u');
});

it('throw an error when bitmask is invalid', function (): void {
    $events = HostEventConverter::fromBitmask(HostEventConverter::MAX_BITMASK | 0b100000);
})->throws(
    ValueError::class,
    '"' . (HostEventConverter::MAX_BITMASK | 0b100000) . '" is not a valid bitmask for enum HostEvent'
);

it('return a full bitmask when array is empty', function (): void {
    $events = HostEventConverter::toBitmask([]);

    expect($events)->toBe(HostEventConverter::MAX_BITMASK);
});

it('return an empty bitmask when array contains HostEvent::None', function (): void {
    $events = HostEventConverter::toBitmask([HostEvent::Down, HostEvent::Unreachable, HostEvent::None]);

    expect($events)->toBe(0b00000);
});

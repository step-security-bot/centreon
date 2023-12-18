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

namespace Tests\Core\CommandMacro\Domain\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\CommandMacro\Domain\Model\CommandMacroType;
use Core\CommandMacro\Domain\Model\NewCommandMacro;

it('should return properly set command macro instance', function (): void {
    $macro = new NewCommandMacro(CommandMacroType::Host, 'macroName');
    $macro->setDescription('macroDescription');

    expect($macro->getName())->toBe('macroName')
        ->and($macro->getType())->toBe(CommandMacroType::Host)
        ->and($macro->getDescription())->toBe('macroDescription');
});

it('should throw an exception when command macro name is empty', function (): void {
    new NewCommandMacro(CommandMacroType::Host, '');
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::notEmptyString('NewCommandMacro::name')->getMessage()
);

it('should throw an exception when command macro name is too long', function (): void {
    new NewCommandMacro(CommandMacroType::Host, str_repeat('a', NewCommandMacro::MAX_NAME_LENGTH + 1));
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', NewCommandMacro::MAX_NAME_LENGTH + 1),
        NewCommandMacro::MAX_NAME_LENGTH + 1,
        NewCommandMacro::MAX_NAME_LENGTH,
        'NewCommandMacro::name'
    )->getMessage()
);

it('should throw an exception when command macro description is too long', function (): void {
    $macro = new NewCommandMacro(CommandMacroType::Host, 'macroName');
    $macro->setDescription(str_repeat('a', NewCommandMacro::MAX_DESCRIPTION_LENGTH + 1));
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', NewCommandMacro::MAX_DESCRIPTION_LENGTH + 1),
        NewCommandMacro::MAX_DESCRIPTION_LENGTH + 1,
        NewCommandMacro::MAX_DESCRIPTION_LENGTH,
        'NewCommandMacro::description'
    )->getMessage()
);

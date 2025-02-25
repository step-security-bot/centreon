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

declare(strict_types = 1);

namespace Tests\Core\ResourceAccess\Application\UseCase\FindRules;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\ForbiddenResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\ResourceAccess\Application\Exception\RuleException;
use Core\ResourceAccess\Application\Repository\ReadRuleRepositoryInterface;
use Core\ResourceAccess\Application\UseCase\FindRules\FindRules;
use Core\ResourceAccess\Application\UseCase\FindRules\FindRulesResponse;
use Core\ResourceAccess\Domain\Model\Rule;
use Tests\Core\ResourceAccess\Infrastructure\API\FindRules\FindRulesPresenterStub;

beforeEach(closure: function (): void {
    $this->requestParameters = $this->createMock(RequestParametersInterface::class);
    $this->user = $this->createMock(ContactInterface::class);
    $this->repository = $this->createMock(ReadRuleRepositoryInterface::class);
    $this->presenter = new FindRulesPresenterStub($this->createMock(PresenterFormatterInterface::class));

    $this->useCase = new FindRules($this->user, $this->repository, $this->requestParameters);
});

it('should present a Forbidden response when user does not have sufficient rights', function (): void {
    $this->user
        ->expects($this->once())
        ->method('hasTopologyRole')
        ->willReturnMap([
            [Contact::ROLE_ADMINISTRATION_ACL_RESOURCE_ACCESS_MANAGEMENT_RW, false],
        ]);

    ($this->useCase)($this->presenter);
    expect($this->presenter->response)
        ->toBeInstanceOf(ForbiddenResponse::class)
        ->and($this->presenter->response->getMessage())
        ->toBe(RuleException::notAllowed()->getMessage());
});

it('should present an ErrorResponse when an exception occurs', function (): void {
    $this->user
        ->expects($this->once())
        ->method('hasTopologyRole')
        ->with(Contact::ROLE_ADMINISTRATION_ACL_RESOURCE_ACCESS_MANAGEMENT_RW)
        ->willReturn(true);

    $exception = new \Exception();
    $this->repository
        ->expects($this->once())
        ->method('findAllByRequestParameters')
        ->with($this->requestParameters)
        ->willThrowException($exception);

    ($this->useCase)($this->presenter);
    expect($this->presenter->response)
        ->toBeInstanceOf(ErrorResponse::class)
        ->and($this->presenter->response->getMessage())
        ->toBe(RuleException::errorWhileSearchingRules()->getMessage());
});

it('should present an ErrorResponse when an error occurs concerning the request parameters', function (): void {
    $this->user
        ->expects($this->once())
        ->method('hasTopologyRole')
        ->with(Contact::ROLE_ADMINISTRATION_ACL_RESOURCE_ACCESS_MANAGEMENT_RW)
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findAllByRequestParameters')
        ->with($this->requestParameters)
        ->willThrowException(new RequestParametersTranslatorException());

    ($this->useCase)($this->presenter);
    expect($this->presenter->response)
        ->toBeInstanceOf(ErrorResponse::class);
});

it('should present a FindRulesResponse when no error occurs', function (): void {
    $this->user
        ->expects($this->once())
        ->method('hasTopologyRole')
        ->with(Contact::ROLE_ADMINISTRATION_ACL_RESOURCE_ACCESS_MANAGEMENT_RW)
        ->willReturn(true);

    $rule = (new Rule(1, 'name', true))->setDescription('description');

    $rulesFound = [$rule];
    $this->repository
        ->expects($this->once())
        ->method('findAllByRequestParameters')
        ->with($this->requestParameters)
        ->willReturn($rulesFound);

    ($this->useCase)($this->presenter);
    $response = $this->presenter->response;
    expect($response)->toBeInstanceOf(FindRulesResponse::class)
        ->and($response->rulesDto[0]->id)->toBe($rule->getId())
        ->and($response->rulesDto[0]->name)->toBe($rule->getName())
        ->and($response->rulesDto[0]->description)->toBe($rule->getDescription())
        ->and($response->rulesDto[0]->isEnabled)->toBe($rule->isEnabled());
});


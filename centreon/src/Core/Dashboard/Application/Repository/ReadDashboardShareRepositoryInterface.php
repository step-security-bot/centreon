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

namespace Core\Dashboard\Application\Repository;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Core\Dashboard\Domain\Model\Dashboard;
use Core\Dashboard\Domain\Model\Share\DashboardContactGroupShare;
use Core\Dashboard\Domain\Model\Share\DashboardContactShare;
use Core\Dashboard\Domain\Model\Share\DashboardSharingRoles;

interface ReadDashboardShareRepositoryInterface
{
    /**
     * Find all contact shares of one dashboard.
     *
     * @param RequestParametersInterface $requestParameters
     * @param Dashboard $dashboard
     *
     * @throws \Throwable
     *
     * @return DashboardContactShare[]
     */
    public function findDashboardContactSharesByRequestParameter(
        Dashboard $dashboard,
        RequestParametersInterface $requestParameters
    ): array;

    /**
     * Find all contact group shares of one dashboard.
     *
     * @param RequestParametersInterface $requestParameters
     * @param Dashboard $dashboard
     *
     * @throws \Throwable
     *
     * @return DashboardContactGroupShare[]
     */
    public function findDashboardContactGroupSharesByRequestParameter(
        Dashboard $dashboard,
        RequestParametersInterface $requestParameters
    ): array;

    /**
     * Retrieve the sharing roles of a dashboard.
     *
     * @param Dashboard $dashboard
     * @param ContactInterface $contact
     *
     * @throws \Throwable
     */
    public function getOneSharingRoles(ContactInterface $contact, Dashboard $dashboard): DashboardSharingRoles;

    /**
     * Retrieve the sharing roles of several dashboards.
     *
     * @param Dashboard ...$dashboards
     * @param ContactInterface $contact
     *
     * @throws \Throwable
     *
     * @return array<int, DashboardSharingRoles>
     */
    public function getMultipleSharingRoles(ContactInterface $contact, Dashboard ...$dashboards): array;
}

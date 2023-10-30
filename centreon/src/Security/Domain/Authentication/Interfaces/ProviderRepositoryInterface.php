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

namespace Security\Domain\Authentication\Interfaces;

use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Security\Domain\Authentication\Model\ProviderConfiguration;

interface ProviderRepositoryInterface
{
    /**
     * Find the provider's configuration.
     *
     * @param int $id Id of the provider configuration
     *
     * @throws \Exception
     *
     * @return Configuration|null
     */
    public function findProviderConfiguration(int $id): ?Configuration;

    /**
     * Find the provider configuration by name.
     *
     * @param string $providerConfigurationName
     *
     * @return ProviderConfiguration|null
     */
    public function findProviderConfigurationByConfigurationName(
        string $providerConfigurationName
    ): ?ProviderConfiguration;
}

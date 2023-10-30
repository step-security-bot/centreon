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

namespace CentreonNotification;

use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use CentreonNotification\Application\Webservice;
use Pimple\Container;

class ServiceProvider implements AutoloadServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $pimple): void
    {
        // register Excalaton webservice
        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]
            ->add(Webservice\EscalationWebservice::class);
    }

    /**
     * {@inheritDoc}
     */
    public static function order(): int
    {
        return 50;
    }
}

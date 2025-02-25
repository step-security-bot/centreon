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

namespace Core\Contact\Application\Repository;

interface ReadContactRepositoryInterface
{
    /**
     * Find contact names by IDs.
     *
     * @param int ...$ids
     *
     * @throws \Throwable
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function findNamesByIds(int ...$ids): array;

    /**
     * Check user existence by its id.
     *
     * @param int $userId
     *
     * @throws \Throwable
     *
     * @return bool
     */
    public function exists(int $userId): bool;

    /**
     * Check existence of provided users
     * Return an array of the existing user IDs out of the provided ones.
     *
     * @param int[] $userIds
     *
     * @return int[]
     */
    public function exist(array $userIds): array;

    /**
     * Find contact_ids link to given contactGroups.
     *
     * @param int[] $contactGroupIds
     *
     * @return int[]
     */
    public function findContactIdsByContactGroups(array $contactGroupIds): array;
}

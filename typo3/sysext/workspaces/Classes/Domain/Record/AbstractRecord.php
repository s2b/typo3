<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Workspaces\Domain\Record;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Workspaces\Service\StagesService;

/**
 * Combined record class
 */
abstract class AbstractRecord
{
    protected array $record;

    protected static function fetch(string $tableName, int $uid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $record = $queryBuilder->select('*')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        if (empty($record)) {
            throw new \RuntimeException('Record "' . $tableName . ': ' . $uid . '" not found', 1476122008);
        }
        return $record;
    }

    protected static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    public function __construct(array $record)
    {
        $this->record = $record;
    }

    public function __toString(): string
    {
        return (string)$this->getUid();
    }

    public function getUid(): int
    {
        return (int)$this->record['uid'];
    }

    public function getTitle(): string
    {
        return (string)$this->record['title'];
    }

    protected function getStagesService(): StagesService
    {
        return GeneralUtility::makeInstance(StagesService::class);
    }
}

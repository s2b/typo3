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

namespace TYPO3\CMS\Core\Database\Platform;

use Doctrine\DBAL\Platforms\MySQL80Platform as DoctrineMySQL80Platform;
use Doctrine\DBAL\Schema\TableDiff as DoctrineTableDiff;
use TYPO3\CMS\Core\Database\Schema\TableDiff;

/**
 * doctrine/dbal 4+ removed the old doctrine event system. The new way is to extend the platform
 * class(es) and directly override the methods instead of consuming events. Therefore, we need to
 * extend the platform classes to provide some changes for TYPO3 database schema operations.
 *
 * @internal not part of Public Core API.
 */
class MySQL80Platform extends DoctrineMySQL80Platform
{
    use MySQLCompatibleAlterTablePlatformAwareTrait;
    use PlatformSaveAlterSchemaSQLTrait;

    /**
     * Gets the SQL statements for altering an existing table.
     *
     * This method returns an array of SQL statements, since some platforms need several statements.
     *
     * @return list<string>
     */
    public function getAlterTableSQL(TableDiff|DoctrineTableDiff $diff): array
    {
        return $this->getCustomAlterTableSQLEngineOptions(
            platform: $this,
            tableDiff: $diff,
            result: parent::getAlterTableSQL(
                diff: $diff,
            ),
        );
    }

    /**
     * @internal Only for internal usage. doctrine/dbal deprecated this method on platforms. Usage may be removed at
     *           any time.
     * @see https://github.com/doctrine/dbal/issues/4749
     */
    public function getName(): string
    {
        return 'mysql';
    }
}

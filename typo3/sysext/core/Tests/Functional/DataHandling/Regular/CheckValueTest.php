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

namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\Regular;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\Framework\DataHandling\ActionService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests related to DataHandler::checkValue()
 */
final class CheckValueTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3/sysext/core/Tests/Functional/Fixtures/Extensions/test_datahandler',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/DataSet/ImportDefault.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users_admin.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    private function insertRecordWithRadioFieldValue($value): array
    {
        // pid 88 comes from ImportDefault
        $actionService = new ActionService();
        $result = $actionService->createNewRecord('tt_content', 88, [
            'tx_testdatahandler_radio' => $value,
        ]);
        $recordUid = $result['tt_content'][0];
        return BackendUtility::getRecord('tt_content', $recordUid);
    }

    /**
     * @test
     */
    public function radioButtonValueMustBeDefinedInTcaItems(): void
    {
        $record = $this->insertRecordWithRadioFieldValue('predefined value');
        self::assertEquals('predefined value', $record['tx_testdatahandler_radio']);
    }

    /**
     * @test
     */
    public function radioButtonValueMustComeFromItemsProcFuncIfNotDefinedInTcaItems(): void
    {
        $record = $this->insertRecordWithRadioFieldValue('processed value');
        self::assertEquals('processed value', $record['tx_testdatahandler_radio']);
    }

    /**
     * @test
     */
    public function radioButtonValueIsNotSavedIfNotDefinedInTcaItemsOrProcessingItems(): void
    {
        $record = $this->insertRecordWithRadioFieldValue('some other value');
        self::assertEquals('', $record['tx_testdatahandler_radio']);
    }
}

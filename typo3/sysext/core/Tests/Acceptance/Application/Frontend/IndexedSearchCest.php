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

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\Frontend;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;
use TYPO3\CMS\Core\Tests\Acceptance\Support\Helper\PageTree;

final class IndexedSearchCest
{
    private string $sidebarSelector = '.sidebar.list-group';
    private string $searchSelector = '#tx-indexedsearch-searchbox-sword';
    private string $advancedSelector = '//a[contains(., "Advanced search")]';
    private string $regularSelector = '//a[contains(., "Regular search")]';
    private string $noResultsSelector = '.tx-indexedsearch-info-noresult';
    private string $submitSelector = '.tx-indexedsearch-search-submit input[type=submit]';

    public function _before(ApplicationTester $I, PageTree $pageTree): void
    {
        $I->useExistingSession('admin');
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node', 5);
        $pageTree->openPath(['styleguide frontend demo']);
        $I->switchToContentFrame();
        $I->waitForElementVisible('.t3js-module-docheader-bar a[title="View webpage"]');
        $I->wait(1);
        $I->click('.t3js-module-docheader-bar a[title="View webpage"]');
        $I->wait(1);
        $I->executeInSelenium(static function (RemoteWebDriver $webdriver) {
            $handles = $webdriver->getWindowHandles();
            $lastWindow = end($handles);
            $webdriver->switchTo()->window($lastWindow);
        });
        $I->wait(1);
        $I->see('TYPO3 Styleguide Frontend', '.content');
        $I->scrollTo('//a[contains(., "list")]');
        $I->click('list', $this->sidebarSelector);
    }

    public function _after(ApplicationTester $I): void
    {
        // Close FE tab again and switch to BE to avoid side effects
        $I->executeInSelenium(static function (RemoteWebDriver $webdriver) {
            $handles = $webdriver->getWindowHandles();
            $webdriver->close();
            $firstWindow = current($handles);
            $webdriver->switchTo()->window($firstWindow);
        });
    }

    public function seeSearchResults(ApplicationTester $I): void
    {
        $I->fillField($this->searchSelector, 'search word');
        $I->click($this->submitSelector);
        $I->see('No results found.', $this->noResultsSelector);
    }

    public function seeAdvancedSearch(ApplicationTester $I): void
    {
        $seeElements = [
            '#tx-indexedsearch-selectbox-searchtype',
            '#tx-indexedsearch-selectbox-defaultoperand',
            '#tx-indexedsearch-selectbox-media',
            '#tx-indexedsearch-selectbox-lang',
            '#tx-indexedsearch-selectbox-sections',
            '#tx-indexedsearch-selectbox-freeIndexUid',
            '#tx-indexedsearch-selectbox-order',
            '#tx-indexedsearch-selectbox-desc',
            '#tx-indexedsearch-selectbox-results',
            '#tx-indexedsearch-selectbox-group',
        ];

        $I->fillField($this->searchSelector, 'search word');
        $I->click($this->advancedSelector);
        foreach ($seeElements as $element) {
            $I->seeElement($element);
        }

        $I->click($this->submitSelector);
        $I->see('No results found.', $this->noResultsSelector);

        $I->click($this->regularSelector);
        foreach ($seeElements as $element) {
            $I->dontSeeElement($element);
        }
    }
}

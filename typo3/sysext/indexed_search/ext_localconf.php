<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\IndexedSearch\Controller\SearchController;
use TYPO3\CMS\IndexedSearch\FileContentParser;
use TYPO3\CMS\IndexedSearch\Hook\DeleteIndexedData;

defined('TYPO3') or die();

// register plugin
ExtensionUtility::configurePlugin(
    'IndexedSearch',
    'Pi2',
    [SearchController::class => ['form', 'search', 'noTypoScript']],
    [SearchController::class => ['form', 'search']]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['indexed_search'] = DeleteIndexedData::class . '->delete';

// Configure default document parsers:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['indexed_search']['external_parsers'] = [
    'pdf'  => FileContentParser::class,
    'doc'  => FileContentParser::class,
    'docx' => FileContentParser::class,
    'dotx' => FileContentParser::class,
    'pps'  => FileContentParser::class,
    'ppsx' => FileContentParser::class,
    'ppt'  => FileContentParser::class,
    'pptx' => FileContentParser::class,
    'potx' => FileContentParser::class,
    'xls'  => FileContentParser::class,
    'xlsx' => FileContentParser::class,
    'xltx' => FileContentParser::class,
    'sxc'  => FileContentParser::class,
    'sxi'  => FileContentParser::class,
    'sxw'  => FileContentParser::class,
    'ods'  => FileContentParser::class,
    'odp'  => FileContentParser::class,
    'odt'  => FileContentParser::class,
    'rtf'  => FileContentParser::class,
    'txt'  => FileContentParser::class,
    'html' => FileContentParser::class,
    'htm'  => FileContentParser::class,
    'csv'  => FileContentParser::class,
    'xml'  => FileContentParser::class,
    'jpg'  => FileContentParser::class,
    'jpeg' => FileContentParser::class,
    'tif'  => FileContentParser::class,
];

$extConf = GeneralUtility::makeInstance(
    ExtensionConfiguration::class
)->get('indexed_search');

if (isset($extConf['useMysqlFulltext']) && (bool)$extConf['useMysqlFulltext']) {
    // Use all index_* tables except "index_rel" and "index_words"
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['indexed_search']['use_tables'] =
        'index_phash,index_fulltext,index_section,index_grlist,index_stat_word,index_debug,index_config';
} else {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['indexed_search']['use_tables'] =
        'index_phash,index_fulltext,index_rel,index_words,index_section,index_grlist,index_stat_word,index_debug,index_config';
}

unset($extConf);

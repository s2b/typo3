.. include:: /Includes.rst.txt

.. _breaking-102849-1705514231:

=================================================================
Breaking: #102849 - Removed ContentObject stdWrap cacheStore hook
=================================================================

See :issue:`102849`

Description
===========

The hook :php:`$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap_cacheStore']`
has been removed in favor of the new PSR-14
:php:`\TYPO3\CMS\Frontend\ContentObject\Event\BeforeStdWrapContentStoredInCacheEvent`.

Impact
======

Any hook implementation registered is not executed anymore
in TYPO3 v13.0+.


Affected installations
======================

TYPO3 installations with custom extensions using this hook.


Migration
=========

The hook is removed without deprecation in order to allow extensions
to work with TYPO3 v12 (using the hook) and v13+ (using the new Event)
when implementing the Event as well without any further deprecations.
Use the :doc:`PSR-14 Event <../13.0/Feature-102849-PSR-14EventForManipulatingStoreCacheFunctionalityOfStdWrap>`
to allow greater influence in the functionality.

.. index:: Frontend, PHP-API, FullyScanned, ext:frontend

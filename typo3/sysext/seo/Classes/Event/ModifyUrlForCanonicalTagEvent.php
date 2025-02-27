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

namespace TYPO3\CMS\Seo\Event;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\Page;

/**
 * PSR-14 event to alter (or empty) a canonical URL for the href="" attribute of a canonical URL.
 */
final class ModifyUrlForCanonicalTagEvent
{
    private string $url = '';

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly Page $page
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getPage(): Page
    {
        return $this->page;
    }
}

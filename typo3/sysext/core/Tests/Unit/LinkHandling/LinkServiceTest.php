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

namespace TYPO3\CMS\Core\Tests\Unit\LinkHandling;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\LinkHandling\Event\AfterLinkResolvedByStringRepresentationEvent;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownUrnException;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LinkServiceTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $container = new Container();
        $container->set(EventDispatcherInterface::class, new NoopEventDispatcher());
        GeneralUtility::setContainer($container);
    }

    /**
     * Data to resolve strings to arrays and vice versa, external, mail, page
     */
    public static function resolveParametersForNonFilesDataProvider(): array
    {
        return [
            'simple page - old style' => [
                // original input value
                '13',
                // splitted values
                [
                    'type' => LinkService::TYPE_PAGE,
                    'pageuid' => 13,
                ],
                // final unified URN
                't3://page?uid=13',
            ],
            'page with type - old style' => [
                '13,31',
                [
                    'type' => LinkService::TYPE_PAGE,
                    'pageuid' => 13,
                    'pagetype' => 31,
                ],
                't3://page?uid=13&type=31',
            ],
            'page with type and fragment - old style' => [
                '13,31#uncool',
                [
                    'type' => LinkService::TYPE_PAGE,
                    'pageuid' => '13',
                    'pagetype' => '31',
                    'fragment' => 'uncool',
                ],
                't3://page?uid=13&type=31#uncool',
            ],
            'page with type and parameters and fragment - old style' => [
                '13,31?unbel=ievable#uncool',
                [
                    'type' => LinkService::TYPE_PAGE,
                    'pageuid' => '13',
                    'pagetype' => '31',
                    'parameters' => 'unbel=ievable',
                    'fragment' => 'uncool',
                ],
                't3://page?uid=13&type=31&unbel=ievable#uncool',
            ],
            'http URL' => [
                'http://www.have.you/ever?did=this',
                [
                    'type' => LinkService::TYPE_URL,
                    'url' => 'http://www.have.you/ever?did=this',
                ],
                'http://www.have.you/ever?did=this',
            ],
            'http URL without scheme' => [
                'www.have.you/ever?did=this',
                [
                    'type' => LinkService::TYPE_URL,
                    'url' => 'http://www.have.you/ever?did=this',
                ],
                'http://www.have.you/ever?did=this',
            ],
            'https URL' => [
                'https://www.have.you/ever?did=this',
                [
                    'type' => LinkService::TYPE_URL,
                    'url' => 'https://www.have.you/ever?did=this',
                ],
                'https://www.have.you/ever?did=this',
            ],
            'https URL with port' => [
                'https://www.have.you:8088/ever?did=this',
                [
                    'type' => LinkService::TYPE_URL,
                    'url' => 'https://www.have.you:8088/ever?did=this',
                ],
                'https://www.have.you:8088/ever?did=this',
            ],
            'ftp URL' => [
                'ftp://www.have.you/ever?did=this',
                [
                    'type' => LinkService::TYPE_URL,
                    'url' => 'ftp://www.have.you/ever?did=this',
                ],
                'ftp://www.have.you/ever?did=this',
            ],
            'afp URL' => [
                'afp://www.have.you/ever?did=this',
                [
                    'type' => LinkService::TYPE_URL,
                    'url' => 'afp://www.have.you/ever?did=this',
                ],
                'afp://www.have.you/ever?did=this',
            ],
            'sftp URL' => [
                'sftp://nice:andsecret@www.have.you:23/ever?did=this',
                [
                    'type' => LinkService::TYPE_URL,
                    'url' => 'sftp://nice:andsecret@www.have.you:23/ever?did=this',
                ],
                'sftp://nice:andsecret@www.have.you:23/ever?did=this',
            ],
            'email with protocol' => [
                'mailto:one@love.com',
                [
                    'type' => LinkService::TYPE_EMAIL,
                    'email' => 'one@love.com',
                ],
                'mailto:one@love.com',
            ],
            'email without protocol' => [
                'one@love.com',
                [
                    'type' => LinkService::TYPE_EMAIL,
                    'email' => 'one@love.com',
                ],
                'mailto:one@love.com',
            ],
            'email without protocol and subject parameter' => [
                'email@mail.mail?subject=Anfrage:%20Text%20Text%20Lösungen',
                [
                    'type' => LinkService::TYPE_EMAIL,
                    'email' => 'email@mail.mail?subject=Anfrage:%20Text%20Text%20Lösungen',
                ],
                'mailto:email@mail.mail?subject=Anfrage:%20Text%20Text%20Lösungen',
            ],
            'current page - cool style' => [
                't3://page?uid=current',
                [
                    'type' => LinkService::TYPE_PAGE,
                    'pageuid' => 'current',
                ],
                't3://page?uid=current',
            ],
            'current empty page - cool style' => [
                't3://page',
                [
                    'type' => LinkService::TYPE_PAGE,
                    'pageuid' => 'current',
                ],
                't3://page?uid=current',
            ],
            'simple page - cool style' => [
                't3://page?uid=13',
                [
                    'type' => LinkService::TYPE_PAGE,
                    'pageuid' => 13,
                ],
                't3://page?uid=13',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider resolveParametersForNonFilesDataProvider
     */
    public function resolveReturnsSplitParameters(string $input, array $expected, string $finalString): void
    {
        $subject = new LinkService();
        self::assertEquals($expected, $subject->resolve($input));
    }

    /**
     * @test
     * @dataProvider resolveParametersForNonFilesDataProvider
     */
    public function splitParametersToUnifiedIdentifier(string $input, array $parameters, string $expected): void
    {
        $subject = new LinkService();
        self::assertEquals($expected, $subject->asString($parameters));
    }

    /**
     * @test
     */
    public function unknownLinkHandlerExceptionIsThrown(): void
    {
        $this->expectException(UnknownLinkHandlerException::class);
        $this->expectExceptionCode(1460581769);

        (new LinkService())->resolveByStringRepresentation('t3://invalid');
    }

    /**
     * @test
     */
    public function unknownUrnExceptionIsThrown(): void
    {
        $this->expectException(UnknownUrnException::class);
        $this->expectExceptionCode(1457177667);

        (new LinkService())->resolveByStringRepresentation('invalid');
    }

    /**
     * @test
     */
    public function afterLinkResolvedByStringRepresentationEventIsCalled(): void
    {
        $afterLinkResolvedByStringRepresentationEvent = null;
        $modifiedResult = ['type' => 'my-type'];

        /** @var Container $container */
        $container = GeneralUtility::getContainer();
        $container->set(
            'after-link-resolved-by-string-representation-listener',
            static function (AfterLinkResolvedByStringRepresentationEvent $event) use (&$afterLinkResolvedByStringRepresentationEvent, $modifiedResult) {
                $afterLinkResolvedByStringRepresentationEvent = $event;
                $event->setResult($modifiedResult);
            }
        );

        $listenerProdiver = GeneralUtility::makeInstance(ListenerProvider::class, $container);
        $listenerProdiver->addListener(AfterLinkResolvedByStringRepresentationEvent::class, 'after-link-resolved-by-string-representation-listener');
        $container->set(ListenerProvider::class, $listenerProdiver);
        $container->set(EventDispatcherInterface::class, new EventDispatcher($listenerProdiver));

        // Note: The $urn will trigger the UnknownLinkHandlerException. This exception is
        // being caught and since the event listener sets a "type" the exception is not thrown.
        // However, the exception is passed to the event to provide extensions as much information as possible
        $urn = 't3://invalid';
        $expectedExceptionMessage = 'LinkHandler for invalid was not registered';
        $result = (new LinkService())->resolveByStringRepresentation($urn);

        self::assertSame($modifiedResult, $result);
        self::assertInstanceOf(AfterLinkResolvedByStringRepresentationEvent::class, $afterLinkResolvedByStringRepresentationEvent);
        self::assertSame($modifiedResult, $afterLinkResolvedByStringRepresentationEvent->getResult());
        self::assertSame($urn, $afterLinkResolvedByStringRepresentationEvent->getUrn());
        self::assertSame($expectedExceptionMessage, $afterLinkResolvedByStringRepresentationEvent->getResolveException()->getMessage());
    }
}

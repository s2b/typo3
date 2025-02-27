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

namespace TYPO3\CMS\Core\Tests\Unit\Mail;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\MailboxHeader;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class MailMessageTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected ?MailMessage $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new MailMessage();
    }

    /**
     * @test
     */
    public function isSentReturnsFalseIfMailWasNotSent(): void
    {
        self::assertFalse($this->subject->isSent());
    }

    /**
     * @test
     */
    public function setSubjectWorksAsExpected(): void
    {
        $this->subject->setSubject('Test');
        self::assertSame('Test', $this->subject->getSubject());
        $this->subject->setSubject('Test2');
        self::assertSame('Test2', $this->subject->getSubject());
    }

    /**
     * @test
     */
    public function setDateWorksAsExpected(): void
    {
        $time = time();
        $this->subject->setDate($time);
        self::assertSame($time, (int)$this->subject->getDate()->format('U'));
        $time++;
        $this->subject->setDate($time);
        self::assertSame($time, (int)$this->subject->getDate()->format('U'));
    }

    /**
     * @test
     */
    public function setReturnPathWorksAsExpected(): void
    {
        $this->subject->setReturnPath('noreply@typo3.com');
        self::assertInstanceOf(Address::class, $this->subject->getReturnPath());
        self::assertSame('noreply@typo3.com', $this->subject->getReturnPath()->getAddress());
        $this->subject->setReturnPath('no-reply@typo3.com');
        self::assertInstanceOf(Address::class, $this->subject->getReturnPath());
        self::assertSame('no-reply@typo3.com', $this->subject->getReturnPath()->getAddress());
    }

    public static function setSenderAddressDataProvider(): array
    {
        return [
            'address without name' => [
                'admin@typo3.com', null, [
                    ['admin@typo3.com'],
                ],
            ],
            'address with name' => [
                'admin@typo3.com', 'Admin', [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider setSenderAddressDataProvider
     */
    public function setSenderWorksAsExpected(string $address, ?string $name, array $expectedAddresses): void
    {
        $this->subject->setSender($address, $name);
        self::assertInstanceOf(Address::class, $this->subject->getSender());
        self::assertSame($address, $this->subject->getSender()->getAddress());
        $this->assertCorrectAddresses([$this->subject->getSender()], $expectedAddresses);
    }

    public static function globalSetAddressDataProvider(): array
    {
        return [
            'address without name' => [
                'admin@typo3.com', null, [
                    ['admin@typo3.com'],
                ],
            ],
            'address with name' => [
                'admin@typo3.com', 'Admin', [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                ],
            ],
            'address with name enclosed in quotes' => [
                'admin@typo3.com', '"Admin"', [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                ],
            ],
            'multiple addresses without name' => [
                [
                    'admin@typo3.com',
                    'system@typo3.com',
                ], null, [
                    ['admin@typo3.com'],
                    ['system@typo3.com'],
                ],
            ],
            'address as array' => [
                ['admin@typo3.com' => 'Admin'], null, [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                ],
            ],
            'multiple addresses as array' => [
                [
                    'admin@typo3.com' => 'Admin',
                    'system@typo3.com' => 'System',
                ], null, [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                    ['system@typo3.com', 'System', '<system@typo3.com>'],
                ],
            ],
            'multiple addresses as array mixed' => [
                [
                    'admin@typo3.com' => 'Admin',
                    'it@typo3.com',
                    'system@typo3.com' => 'System',
                ], null, [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                    ['it@typo3.com'],
                    ['system@typo3.com', 'System', '<system@typo3.com>'],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider globalSetAddressDataProvider
     */
    public function setFromWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        // We first add one address, because set should override / remove existing addresses
        $this->subject->addFrom('foo@bar.com', 'Foo');
        $this->subject->setFrom($address, $name);
        $this->assertCorrectAddresses($this->subject->getFrom(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalSetAddressDataProvider
     */
    public function setReplyToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        // We first add one address, because set should override / remove existing addresses
        $this->subject->addReplyTo('foo@bar.com', 'Foo');
        $this->subject->setReplyTo($address, $name);
        $this->assertCorrectAddresses($this->subject->getReplyTo(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalSetAddressDataProvider
     */
    public function setToToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        // We first add one address, because set should override / remove existing addresses
        $this->subject->addTo('foo@bar.com', 'Foo');
        $this->subject->setTo($address, $name);
        $this->assertCorrectAddresses($this->subject->getTo(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalSetAddressDataProvider
     */
    public function setCcToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        // We first add one address, because set should override / remove existing addresses
        $this->subject->addCc('foo@bar.com', 'Foo');
        $this->subject->setCc($address, $name);
        $this->assertCorrectAddresses($this->subject->getCc(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalSetAddressDataProvider
     */
    public function setBccToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        // We first add one address, because set should override / remove existing addresses
        $this->subject->addBcc('foo@bar.com', 'Foo');
        $this->subject->setBcc($address, $name);
        $this->assertCorrectAddresses($this->subject->getBcc(), $expectedAddresses);
    }

    public static function globalAddAddressDataProvider(): array
    {
        return [
            'address without name' => [
                'admin@typo3.com', null, [
                    ['admin@typo3.com'],
                ],
            ],
            'address with name' => [
                'admin@typo3.com', 'Admin', [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                ],
            ],
            'address as array' => [
                ['admin@typo3.com' => 'Admin'], null, [
                    ['admin@typo3.com', 'Admin', '<admin@typo3.com>'],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider globalAddAddressDataProvider
     */
    public function addFromToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        $this->subject->addFrom($address, $name);
        $this->assertCorrectAddresses($this->subject->getFrom(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalAddAddressDataProvider
     */
    public function addReplyToToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        $this->subject->addReplyTo($address, $name);
        $this->assertCorrectAddresses($this->subject->getReplyTo(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalAddAddressDataProvider
     */
    public function addToToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        $this->subject->addTo($address, $name);
        $this->assertCorrectAddresses($this->subject->getTo(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalAddAddressDataProvider
     */
    public function addCcToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        $this->subject->addCc($address, $name);
        $this->assertCorrectAddresses($this->subject->getCc(), $expectedAddresses);
    }

    /**
     * @test
     * @dataProvider globalAddAddressDataProvider
     */
    public function addBccToWorksAsExpected(string|array $address, ?string $name, array $expectedAddresses): void
    {
        $this->subject->addBcc($address, $name);
        $this->assertCorrectAddresses($this->subject->getBcc(), $expectedAddresses);
    }

    /**
     * @test
     */
    public function setReadReceiptToToWorksAsExpected(): void
    {
        $this->subject->setReadReceiptTo('foo@example.com');
        /** @var MailboxHeader $header */
        $header = $this->subject->getHeaders()->get('Disposition-Notification-To');
        self::assertSame('foo@example.com', $header->getAddress()->getAddress());
    }

    public static function exceptionIsThrownForInvalidArgumentCombinationsDataProvider(): array
    {
        return [
            'setFrom' => ['setFrom'],
            'setReplyTo' => ['setReplyTo'],
            'setTo' => ['setTo'],
            'setCc' => ['setCc'],
            'setBcc' => ['setBcc'],
        ];
    }

    /**
     * @test
     * @dataProvider exceptionIsThrownForInvalidArgumentCombinationsDataProvider
     */
    public function exceptionIsThrownForInvalidArgumentCombinations(string $method): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1570543657);
        $this->subject->{$method}(['foo@example.com'], 'A name');
    }

    /**
     * Assert that the correct address data are resolved after setting to the object.
     * This is a helper method to prevent duplicated code in this test.
     */
    protected function assertCorrectAddresses(array $dataToCheck, array $expectedAddresses): void
    {
        self::assertIsArray($dataToCheck);
        self::assertCount(count($expectedAddresses), $dataToCheck);
        foreach ($expectedAddresses as $key => $expectedAddress) {
            self::assertIsArray($expectedAddress);
            self::assertSame($expectedAddress[0], $dataToCheck[$key]->getAddress());
            foreach ($expectedAddress as $expectedAddressPart) {
                self::assertStringContainsString($expectedAddressPart, $dataToCheck[$key]->toString());
            }
        }
    }
}

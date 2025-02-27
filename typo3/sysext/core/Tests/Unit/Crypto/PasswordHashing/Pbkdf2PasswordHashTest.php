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

namespace TYPO3\CMS\Core\Tests\Unit\Crypto\PasswordHashing;

use TYPO3\CMS\Core\Crypto\PasswordHashing\Pbkdf2PasswordHash;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class Pbkdf2PasswordHashTest extends UnitTestCase
{
    /**
     * @test
     */
    public function constructorThrowsExceptionIfHashCountIsTooLow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1533903544);
        new Pbkdf2PasswordHash(['hash_count' => 999]);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfHashCountIsTooHigh(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1533903544);
        new Pbkdf2PasswordHash(['hash_count' => 10000001]);
    }

    /**
     * @test
     */
    public function getHashedPasswordReturnsNullWithEmptyPassword(): void
    {
        $password = '';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        self::assertNull($subject->getHashedPassword($password));
    }

    /**
     * @test
     */
    public function getHashedPasswordReturnsNotNullWithNullPassword(): void
    {
        $password = 'a';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        self::assertNotNull($subject->getHashedPassword($password));
    }

    /**
     * @test
     */
    public function getHashedPasswordValidates(): void
    {
        $password = 'password';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertTrue($subject->isValidSaltedPW($saltedHashPassword));
    }

    /**
     * Tests authentication procedure with fixed password and fixed (pre-generated) hash.
     *
     * Checks if a "plain-text password" is every time mapped to the
     * same "salted password hash" when using the same fixed salt.
     *
     * @test
     */
    public function checkPasswordReturnsTrueWithValidAlphaCharClassPasswordAndFixedHash(): void
    {
        $password = 'password';
        $saltedHashPassword = '$pbkdf2-sha256$1000$woPhT0yoWm3AXJXSjuxJ3w$iZ6EvTulMqXlzr0NO8z5EyrklFcJk5Uw2Fqje68FfaQ';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        self::assertTrue($subject->checkPassword($password, $saltedHashPassword));
    }

    /**
     * Tests that authentication procedure fails with broken hash to compare to
     *
     * @test
     */
    public function checkPasswordReturnsFalseWithBrokenHash(): void
    {
        $password = 'password';
        $saltedHashPassword = '$pbkdf2-sha256$1000$woPhT0yoWm3AXJXSjuxJ3w$iZ6EvTulMqXlzr0NO8z5EyrklFcJk5Uw2Fqje68Ffa';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        self::assertFalse($subject->checkPassword($password, $saltedHashPassword));
    }

    /**
     * Tests authentication procedure with alphabet characters.
     *
     * Checks if a "plain-text password" is every time mapped to the
     * same "salted password hash" when using the same salt.
     *
     * @test
     */
    public function checkPasswordReturnsTrueWithValidAlphaCharClassPassword(): void
    {
        $password = 'aEjOtY';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertTrue($subject->checkPassword($password, $saltedHashPassword));
    }

    /**
     * Tests authentication procedure with numeric characters.
     *
     * Checks if a "plain-text password" is every time mapped to the
     * same "salted password hash" when using the same salt.
     *
     * @test
     */
    public function checkPasswordReturnsTrueWithValidNumericCharClassPassword(): void
    {
        $password = '01369';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertTrue($subject->checkPassword($password, $saltedHashPassword));
    }

    /**
     * Tests authentication procedure with US-ASCII special characters.
     *
     * Checks if a "plain-text password" is every time mapped to the
     * same "salted password hash" when using the same salt.
     *
     * @test
     */
    public function checkPasswordReturnsTrueWithValidAsciiSpecialCharClassPassword(): void
    {
        $password = ' !"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertTrue($subject->checkPassword($password, $saltedHashPassword));
    }

    /**
     * Tests authentication procedure with latin1 special characters.
     *
     * Checks if a "plain-text password" is every time mapped to the
     * same "salted password hash" when using the same salt.
     *
     * @test
     */
    public function checkPasswordReturnsTrueWithValidLatin1SpecialCharClassPassword(): void
    {
        $password = '';
        for ($i = 160; $i <= 191; $i++) {
            $password .= chr($i);
        }
        $password .= chr(215) . chr(247);
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertTrue($subject->checkPassword($password, $saltedHashPassword));
    }

    /**
     * Tests authentication procedure with latin1 umlauts.
     *
     * Checks if a "plain-text password" is every time mapped to the
     * same "salted password hash" when using the same salt.
     *
     * @test
     */
    public function checkPasswordReturnsTrueWithValidLatin1UmlautCharClassPassword(): void
    {
        $password = '';
        for ($i = 192; $i <= 214; $i++) {
            $password .= chr($i);
        }
        for ($i = 216; $i <= 246; $i++) {
            $password .= chr($i);
        }
        for ($i = 248; $i <= 255; $i++) {
            $password .= chr($i);
        }
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertTrue($subject->checkPassword($password, $saltedHashPassword));
    }

    /**
     * @test
     */
    public function checkPasswordReturnsFalseWithNonValidPassword(): void
    {
        $password = 'password';
        $password1 = $password . 'INVALID';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertFalse($subject->checkPassword($password1, $saltedHashPassword));
    }

    /**
     * @test
     */
    public function isHashUpdateNeededReturnsFalseForValidSaltedPassword(): void
    {
        $password = 'password';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword($password);
        self::assertFalse($subject->isHashUpdateNeeded($saltedHashPassword));
    }

    /**
     * @test
     */
    public function isHashUpdateNeededReturnsTrueWithChangedHashCount(): void
    {
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        $saltedHashPassword = $subject->getHashedPassword('password');
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1001]);
        self::assertTrue($subject->isHashUpdateNeeded($saltedHashPassword));
    }

    /**
     * @test
     */
    public function checkPasswordIsCompatibleWithPythonPasslibHashes(): void
    {
        $passlibSaltedHash = '$pbkdf2-sha256$6400$.6UI/S.nXIk8jcbdHx3Fhg$98jZicV16ODfEsEZeYPGHU3kbrUrvUEXOPimVSQDD44';
        $subject = new Pbkdf2PasswordHash(['hash_count' => 1000]);
        self::assertTrue($subject->checkPassword('password', $passlibSaltedHash));
    }
}

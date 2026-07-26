<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Jwt;

use App\IdentityAccess\Infrastructure\Security\Jwt\JwtConfigFactory;
use App\Tests\Shared\BaseUnitTest;
use Lcobucci\JWT\Validation\Constraint\HasClaim;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

final class JwtConfigFactoryTest extends BaseUnitTest
{
    public function testItCreatesConfigurationWithCorrectConstraints(): void
    {
        $private = "-----BEGIN RSA PRIVATE KEY-----\n...\n-----END RSA PRIVATE KEY-----";
        $pub = "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----";

        $config = JwtConfigFactory::create($private, $pub, 'passphrase');

        $constraints = $config->validationConstraints();

        self::assertCount(5, $constraints);
        self::assertInstanceOf(SignedWith::class, $constraints[0]);
        self::assertInstanceOf(LooseValidAt::class, $constraints[1]);
        self::assertInstanceOf(HasClaim::class, $constraints[2]);
        self::assertInstanceOf(HasClaim::class, $constraints[3]);
        self::assertInstanceOf(HasClaim::class, $constraints[4]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Jwt;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Infrastructure\Security\Jwt\JwtConfigFactory;
use App\IdentityAccess\Infrastructure\Security\Jwt\JwtGenerator;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\Shared\BaseUnitTest;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\RegisteredClaims;
use Symfony\Component\Clock\MockClock;

final class JwtGeneratorTest extends BaseUnitTest
{
    private Configuration $config;
    private MockClock $clock;
    private string $appName = 'TestApp';
    private int $ttl = 3600;

    protected function setUp(): void
    {
        // we use forSymmetricSigner for easier testing handler work
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('testing-secret-key-64-characters-long-for-sha256-standard-rules')
        );
        $this->clock = new MockClock('2024-01-01 12:00:00');
    }

    public function testItGeneratesValidTokenWithCorrectClaims(): void
    {
        $generator = new JwtGenerator($this->config, $this->clock, $this->appName, $this->ttl);

        $dto = new GrantResultData(
            subjectUlid: '01ARZ3NDEKTSV4RRFFQ6KHNQZY',
            subjectType: IdentityTypeEnum::User,
            roles: ['ROLE_USER'],
            scopes: []
        );

        $result = $generator->generateAccessToken($dto);

        self::assertSame($this->ttl, $result->expiresIn);

        $token = $this->config->parser()->parse($result->token);
        $claims = $token->claims();

        self::assertSame($this->appName, $claims->get(RegisteredClaims::ISSUER));
        self::assertSame($dto->subjectUlid, $claims->get(RegisteredClaims::SUBJECT));
        self::assertSame($dto->roles, $claims->get(JwtConfigFactory::CLAIM_ROLES));
        self::assertSame($dto->scopes, $claims->get(JwtConfigFactory::CLAIM_SCOPES));
        self::assertSame($dto->subjectType->value, $claims->get(JwtConfigFactory::CLAIM_SUBJECT_TYPE));

        self::assertSame(
            $this->clock->now()->getTimestamp(),
            $claims->get(RegisteredClaims::ISSUED_AT)->getTimestamp()
        );
        self::assertSame(
            $this->clock->now()->modify("+{$this->ttl} seconds")->getTimestamp(),
            $claims->get(RegisteredClaims::EXPIRATION_TIME)->getTimestamp()
        );

        self::assertNotEmpty($claims->get(RegisteredClaims::ID));
    }
}

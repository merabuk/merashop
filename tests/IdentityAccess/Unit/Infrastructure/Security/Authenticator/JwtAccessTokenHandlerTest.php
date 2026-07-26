<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Authenticator;

use App\IdentityAccess\Application\Security\CurrentAccessTokenContextInterface;
use App\IdentityAccess\Domain\Security\AccessTokenBlacklistInterface;
use App\IdentityAccess\Infrastructure\Exception\InvalidCredentialsException;
use App\IdentityAccess\Infrastructure\Security\Authenticator\JwtAccessTokenHandler;
use App\IdentityAccess\Infrastructure\Security\Jwt\JwtConfigFactory;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthEntityProvider;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\Shared\BaseUnitTest;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

final class JwtAccessTokenHandlerTest extends BaseUnitTest
{
    private Parser&MockObject $parser;
    private Validator&MockObject $validator;
    private AccessTokenBlacklistInterface&MockObject $blacklist;
    private CurrentAccessTokenContextInterface&MockObject $context;
    private Configuration $jwtConfig;

    protected function setUp(): void
    {
        $this->parser = $this->createMock(Parser::class);
        $this->validator = $this->createMock(Validator::class);
        $this->blacklist = $this->createMock(AccessTokenBlacklistInterface::class);
        $this->context = $this->createMock(CurrentAccessTokenContextInterface::class);
        $this->createAndSetJwtConfig();
    }

    public function testItSuccessfullyReturnsUserBadge(): void
    {
        $rawToken = 'valid.jwt.token';
        $jti = 'uuid-v7-token-id';
        $ulid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $type = IdentityTypeEnum::User;
        $expiresAt = new DateTimeImmutable('+1 hour');

        $token = $this->createMock(UnencryptedToken::class);
        $claims = new DataSet([
            RegisteredClaims::ID => $jti,
            RegisteredClaims::SUBJECT => $ulid,
            RegisteredClaims::EXPIRATION_TIME => $expiresAt,
            JwtConfigFactory::CLAIM_SUBJECT_TYPE => $type->value,
        ], '');
        $token->method('claims')->willReturn($claims);

        $this->parser->expects(self::once())->method('parse')->with($rawToken)->willReturn($token);
        $this->validator->method('validate')->willReturn(true);
        $this->blacklist->method('isRevoked')->with($jti)->willReturn(false);

        $this->context->expects(self::once())
            ->method('set')
            ->with($jti, $expiresAt->getTimestamp());

        $handler = $this->createHandler();
        $badge = $handler->getUserBadgeFrom($rawToken);

        self::assertSame($type->value.AuthEntityProvider::SEPARATOR.$ulid, $badge->getUserIdentifier());
    }

    public function testThrowsExceptionWhenTokenIsRevoked(): void
    {
        $jti = 'revoked-id';
        $token = $this->createMock(UnencryptedToken::class);
        $claims = new DataSet([RegisteredClaims::ID => $jti], '');
        $token->method('claims')->willReturn($claims);

        $this->parser->method('parse')->willReturn($token);
        $this->validator->method('validate')->willReturn(true);

        $this->blacklist->method('isRevoked')->with($jti)->willReturn(true);

        $handler = $this->createHandler();

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Token has been revoked');

        $handler->getUserBadgeFrom('some.token');
    }

    public function testThrowsExceptionOnInvalidTokenFormat(): void
    {
        $this->parser->method('parse')->willThrowException(new RuntimeException());

        $handler = $this->createHandler();

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid JWT token');

        $handler->getUserBadgeFrom('invalid-string');
    }

    #[DataProvider('invalidClaimsProvider')]
    public function testThrowsExceptionWhenRequiredClaimsAreMissing(array $claimsData, string $expectedMessage): void
    {
        $token = $this->createMock(UnencryptedToken::class);
        $token->method('claims')->willReturn(new DataSet($claimsData, ''));

        $this->parser->method('parse')->willReturn($token);
        $this->validator->method('validate')->willReturn(true);

        $handler = $this->createHandler();

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage($expectedMessage);

        $handler->getUserBadgeFrom('valid.token.but.missing.claims');
    }

    public static function invalidClaimsProvider(): iterable
    {
        yield 'missing subject' => [
            [
                RegisteredClaims::ID => '123',
                RegisteredClaims::EXPIRATION_TIME => new DateTimeImmutable(),
                JwtConfigFactory::CLAIM_SUBJECT_TYPE => IdentityTypeEnum::User->value,
            ],
            'JWT token does not contain a subject (ULID)',
        ];
        yield 'missing subject type' => [
            [
                RegisteredClaims::SUBJECT => 'ulid',
                RegisteredClaims::ID => '123',
                RegisteredClaims::EXPIRATION_TIME => new DateTimeImmutable(),
            ],
            'JWT token does not contain a subject type',
        ];
        yield 'missing jti/exp' => [
            [
                RegisteredClaims::SUBJECT => 'ulid',
                JwtConfigFactory::CLAIM_SUBJECT_TYPE => IdentityTypeEnum::User->value,
            ],
            'Token missing required claims',
        ];
    }

    private function createAndSetJwtConfig(): void
    {
        // we use forSymmetricSigner for easier testing handler work
        $this->jwtConfig = Configuration::forSymmetricSigner(
            signer: $this->createMock(Signer::class),
            key: InMemory::plainText('test-key')
        );

        $this->jwtConfig = $this->jwtConfig
            ->withParser($this->parser)
            ->withValidator($this->validator);
    }

    private function createHandler(): JwtAccessTokenHandler
    {
        return new JwtAccessTokenHandler(
            jwtConfiguration: $this->jwtConfig,
            blacklist: $this->blacklist,
            accessTokenContext: $this->context
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Http\Resolver;

use App\Shared\Application\Security\AuthEntityContextInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Resolver\CurrentAuthEntityIdentityResolver;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Support\Traits\ResolverTrait;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class CurrentAuthEntityIdentityResolverTest extends BaseUnitTest
{
    use ResolverTrait;

    public function testItReturnsIdentity(): void
    {
        $context = $this->createMock(AuthEntityContextInterface::class);
        $identity = new AuthIdentity(
            id: '01ARZ3NDEKTSV4RRFFQ6KHNQZY',
            type: IdentityTypeEnum::User,
            roles: [RoleEnum::User->value]
        );
        $context->method('getIdentity')->willReturn($identity);

        $resolver = new CurrentAuthEntityIdentityResolver($context);

        $argument = $this->makeArgumentMetadata(
            name: 'identity',
            type: AuthIdentity::class,
            attributes: [new CurrentAuthEntityIdentity()]
        );

        $result = iterator_to_array($resolver->resolve(new Request(), $argument));

        self::assertNotEmpty($result);
        /** @var AuthIdentity $dto */
        $dto = $result[0];
        self::assertSame($identity->id, $dto->id);
        self::assertSame($identity->type, $dto->type);
        self::assertSame($identity->roles, $dto->roles);
    }

    public function testThrowsExceptionWhenNotAuthenticatedAndNotNullable(): void
    {
        $context = $this->createMock(AuthEntityContextInterface::class);
        $context->method('getIdentity')->willReturn(null);

        $resolver = new CurrentAuthEntityIdentityResolver($context);

        $argument = $this->makeArgumentMetadata(
            name: 'identity',
            type: AuthIdentity::class,
            attributes: [new CurrentAuthEntityIdentity()]
        );

        $this->expectException(AccessDeniedException::class);

        iterator_to_array($resolver->resolve(new Request(), $argument));
    }

    public function testItReturnsNullIdentityWhenNotAuthenticatedAndNullable(): void
    {
        $context = $this->createMock(AuthEntityContextInterface::class);
        $context->method('getIdentity')->willReturn(null);

        $resolver = new CurrentAuthEntityIdentityResolver($context);

        $argument = $this->makeArgumentMetadata(
            name: 'identity',
            type: AuthIdentity::class,
            isNullable: true,
            attributes: [new CurrentAuthEntityIdentity()]
        );

        $result = iterator_to_array($resolver->resolve(new Request(), $argument));

        self::assertNotEmpty($result);
        self::assertNull($result[0]);
    }

    public function testItReturnsEmptyIfAttributeMissing(): void
    {
        $context = $this->createMock(AuthEntityContextInterface::class);
        $resolver = new CurrentAuthEntityIdentityResolver($context);

        $argument = $this->makeArgumentMetadata(name: 'test', type: stdClass::class);

        self::assertEmpty(iterator_to_array($resolver->resolve(new Request(), $argument)));
    }
}

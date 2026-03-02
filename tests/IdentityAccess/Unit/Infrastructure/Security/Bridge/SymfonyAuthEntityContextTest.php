<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Bridge;

use App\IdentityAccess\Infrastructure\Security\Bridge\SymfonyAuthEntityContext;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class SymfonyAuthEntityContextTest extends TestCase
{
    private Security&MockObject $security;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
    }

    public function testItReturnsIdentityCorrectly(): void
    {
        $user = UserAccountMother::createWithData();
        $authSubject = AuthSubject::fromUserAccount($user);
        $this->security->method('getUser')->willReturn($authSubject);

        $identity = $this->createContext()->getIdentity();

        self::assertNotNull($identity);
        self::assertSame($user->getUlid()->value(), $identity->id);
        self::assertSame(IdentityTypeEnum::User, $identity->type);
        self::assertSame($user->getRoles()->toStrings(), $identity->roles);
    }

    public function testItReturnsNullWhenNoUserIsLoggedIn(): void
    {
        $this->security->method('getUser')->willReturn(null);

        self::assertNull($this->createContext()->getIdentity());
    }

    public function testItReturnsNullWhenUserIsOfUnsupportedType(): void
    {
        $otherUser = $this->createMock(UserInterface::class);
        $this->security->method('getUser')->willReturn($otherUser);

        self::assertNull($this->createContext()->getIdentity());
    }

    private function createContext(): SymfonyAuthEntityContext
    {
        return new SymfonyAuthEntityContext(security: $this->security);
    }
}

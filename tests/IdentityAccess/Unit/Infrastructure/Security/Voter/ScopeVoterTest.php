<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Voter;

use App\IdentityAccess\Infrastructure\Security\Voter\ScopeVoter;
use App\Shared\Domain\Enum\ScopeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ScopeVoterTest extends TestCase
{
    public function testVote(): void
    {
        $voter = new ScopeVoter();

        $user = $this->createMock(UserInterface::class);
        $user->method('getRoles')->willReturn(['user:read', 'ROLE_USER']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, null, [ScopeEnum::UserRead->value])
        );

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, null, ['product:write'])
        );

        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, null, ['ROLE_ADMIN'])
        );
    }
}

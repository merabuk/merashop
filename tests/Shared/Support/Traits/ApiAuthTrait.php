<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Application\Security\AuthEntityContextInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Tests\Shared\Support\AuthIdentityMother;
use App\Tests\Shared\Support\Security\TestAuthContext;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait ApiAuthTrait
{
    protected function loginAsAdmin(?string $id = null): AuthIdentity
    {
        $identity = $this->getAuthMother()->admin($id);
        $this->setAuthIdentity($identity);

        return $identity;
    }

    protected function loginAsUser(?string $id = null): AuthIdentity
    {
        $identity = $this->getAuthMother()->user($id);
        $this->setAuthIdentity($identity);

        return $identity;
    }

    protected function logout(): void
    {
        $authContext = $this->getAuthContext();
        $authContext->setIdentity(null);
    }

    private function setAuthIdentity(AuthIdentity $identity): void
    {
        $authContext = $this->getAuthContext();
        $authContext->setIdentity($identity);
    }

    private function getAuthContext(): TestAuthContext
    {
        /** @var TestAuthContext $context */
        $context = static::getContainer()->get(AuthEntityContextInterface::class);

        return $context;
    }

    private function getAuthMother(): AuthIdentityMother
    {
        /** @var AuthIdentityMother $mother */
        $mother = static::getContainer()->get(AuthIdentityMother::class);

        return $mother;
    }
}

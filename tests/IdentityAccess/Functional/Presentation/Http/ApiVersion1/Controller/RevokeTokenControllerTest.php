<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Functional\Presentation\Http\ApiVersion1\Controller;

use App\IdentityAccess\Domain\Security\AccessTokenBlacklistInterface;
use App\IdentityAccess\Presentation\Http\ApiVersion1\Controller\RevokeTokenController;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessCacheTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RevokeTokenControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use IdentityAccessCacheTrait;

    private const string ROUTE_NAME = RevokeTokenController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_POST;

    protected function tearDown(): void
    {
        $this->clearIdentity();
        $this->clearTokenBlacklistCache();

        parent::tearDown();
    }

    public function testItSuccessfullyRevokesToken(): void
    {
        $client = static::createClient();

        $jti = 'active-session-id';
        $this->loginAsUser();
        $this->simulateTokenContext(jti: $jti);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl()
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        self::assertStringContainsString('successfully revoked', $data['message']);

        /** @var AccessTokenBlacklistInterface $blacklist */
        $blacklist = self::getContainer()->get(AccessTokenBlacklistInterface::class);
        self::assertTrue($blacklist->isRevoked($jti), 'Token JTI must be in the blacklist');
    }

    public function testItReturns403WhenNoTokenProvided(): void
    {
        $client = self::createClient();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturns403UnlessUser(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}

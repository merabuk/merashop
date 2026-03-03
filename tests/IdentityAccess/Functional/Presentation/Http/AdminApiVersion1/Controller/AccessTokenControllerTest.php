<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Functional\Presentation\Http\AdminApiVersion1\Controller;

use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Infrastructure\Security\OAuth2\OAuth2Error;
use App\IdentityAccess\Presentation\Http\AdminApiVersion1\Controller\AccessTokenController;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\Traits\AdminAccountFactoryTrait;
use App\Tests\IdentityAccess\Support\Traits\RefreshTokenFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AccessTokenControllerTest extends WebTestCase
{
    use AdminAccountFactoryTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use RefreshTokenFactoryTrait;

    private const string ROUTE_NAME = AccessTokenController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_POST;

    public function testItSuccessfullyIssuesTokenViaPassword(): void
    {
        $client = self::createClient();

        $email = 'admin@example.com';
        $password = 'password'; // hash already done in fixture (mother)
        $this->getAdminAccountFixture()->create(email: $email);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::Password->value,
                'username' => $email,
                'password' => $password,
            ]
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('expires_in', $data);
        self::assertArrayHasKey('refresh_token', $data);
        self::assertSame('Bearer', $data['token_type']);
    }

    public function testItReturns400OnInvalidPassword(): void
    {
        $client = self::createClient();

        $email = 'wrong-pass@test.com';
        $this->getAdminAccountFixture()->create(email: $email);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::Password->value,
                'username' => $email,
                'password' => 'incorrect',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = $this->getResponseData($client);
        self::assertSame(OAuth2Error::INVALID_GRANT, $data['error']);
    }

    public function testItReturns400OnMissingFieldsViaPassword(): void
    {
        $client = self::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::Password->value,
                'username' => 'test@test.com',
                'password' => '',
            ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = $this->getResponseData($client);
        self::assertSame(OAuth2Error::INVALID_REQUEST, $data['error']);
    }

    public function testItSuccessfullyIssuesTokenViaRefreshToken(): void
    {
        $client = self::createClient();

        $token = 'token'; // hash already done in refresh_token fixture (mother)
        $admin = $this->getAdminAccountFixture()->create();
        $this->getRefreshTokenFixture()->create(
            accountUlid: $admin->getUlid()->value(),
            accountType: IdentityTypeEnum::Admin
        );

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::RefreshToken->value,
                'refresh_token' => $token,
            ],
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);

        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('expires_in', $data);
        self::assertArrayHasKey('refresh_token', $data);
        self::assertSame('Bearer', $data['token_type']);
    }

    public function testItReturns400OnInvalidRefreshToken(): void
    {
        $client = self::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::RefreshToken->value,
                'refresh_token' => 'invalid-token',
            ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = $this->getResponseData($client);
        self::assertSame(OAuth2Error::INVALID_GRANT, $data['error']);
    }

    public function testItReturns400OnMissingFieldsViaRefreshToken(): void
    {
        $client = self::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::RefreshToken->value,
                'refresh_token' => '',
            ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = $this->getResponseData($client);
        self::assertSame(OAuth2Error::INVALID_REQUEST, $data['error']);
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}

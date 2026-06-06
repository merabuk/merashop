<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Functional\Presentation\Http\InternalApiVersion1\Controller;

use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Infrastructure\Security\OAuth2\OAuth2Error;
use App\IdentityAccess\Presentation\Http\InternalApiVersion1\Controller\AccessTokenController;
use App\Tests\IdentityAccess\Support\Traits\ModuleAccountFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AccessTokenControllerTest extends WebTestCase
{
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use ModuleAccountFactoryTrait;

    private const string ROUTE_NAME = AccessTokenController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_POST;

    public function testItSuccessfullyIssuesTokenViaPassword(): void
    {
        $client = self::createClient();

        $clientId = 'test-client';
        $clientSecret = 'password'; // hash already done in fixture (mother)
        $this->getModuleAccountFixture()->create(clientId: $clientId);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::ClientCredentials->value,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]
        );

        self::assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('expires_in', $data);
        self::assertArrayNotHasKey('refresh_token', $data);
        self::assertSame('Bearer', $data['token_type']);
    }

    public function testItReturns401OnInvalidPassword(): void
    {
        $client = self::createClient();

        $clientId = 'wrong-pass-client';
        $this->getModuleAccountFixture()->create(clientId: $clientId);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::ClientCredentials->value,
                'client_id' => $clientId,
                'client_secret' => 'incorrect',
            ]
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $data = $this->getResponseData($client);
        self::assertSame(OAuth2Error::INVALID_CLIENT, $data['error']);
    }

    public function testItReturns400OnMissingFields(): void
    {
        $client = self::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: [
                'grant_type' => GrantTypeEnum::ClientCredentials->value,
                'client_id' => 'test-client',
                'client_secret' => '',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = $this->getResponseData($client);
        self::assertSame(OAuth2Error::INVALID_REQUEST, $data['error']);
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}

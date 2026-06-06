<?php

declare(strict_types=1);

namespace App\Tests\Customer\Functional\Presentation\Http\ApiVersion1\Controller;

use App\Customer\Domain\Enum\ErrorCodeEnum;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Presentation\Http\ApiVersion1\Controller\UpdateCustomerProfileController;
use App\Tests\Customer\Support\Traits\CustomerProfileFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateCustomerProfileControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use CustomerProfileFactoryTrait;

    private const string ROUTE_NAME = UpdateCustomerProfileController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_PUT;

    protected function tearDown(): void
    {
        $this->clearIdentity();

        parent::tearDown();
    }

    public function testItSuccessfullyUpdatesCustomerProfile(): void
    {
        $client = self::createClient();

        $customerProfile = $this->getCustomerProfileFixture()->create();

        $this->loginAsUser(id: $customerProfile->getUserUlid()->value());

        $payload = [
            'firstName' => 'Tony',
            'lastName' => 'Stark',
            'phoneNumber' => '+380991234567',
        ];

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(), payload: $payload);

        self::assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        self::assertSame('Profile was successfully updated', $data['message']);

        $updated = $this->getReadRepository()->findById($customerProfile->getId());
        self::assertSame('Tony', $updated->getFirstName()->value());
        self::assertSame('Stark', $updated->getLastName()->value());
        self::assertSame('+380991234567', $updated->getPhoneNumber()->value());
    }

    public function testItReturns403WhenNoTokenProvided(): void
    {
        $client = self::createClient();

        $payload = [
            'firstName' => 'Tony',
            'lastName' => 'Stark',
            'phoneNumber' => '+380991234567',
        ];

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(), payload: $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturns403UnlessUser(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $payload = [
            'firstName' => 'Tony',
            'lastName' => 'Stark',
            'phoneNumber' => '+380991234567',
        ];

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(), payload: $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturns404WhenCustomerProfileNotFound(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $payload = [
            'firstName' => 'Tony',
            'lastName' => 'Stark',
            'phoneNumber' => '+380991234567',
        ];

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(), payload: $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::CustomerProfileNotFound->value,
            expectedContainMessage: 'Customer profile not found'
        );
    }

    #[DataProvider('invalidProfileDataProvider')]
    public function testItReturns422OnInvalidData(array $payload, array $expectedErrorFields): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: $payload
        );

        $this->assertResponseIsUnprocessable();
        $data = $this->getResponseData($client);

        $this->assertValidationErrors(responseData: $data, expectedErrorFields: $expectedErrorFields);
    }

    public static function invalidProfileDataProvider(): iterable
    {
        yield 'empty first name' => [
            'payload' => ['firstName' => '', 'lastName' => 'Stark', 'phoneNumber' => '+380991234567'],
            'expectedErrorFields' => ['firstName'],
        ];
        yield 'first name too long' => [
            'payload' => ['firstName' => str_repeat('a', FirstName::MAX_LENGTH + 1), 'lastName' => 'Stark', 'phoneNumber' => '+380991234567'],
            'expectedErrorFields' => ['firstName'],
        ];
        yield 'empty last name' => [
            'payload' => ['firstName' => 'Tony', 'lastName' => '', 'phoneNumber' => '+380991234567'],
            'expectedErrorFields' => ['lastName'],
        ];
        yield 'last name too long' => [
            'payload' => ['firstName' => 'Tony', 'lastName' => str_repeat('a', LastName::MAX_LENGTH + 1), 'phoneNumber' => '+380991234567'],
            'expectedErrorFields' => ['lastName'],
        ];
        yield 'empty phone number' => [
            'payload' => ['firstName' => 'Tony', 'lastName' => 'Stark', 'phoneNumber' => ''],
            'expectedErrorFields' => ['phoneNumber'],
        ];
        yield 'invalid phone number' => [
            'payload' => ['firstName' => 'Tony', 'lastName' => 'Stark', 'phoneNumber' => 'invalid-phone-number'],
            'expectedErrorFields' => ['phoneNumber'],
        ];
    }

    private function getReadRepository(): CustomerProfileReadRepositoryInterface
    {
        return self::getContainer()->get(CustomerProfileReadRepositoryInterface::class);
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}

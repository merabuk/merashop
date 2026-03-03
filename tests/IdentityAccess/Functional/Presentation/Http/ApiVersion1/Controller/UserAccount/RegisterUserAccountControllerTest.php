<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Functional\Presentation\Http\ApiVersion1\Controller\UserAccount;

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Presentation\Http\ApiVersion1\Controller\UserAccount\RegisterUserAccountController;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use App\Tests\IdentityAccess\Support\Traits\UserAccountFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class RegisterUserAccountControllerTest extends WebTestCase
{
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use UserAccountFactoryTrait;

    private const string ROUTE_NAME = RegisterUserAccountController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_POST;

    public function testItSuccessfullyRegistersUserAccount(): void
    {
        $client = self::createClient();

        $eventBusMock = $this->createMock(MessageBusInterface::class);
        $eventBusMock->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(UserRegisteredSharedEvent::class))
            ->willReturn(new Envelope(new stdClass()));

        self::getContainer()->set('event.bus', $eventBusMock);

        $email = 'user@example.com';
        $password = 'StrongPassword123!';
        $payload = [
            'email' => $email,
            'password' => $password,
            'passwordConfirmation' => $password,
            'termsAccepted' => true,
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUri(),
            payload: $payload,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        self::assertStringContainsString('successfully registered', $data['message']);

        /** @var UserAccountReadRepositoryInterface $readRepository */
        $readRepository = self::getContainer()->get(UserAccountReadRepositoryInterface::class);

        $userAccount = $readRepository->findByEmail(EmailAddress::fromString($email));
        self::assertNotNull($userAccount);
        self::assertNotNull($userAccount->getId());
        self::assertSame([RoleEnum::User->value, RoleEnum::Customer->value], $userAccount->getRoles()->toStrings());
    }

    public function testThrowsExceptionWhenEmailIsExists(): void
    {
        $client = self::createClient();

        $existingUser = $this->getUserAccountFixture()->create();

        $email = $existingUser->getEmail()->value();
        $password = 'StrongPassword123!';
        $payload = [
            'email' => $email,
            'password' => $password,
            'passwordConfirmation' => $password,
            'termsAccepted' => true,
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUri(),
            payload: $payload,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = $this->getResponseData($client);

        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::UserAccountAlreadyExists->value,
            expectedContainMessage: 'User account already exists'
        );
    }

    #[DataProvider('invalidUserDataProvider')]
    public function testItReturns422OnInvalidData(array $payload, array $expectedErrorFields): void
    {
        $client = self::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUri(),
            payload: $payload,
        );

        $this->assertResponseIsUnprocessable();
        $data = $this->getResponseData($client);

        $this->assertValidationErrors(responseData: $data, expectedErrorFields: $expectedErrorFields);
    }

    public static function invalidUserDataProvider(): iterable
    {
        yield 'empty email' => [
            'payload' => [
                'email' => '',
                'password' => 'Password123!',
                'passwordConfirmation' => 'Password123!',
                'termsAccepted' => true,
            ],
            'expectedErrorFields' => ['email'],
        ];
        yield 'invalid email' => [
            'payload' => [
                'email' => 'invalid-email',
                'password' => 'Password123!',
                'passwordConfirmation' => 'Password123!',
                'termsAccepted' => true,
            ],
            'expectedErrorFields' => ['email'],
        ];
        yield 'terms not accepted' => [
            'payload' => [
                'email' => 'user@example.com',
                'password' => 'Password123!',
                'passwordConfirmation' => 'Password123!',
            ],
            'expectedErrorFields' => ['termsAccepted'],
        ];
        yield 'weak password' => [
            'payload' => [
                'email' => 'user@example.com',
                'password' => 'weak',
                'passwordConfirmation' => 'weak',
                'termsAccepted' => true,
            ],
            'expectedErrorFields' => ['password'],
        ];
        yield 'password confirm fail' => [
            'payload' => [
                'email' => 'user@example.com',
                'password' => 'Password123!',
                'passwordConfirmation' => 'password123!',
                'termsAccepted' => true,
            ],
            'expectedErrorFields' => ['passwordConfirmation'],
        ];
    }

    private function getUri(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}

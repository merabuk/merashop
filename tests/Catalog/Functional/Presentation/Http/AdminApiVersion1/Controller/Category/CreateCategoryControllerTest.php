<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Category;

use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\Translation;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Category\CreateCategoryController;
use App\Tests\Catalog\Support\Traits\CategoryFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateCategoryControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use CategoryFactoryTrait;

    private const string ROUTE_NAME = CreateCategoryController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_POST;

    protected function tearDown(): void
    {
        $this->clearIdentity();

        parent::tearDown();
    }

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItSuccessfullyCreatesCategory(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $parent = $this->getCategoryFixture()->create();

        $payload = [
            'slug' => 'electronics',
            'parentId' => $parent->getId()->value(),
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Category was successfully created', $data['message']);

        $exists = $this->getReadRepository()->existsBySlug(Slug::fromString($payload['slug']));
        $this->assertTrue($exists, 'Category was not saved to database');
    }

    public function testItReturns409WhenSlugAlreadyExists(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $existingCategory = $this->getCategoryFixture()->create();

        $payload = [
            'slug' => $existingCategory->getSlug()->value(),
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
        ];

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(), payload: $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::CategoryAlreadyExists->value,
            expectedContainMessage: sprintf('Category with slug "%s" already exists', $payload['slug']),
        );
    }

    #[DataProvider('invalidCategoryProvider')]
    public function testItReturns422OnInvalidData(array $payload, array $expectedErrorFields): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

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

    public static function invalidCategoryProvider(): iterable
    {
        $payload = [
            'slug' => 'electronics',
            'parentId' => 1,
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
        ];

        yield 'empty slug' => [
            'payload' => [
                ...$payload,
                'slug' => '',
            ],
            'expectedErrorFields' => ['slug'],
        ];
        yield 'invalid parent id' => [
            'payload' => [
                ...$payload,
                'parentId' => 'wrong_parent',
            ],
            'expectedErrorFields' => ['parentId'],
        ];
        yield 'invalid status' => [
            'payload' => [
                ...$payload,
                'status' => 'wrong_status',
            ],
            'expectedErrorFields' => ['status'],
        ];
        yield 'invalid locale and missed required locales' => [
            'payload' => [
                ...$payload,
                'translations' => [
                    'xx' => [
                        'name' => 'Name',
                    ],
                ],
            ],
            'expectedErrorFields' => ['translations', 'translations[xx]'],
        ];
        yield 'too long en translation name and description' => [
            'payload' => [
                ...$payload,
                'translations' => [
                    ...self::validTranslations(),
                    'en' => [
                        'name' => str_repeat('a', Translation::NAME_MAX_LENGTH + 1),
                        'description' => str_repeat('a', Translation::DESCRIPTION_MAX_LENGTH + 1),
                    ],
                ],
            ],
            'expectedErrorFields' => ['translations[en][name]', 'translations[en][description]'],
        ];
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }

    private function getReadRepository(): CategoryReadRepositoryInterface
    {
        return $this->getContainer()->get(CategoryReadRepositoryInterface::class);
    }

    private static function validTranslations(): array
    {
        return [
            'en' => ['name' => 'Electronics', 'description' => 'All electronics'],
            'uk' => ['name' => 'Електроніка', 'description' => 'Вся електроніка'],
        ];
    }
}

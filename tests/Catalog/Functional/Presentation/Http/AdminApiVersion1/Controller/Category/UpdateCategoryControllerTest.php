<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Category;

use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Translation;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Category\UpdateCategoryController;
use App\Shared\Domain\Enum\ErrorCodeEnum as SharedErrorCodeEnum;
use App\Tests\Catalog\Support\Traits\CategoryFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateCategoryControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use CategoryFactoryTrait;

    private const string ROUTE_NAME = UpdateCategoryController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_PUT;

    protected function tearDown(): void
    {
        $this->clearIdentity();

        parent::tearDown();
    }

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(['id' => 123]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(['id' => 123]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItSuccessfullyUpdatesCategory(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $oldParent = $this->getCategoryFixture()->create(path: '/home', slug: 'home');
        $newParent = $this->getCategoryFixture()->create(path: '/work', slug: 'work');
        $category = $this->getCategoryFixture()->create(parentId: $oldParent->getId()->value(), slug: 'electronic');

        $payload = [
            'slug' => 'electronics',
            'parentId' => $newParent->getId()->value(),
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
            'version' => $category->getVersion()->value(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $category->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        self::assertStringContainsString('Category was successfully updated', $data['message']);

        $updated = $this->getReadRepository()->findById($category->getId());
        self::assertSame('electronics', $updated->getSlug()->value());
        self::assertSame(
            expected: $newParent->getPath()->value().Path::SEPARATOR.$payload['slug'],
            actual: $updated->getPath()->value(),
            message: 'Category path was not matched. Check the path generation logic'
        );
    }

    public function testItReturns409OnConcurrencyError(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $category = $this->getCategoryFixture()->create(slug: 'old');

        $payload = [
            'slug' => 'new',
            'parentId' => null,
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
            'version' => $category->getVersion()->value() + 1,
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $category->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: SharedErrorCodeEnum::ConcurrencyError->value,
            expectedContainMessage: 'The Category has been already modified. Please refresh the page and try again'
        );
    }

    public function testItReturns409WhenSlugAlreadyExists(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $existingCategory = $this->getCategoryFixture()->create(slug: 'existing');
        $category = $this->getCategoryFixture()->create(slug: 'electronics');

        $payload = [
            'slug' => $existingCategory->getSlug()->value(),
            'parentId' => null,
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
            'version' => $category->getVersion()->value(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $category->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::CategoryAlreadyExists->value,
            expectedContainMessage: sprintf('Category with slug "%s" already exists', $payload['slug']),
        );
    }

    public function testItReturns409WhenTryMakeParentOfItself(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $category = $this->getCategoryFixture()->create(slug: 'electronics');

        $payload = [
            'slug' => 'electronics',
            'parentId' => $category->getId()->value(),
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
            'version' => $category->getVersion()->value(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $category->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::CategoryCannotBeParentOfItselfConflict->value,
            expectedContainMessage: 'Category cannot be parent to itself'
        );
    }

    public function testItReturns409WhenTryMakeChildAsParent(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $parent = $this->getCategoryFixture()->create(path: '/electronics', slug: 'electronics');
        $child = $this->getCategoryFixture()->create(
            parentId: $parent->getId()->value(),
            path: '/electronics/phones',
            slug: 'phones'
        );

        $payload = [
            'slug' => $parent->getSlug()->value(),
            'parentId' => $child->getId()->value(),
            'status' => StatusEnum::Active->value,
            'translations' => self::validTranslations(),
            'version' => $parent->getVersion()->value(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $parent->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::CategoryChildCanNotBeParentConflictException->value,
            expectedContainMessage: 'A child category cannot be set as a parent'
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
            uri: $this->getUrl(['id' => 123]),
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
            'version' => 1,
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

    public function testItReturns404OnInvalidRouteParameterFormat(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: '/admin/api/v1/catalog/categories/invalid-string'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: SharedErrorCodeEnum::NotFound->value,
            expectedContainMessage: 'No route found for'
        );
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }

    private function getReadRepository(): CategoryReadRepositoryInterface
    {
        return self::getContainer()->get(CategoryReadRepositoryInterface::class);
    }

    private static function validTranslations(): array
    {
        return [
            'en' => ['name' => 'Electronics', 'description' => 'All electronics'],
            'uk' => ['name' => 'Електроніка', 'description' => 'Вся електроніка'],
        ];
    }
}

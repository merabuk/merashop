<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\GetAttributeItemController;
use App\Shared\Domain\Enum\ErrorCodeEnum as SharedErrorCodeEnum;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeItemControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use AttributeFactoryTrait;
    use BaseUriTrait;

    private const string ROUTE_NAME = GetAttributeItemController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_GET;

    protected function tearDown(): void
    {
        $this->clearIdentity();

        parent::tearDown();
    }

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => 123])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsForbiddenForRegularUsers(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => 123])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[DataProvider('validAttributeProvider')]
    public function testItReturnsAttributeItem(TypeEnum $type): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(type: $type);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $attribute->getId()->value()])
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertIsArray($data);
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('code', $data);
        self::assertArrayHasKey('type', $data);
        self::assertArrayHasKey('translations', $data);
        self::assertArrayHasKey('version', $data);
        self::assertArrayHasKey('options', $data);
        self::assertSame($attribute->getId()->value(), $data['id']);
        self::assertSame($attribute->getCode()->value(), $data['code']);
        self::assertSame($attribute->getType()->value()->value, $data['type']);
        foreach ($attribute->getTranslations() as $locale => $translation) {
            self::assertArrayHasKey($locale, $data['translations']);
            self::assertArrayHasKey('name', $data['translations'][$locale]);
            self::assertSame($translation->name, $data['translations'][$locale]['name']);
        }
        self::assertSame($attribute->getVersion()->value(), $data['version']);
        self::assertSame($attribute->getOptions()->count(), count($data['options']));
        foreach ($attribute->getOptions() as $i => $option) {
            self::assertArrayHasKey($i, $data['options']);
            self::assertArrayHasKey('ulid', $data['options'][$i]);
            self::assertArrayHasKey('code', $data['options'][$i]);
            self::assertArrayHasKey('translations', $data['options'][$i]);
            self::assertArrayHasKey('isActive', $data['options'][$i]);
            $metadata = $option->getMetadata();
            if ($metadata) {
                self::assertArrayHasKey('metadata', $data['options'][$i]);
            } else {
                self::assertArrayNotHasKey('metadata', $data['options'][$i]);
            }
            self::assertSame($option->getUlid()->value(), $data['options'][$i]['ulid']);
            self::assertSame($option->getCode()->value(), $data['options'][$i]['code']);
            foreach ($option->getTranslations() as $locale => $translation) {
                self::assertArrayHasKey($locale, $data['options'][$i]['translations']);
                self::assertArrayHasKey('value', $data['options'][$i]['translations'][$locale]);
                self::assertSame($translation->value, $data['options'][$i]['translations'][$locale]['value']);
            }
            self::assertSame($option->isActive()->value(), $data['options'][$i]['isActive']);
            match (true) {
                $metadata instanceof DimensionMetadata => self::assertSame(
                    ['baseRatio' => (string) $metadata],
                    $data['options'][$i]['metadata']),
                default => self::assertNull($data['options'][$i]['metadata']),
            };
        }
    }

    public static function validAttributeProvider(): iterable
    {
        yield 'without option' => [TypeEnum::String];
        yield 'with option' => [TypeEnum::Select];
        yield 'with option and metadata' => [TypeEnum::Dimension];
    }

    public function testItReturns404OnNonExistingAttribute(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $id = 123;

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $id])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::AttributeNotFound->value,
            expectedContainMessage: sprintf('Attribute with id "%s" not found', $id)
        );
    }

    public function testItReturns404OnInvalidRouteParameterFormat(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: '/admin/api/v1/catalog/attributes/invalid-string'
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
}

<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Product;

use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Translation;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Product\CreateProductController;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Catalog\Support\Traits\CatalogStorageTestTrait;
use App\Tests\Catalog\Support\Traits\CategoryFactoryTrait;
use App\Tests\Catalog\Support\Traits\ProductFactoryTrait;
use App\Tests\Catalog\Support\Traits\TemporaryImageFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateProductControllerTest extends WebTestCase
{
    use AttributeFactoryTrait;
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use CatalogStorageTestTrait;
    use CategoryFactoryTrait;
    use ProductFactoryTrait;
    use TemporaryImageFactoryTrait;

    private const string ROUTE_NAME = CreateProductController::ROUTE_NAME;
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

    public function testItSuccessfullyCreatesProduct(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute1 = $this->getAttributeFixture()->create(code: 'model', type: AttributeTypeEnum::String);
        $attribute2 = $this->getAttributeFixture()->create(code: 'year-of-manufacture', type: AttributeTypeEnum::Int);
        $attribute3 = $this->getAttributeFixture()->create(code: '4G', type: AttributeTypeEnum::Boolean);
        // TODO: rework on attribute_options
        $attribute4 = $this->getAttributeFixture()->create(code: 'wireless_tech', type: AttributeTypeEnum::Select);

        $category1 = $this->getCategoryFixture()->create(slug: 'electronics');
        $category2 = $this->getCategoryFixture()->create(slug: 'phones');

        $context = ContextEnum::ProductMain;
        $temporaryImage1 = $this->getTemporaryImageFixture()->create(context: $context);
        $temporaryImage2 = $this->getTemporaryImageFixture()->create(context: $context);
        $this->putImageToStorage($temporaryImage1->getPath()->value());
        $this->putImageToStorage($temporaryImage2->getPath()->value());

        $payload = [
            'sku' => 'APL-IPH17P-256SLV',
            'status' => StatusEnum::Active->value,
            'prices' => self::getValidPrices(),
            'categoryIds' => [$category1->getId()->value(), $category2->getId()->value()],
            'attributeValues' => self::getValidAttributeValues(
                stringAttributeId: $attribute1->getId()->value(),
                intAttributeId: $attribute2->getId()->value(),
                booleanAttributeId: $attribute3->getId()->value(),
                selectAttributeId: $attribute4->getId()->value(),
            ),
            'translations' => self::validTranslations(),
            'images' => [$temporaryImage1->getUlid()->value(), $temporaryImage2->getUlid()->value()],
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
        $this->assertStringContainsString('Product was successfully created', $data['message']);

        $exists = $this->getReadRepository()->existsBySku(Sku::fromString($payload['sku']));
        $this->assertTrue($exists, 'Product was not saved to database');
    }

    public function testItReturns409WhenSkuAlreadyExists(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $existingProduct = $this->getProductFixture()->create();

        $payload = [
            'sku' => $existingProduct->getSku()->value(),
            'status' => StatusEnum::Draft->value,
            'prices' => [
                [
                    'amount' => 100_000,
                    'currency' => CurrencyEnum::UAH->value,
                    'type' => TypeEnum::Regular->value,
                    'taxValue' => 100,
                    'taxType' => TaxTypeEnum::Fixed->value,
                    'taxIncluded' => false,
                ],
            ],
            'categoryIds' => [],
            'attributeValues' => [],
            'translations' => self::validTranslations(),
            'images' => [],
        ];

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(), payload: $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::ProductAlreadyExists->value,
            expectedContainMessage: sprintf('Product with sku "%s" already exists', $payload['sku']),
        );
    }

    #[DataProvider('invalidProductProvider')]
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

    public static function invalidProductProvider(): iterable
    {
        $prices = self::getValidPrices();
        $payload = [
            'sku' => 'SKU-TEST-123',
            'status' => StatusEnum::Active->value,
            'prices' => $prices,
            'translations' => self::validTranslations(),
            'categoryIds' => self::getFakeValidCategoryIds(),
            'attributeValues' => self::getValidAttributeValues(),
            'images' => self::getFakeValidImageUlids(),
        ];

        yield 'empty sku' => [
            'payload' => [
                ...$payload,
                'sku' => '',
            ],
            'expectedErrorFields' => ['sku'],
        ];
        yield 'sku too short' => [
            'payload' => [
                ...$payload,
                'sku' => str_repeat('A', Sku::MIN_LENGTH - 1),
            ],
            'expectedErrorFields' => ['sku'],
        ];
        yield 'sku too long' => [
            'payload' => [
                ...$payload,
                'sku' => str_repeat('A', Sku::MAX_LENGTH + 1),
            ],
            'expectedErrorFields' => ['sku'],
        ];
        yield 'invalid status' => [
            'payload' => [
                ...$payload,
                'status' => 'wrong_status',
            ],
            'expectedErrorFields' => ['status'],
        ];
        yield 'empty prices' => [
            'payload' => [
                ...$payload,
                'prices' => [],
            ],
            'expectedErrorFields' => ['prices'],
        ];
        yield 'prices duplicated' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    $prices[0],
                    $prices[0],
                ],
            ],
            'expectedErrorFields' => ['prices[1]'],
        ];
        yield 'invalid price amount' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'amount' => -1,
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].amount'],
        ];
        yield 'invalid price currency' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'currency' => 'invalid_currency',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].currency'],
        ];
        yield 'price currency not supported' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'currency' => 'ZWD',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].currency'],
        ];
        yield 'invalid price type' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'type' => 'invalid_type',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].type'],
        ];
        yield 'invalid price fixed tax value' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'taxType' => TaxTypeEnum::Fixed->value,
                        'taxValue' => -1,
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].taxValue'],
        ];
        yield 'invalid price tax value' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'taxValue' => 101,
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].taxValue'],
        ];
        yield 'invalid price tax type' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'taxType' => 'invalid_type',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].taxType'],
        ];
        yield 'invalid price tax included' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'taxIncluded' => 'invalid_value',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].taxIncluded'],
        ];
        yield 'forbidden validity period for price type' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'validFrom' => '2024-01-01T00:00:00+00:00',
                        'validTo' => '2024-01-31T23:59:59+00:00',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].validFrom', 'prices[0].validTo'],
        ];
        yield 'invalid price formate of validity period' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'type' => TypeEnum::Sale->value,
                        'validFrom' => 'invalid_date',
                        'validTo' => 'invalid_date',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].validFrom', 'prices[0].validTo'],
        ];
        yield 'invalid price range of validity period' => [
            'payload' => [
                ...$payload,
                'prices' => [
                    [
                        ...$prices[0],
                        'type' => TypeEnum::Sale->value,
                        'validFrom' => '2024-01-31T23:59:59+00:00',
                        'validTo' => '2024-01-01T00:00:00+00:00',
                    ],
                ],
            ],
            'expectedErrorFields' => ['prices[0].validFrom', 'prices[0].validTo'],
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
            'expectedErrorFields' => ['translations[en].name', 'translations[en].description'],
        ];
        yield 'empty category ids' => [
            'payload' => [
                ...$payload,
                'categoryIds' => [],
            ],
            'expectedErrorFields' => ['categoryIds'],
        ];
        yield 'invalid category id' => [
            'payload' => [
                ...$payload,
                'categoryIds' => [1, -1],
            ],
            'expectedErrorFields' => ['categoryIds[1]'],
        ];
        yield 'empty attribute values' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [],
            ],
            'expectedErrorFields' => ['attributeValues'],
        ];
        yield 'invalid attribute value' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'value' => 12.34,
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].value'],
        ];
        yield 'empty images' => [
            'payload' => [
                ...$payload,
                'images' => [],
            ],
            'expectedErrorFields' => ['images'],
        ];
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }

    private function getReadRepository(): ProductReadRepositoryInterface
    {
        return $this->getContainer()->get(ProductReadRepositoryInterface::class);
    }

    private static function getValidPrices(): array
    {
        return [
            [
                'amount' => 65_000_00,
                'currency' => CurrencyEnum::UAH->value,
                'type' => TypeEnum::Regular->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
            ],
            [
                'amount' => 1_500_00,
                'currency' => CurrencyEnum::USD->value,
                'type' => TypeEnum::Regular->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
            ],
            [
                'amount' => 55_000_00,
                'currency' => CurrencyEnum::UAH->value,
                'type' => TypeEnum::Sale->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
                'validFrom' => '2024-01-01T00:00:00+00:00',
                'validTo' => '2024-01-31T23:59:59+00:00',
            ],
            [
                'amount' => 1_250_00,
                'currency' => CurrencyEnum::USD->value,
                'type' => TypeEnum::Sale->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
                'validFrom' => '2024-01-01T00:00:00+00:00',
                'validTo' => '2024-01-31T23:59:59+00:00',
            ],
            [
                'amount' => 43_333_33,
                'currency' => CurrencyEnum::UAH->value,
                'type' => TypeEnum::Cost->value,
                'taxValue' => 0.0,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
            ],
            [
                'amount' => 1_000_00,
                'currency' => CurrencyEnum::USD->value,
                'type' => TypeEnum::Cost->value,
                'taxValue' => 0.0,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
            ],
        ];
    }

    private static function validTranslations(): array
    {
        return [
            'en' => [
                'name' => 'Apple iPhone 17 Pro 256GB Silver (MG8G4)',
                'description' => 'The iPhone 17 Pro is a premium smartphone that combines cutting-edge technology, powerful performance, and high-quality materials.',
            ],
            'uk' => [
                'name' => 'Смартфон Apple iPhone 17 Pro 256GB Silver (MG8G4)',
                'description' => 'iPhone 17 Pro - це преміальний смартфон, що поєднує передові технології, потужну продуктивність і високоякісні матеріали.',
            ],
        ];
    }

    private static function getFakeValidCategoryIds(): array
    {
        return [1, 2, 3];
    }

    private static function getValidAttributeValues(
        ?int $stringAttributeId = null,
        ?int $intAttributeId = null,
        ?int $booleanAttributeId = null,
        ?int $selectAttributeId = null,
    ): array {
        return [
            [
                'attributeId' => $stringAttributeId ?? 1,
                'value' => 'iPhone 17 Pro',
            ],
            [
                'attributeId' => $intAttributeId ?? 2,
                'value' => 2024,
            ],
            [
                'attributeId' => $booleanAttributeId ?? 3,
                'value' => true,
            ],
            [
                'attributeId' => $selectAttributeId ?? 4,
                'value' => ['WI-FI', 'Bluetooth'],
            ],
        ];
    }

    private static function getFakeValidImageUlids(): array
    {
        return [
            '01KKTVY7D6D7S1BCSBB3GQA8B3',
            '01KKTVY7D6D7S1BCSBB3GQA8B4',
            '01KKTVY7D6D7S1BCSBB3GQA8B5',
        ];
    }
}

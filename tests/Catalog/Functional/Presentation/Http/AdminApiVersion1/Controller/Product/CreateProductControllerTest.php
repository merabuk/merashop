<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Product;

use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum as ProductPriceTypeEnum;
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

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItSuccessfullyCreatesProduct(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $option1 = $this->getAttributeOptionMother()->create(code: 'bluetooth');
        $option2 = $this->getAttributeOptionMother()->create(code: 'wifi');

        $attribute1 = $this->getAttributeFixture()->create(code: 'model', type: AttributeTypeEnum::String);
        $attribute2 = $this->getAttributeFixture()->create(
            code: 'year-of-manufacture',
            type: AttributeTypeEnum::Integer
        );
        $attribute3 = $this->getAttributeFixture()->create(code: '4g', type: AttributeTypeEnum::Boolean);
        $attribute4 = $this->getAttributeFixture()->create(
            code: 'wireless-tech',
            type: AttributeTypeEnum::Select,
            options: [$option1, $option2]
        );

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
            'attributeValues' => [
                [
                    'attributeId' => $attribute1->getId()->value(),
                    'type' => $attribute1->getType()->value()->value,
                    'translations' => [
                        'en' => 'Apple iPhone 17 Pro 256GB Silver (MG8G4)',
                        'uk' => 'Apple iPhone 17 Pro 256GB Сірий (MG8G4)',
                    ],
                ],
                [
                    'attributeId' => $attribute2->getId()->value(),
                    'type' => $attribute2->getType()->value()->value,
                    'value' => 2024,
                ],
                [
                    'attributeId' => $attribute3->getId()->value(),
                    'type' => $attribute3->getType()->value()->value,
                    'value' => true,
                ],
                [
                    'attributeId' => $attribute4->getId()->value(),
                    'type' => $attribute4->getType()->value()->value,
                    'value' => $attribute4->getOptions()->all()[0]->getId()->value(),
                ],
            ],
            'translations' => self::validTranslations(),
            'images' => [$temporaryImage1->getUlid()->value(), $temporaryImage2->getUlid()->value()],
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: $payload,
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        self::assertStringContainsString('Product was successfully created', $data['message']);

        $exists = $this->getReadRepository()->existsBySku(Sku::fromString($payload['sku']));
        self::assertTrue($exists, 'Product was not saved to database');
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
                    'type' => ProductPriceTypeEnum::Regular->value,
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

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

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
            'attributeValues' => self::getFakeValidAttributeValues(),
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
                        'type' => ProductPriceTypeEnum::Sale->value,
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
                        'type' => ProductPriceTypeEnum::Sale->value,
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
        yield 'invalid attribute id' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 0,
                        'type' => AttributeTypeEnum::Integer->value,
                        'value' => 123,
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].attributeId'],
        ];
        yield 'invalid attribute type' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'type' => 'type',
                        'value' => 'invalid',
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].type'],
        ];
        yield 'missing and invalid attribute string value' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'type' => AttributeTypeEnum::String->value,
                        'translations' => [
                            'en' => '',
                            'xx' => 'Value',
                        ],
                    ],
                ],
            ],
            'expectedErrorFields' => [
                'attributeValues[0].translations',
                'attributeValues[0].translations[en]',
                'attributeValues[0].translations[xx]',
            ],
        ];
        yield 'invalid attribute integer value' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'type' => AttributeTypeEnum::Integer->value,
                        'value' => 'invalid',
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].value'],
        ];
        yield 'invalid attribute select value' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'type' => AttributeTypeEnum::Select->value,
                        'value' => 0,
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].value'],
        ];
        yield 'invalid attribute multiselect value' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'type' => AttributeTypeEnum::MultiSelect->value,
                        'values' => [0],
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].values[0]'],
        ];
        yield 'invalid attribute dimension magnitude value' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'type' => AttributeTypeEnum::Dimension->value,
                        'magnitude' => 'invalid',
                        'unitOptionId' => 1,
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].magnitude'],
        ];
        yield 'invalid attribute dimension unitOptionId value' => [
            'payload' => [
                ...$payload,
                'attributeValues' => [
                    [
                        'attributeId' => 1,
                        'type' => AttributeTypeEnum::Dimension->value,
                        'magnitude' => 0.1,
                        'unitOptionId' => -1,
                    ],
                ],
            ],
            'expectedErrorFields' => ['attributeValues[0].unitOptionId'],
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
                'type' => ProductPriceTypeEnum::Regular->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
            ],
            [
                'amount' => 1_500_00,
                'currency' => CurrencyEnum::USD->value,
                'type' => ProductPriceTypeEnum::Regular->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
            ],
            [
                'amount' => 55_000_00,
                'currency' => CurrencyEnum::UAH->value,
                'type' => ProductPriceTypeEnum::Sale->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
                'validFrom' => '2024-01-01T00:00:00+00:00',
                'validTo' => '2024-01-31T23:59:59+00:00',
            ],
            [
                'amount' => 1_250_00,
                'currency' => CurrencyEnum::USD->value,
                'type' => ProductPriceTypeEnum::Sale->value,
                'taxValue' => 0.5,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
                'validFrom' => '2024-01-01T00:00:00+00:00',
                'validTo' => '2024-01-31T23:59:59+00:00',
            ],
            [
                'amount' => 43_333_33,
                'currency' => CurrencyEnum::UAH->value,
                'type' => ProductPriceTypeEnum::Cost->value,
                'taxValue' => 0.0,
                'taxType' => TaxTypeEnum::Percentage->value,
                'taxIncluded' => true,
            ],
            [
                'amount' => 1_000_00,
                'currency' => CurrencyEnum::USD->value,
                'type' => ProductPriceTypeEnum::Cost->value,
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

    private static function getFakeValidAttributeValues(): array
    {
        return [
            [
                'attributeId' => 1,
                'type' => AttributeTypeEnum::Integer->value,
                'value' => 123,
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

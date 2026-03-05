<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeTranslation;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Catalog\Support\Traits\CatalogEntityManagerTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AttributeWriteRepositoryTest extends KernelTestCase
{
    use AttributeFactoryTrait;
    use CatalogEntityManagerTrait;
    use EntityTechnicalMetadataTrait;

    private AttributeWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(AttributeWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $attribute = $this->getAttributeMother()->create();

        $created = $this->repository->save($attribute);

        self::assertNotNull($created->getId());
        self::assertTrue($attribute->getUlid()->equals($created->getUlid()));
        self::assertTrue($attribute->getCode()->equals($created->getCode()));
        self::assertTrue($attribute->getType()->equals($created->getType()));
        self::assertCount($attribute->getTranslations()->count(), $created->getTranslations());
        foreach ($attribute->getTranslations() as $locale => $translation) {
            $createdTranslation = $created->getTranslations()->get($locale);
            self::assertNotNull($createdTranslation);
            self::assertSame($translation->name, $createdTranslation->name);
        }
        self::assertSame(1, $created->getVersion()->value(), 'Initial version should be 1');
        self::assertTrue($attribute->getCreatedBy()->equals($created->getCreatedBy()));
        self::assertNull($created->getUpdatedBy());
    }

    public function testDelete(): void
    {
        $attribute = $this->getAttributeFixture()->create();
        $id = $attribute->getId()->value();

        $ormAttribute = $this->findOrmEntity(OrmAttribute::class, $id);
        $translationIds = $ormAttribute->translations->map(fn (OrmAttributeTranslation $t) => $t->id)->toArray();

        $this->clearEntityManager();

        $this->repository->delete($attribute);

        self::assertNull(
            actual: $this->getReadRepository()->findById($attribute->getId()),
            message: 'The attribute should be deleted'
        );

        foreach ($translationIds as $translationId) {
            self::assertNull(
                actual: $this->findOrmEntity(OrmAttributeTranslation::class, $translationId),
                message: sprintf('Translation with ID %d should be deleted by cascade', $translationId)
            );
        }
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $attribute = $this->getAttributeFixture()->create();
        $id = $attribute->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmAttribute::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
        $this->assertHasUpdatedAt($ormEntity);
    }

    private function getReadRepository(): AttributeReadRepositoryInterface
    {
        return self::getContainer()->get(AttributeReadRepositoryInterface::class);
    }
}

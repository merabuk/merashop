<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Tests\Catalog\Support\Traits\CatalogEntityManagerTrait;
use App\Tests\Catalog\Support\Traits\TemporaryImageFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TemporaryImageReadRepositoryTest extends KernelTestCase
{
    use TemporaryImageFactoryTrait;
    use CatalogEntityManagerTrait;

    private TemporaryImageReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(TemporaryImageReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $temporaryImage = $this->getTemporaryImageFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($temporaryImage->getId());

        self::assertNotNull($found);
        self::assertTrue($temporaryImage->getUlid()->equals($found->getUlid()));
        self::assertTrue($temporaryImage->getPath()->equals($found->getPath()));
        self::assertTrue($temporaryImage->getContext()->equals($found->getContext()));
    }

    public function testFindByUlid(): void
    {
        $temporaryImage = $this->getTemporaryImageFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($temporaryImage->getUlid());

        self::assertNotNull($found);
        self::assertTrue($temporaryImage->getId()->equals($found->getId()));
    }

    public function testFindByUlids(): void
    {
        $temporaryImage1 = $this->getTemporaryImageFixture()->create();
        $temporaryImage2 = $this->getTemporaryImageFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlids([$temporaryImage2->getUlid(), $temporaryImage1->getUlid()]);

        self::assertCount(2, $found);
        self::assertTrue($temporaryImage1->getUlid()->equals($found[0]->getUlid()));
        self::assertTrue($temporaryImage2->getUlid()->equals($found[1]->getUlid()));
    }

    public function testAssertAllExistByUlidsAndContext(): void
    {
        $context = ContextEnum::ProductMain;
        $temporaryImage1 = $this->getTemporaryImageFixture()->create(context: $context);
        $temporaryImage2 = $this->getTemporaryImageFixture()->create(context: $context);
        $this->clearEntityManager();

        $this->repository->assertAllExistByUlidsAndContext(
            ulids: [$temporaryImage1->getUlid(), $temporaryImage2->getUlid()],
            context: $context,
        );

        self::expectNotToPerformAssertions();
    }

    public function testAssertAllExistByUlidsAndContextThrowsExceptionOnFailure(): void
    {
        $context = ContextEnum::ProductMain;
        $temporaryImage1 = $this->getTemporaryImageFixture()->create(context: $context);
        $temporaryImage2 = $this->getTemporaryImageFixture()->create(context: ContextEnum::CategoryIcon);
        $this->clearEntityManager();

        $this->expectException(OneOfTemporaryImagesNotFoundException::class);

        $this->repository->assertAllExistByUlidsAndContext(
            ulids: [
                $temporaryImage1->getUlid(),
                $temporaryImage2->getUlid(),
            ],
            context: $context,
        );
    }
}

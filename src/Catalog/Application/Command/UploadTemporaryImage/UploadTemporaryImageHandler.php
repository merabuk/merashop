<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UploadTemporaryImage;

use App\Catalog\Application\Exception\TemporaryImage\UploadTemporaryImageException;
use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\Service\CatalogStorageInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Context;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UploadTemporaryImageHandler implements CommandHandlerInterface
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
        private CatalogStorageInterface $catalogStorage,
        private TemporaryImageWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws UploadTemporaryImageException
     */
    public function __invoke(UploadTemporaryImageCommand $command): string
    {
        try {
            $file = $command->file;

            $ulid = $this->ulidGenerator->next();
            $ulidVo = Ulid::fromString($ulid);

            $targetPath = $this->catalogStorage->generateTemporaryImageStoragePath($ulidVo, $file);

            $this->catalogStorage->uploadFromLocalPath($file->getLocalPath(), $targetPath->value());

            $temporaryImage = TemporaryImage::create(
                ulid: $ulidVo,
                path: $targetPath,
                context: Context::fromEnum($command->context)
            );

            $temporaryImage = $this->writeRepository->save($temporaryImage);

            return $temporaryImage->getUlid()->value();
        } catch (Throwable $e) {
            throw new UploadTemporaryImageException(message: 'Fail to upload temporary image', previous: $e);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

interface ClientFacingExceptionInterface extends AppExceptionInterface
{
    public function getPublicMessage(): string;
}

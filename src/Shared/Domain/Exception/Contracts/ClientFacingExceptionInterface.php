<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Contracts;

interface ClientFacingExceptionInterface extends AppExceptionInterface
{
    public function getPublicMessage(): string;
}

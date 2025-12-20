<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Repository;

use App\EmailSender\Domain\Entity\OutboxEmail;

interface OutboxEmailReadRepositoryInterface
{
    public function findById(int $id): ?OutboxEmail;
}

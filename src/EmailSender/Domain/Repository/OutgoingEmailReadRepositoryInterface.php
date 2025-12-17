<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Repository;

use App\EmailSender\Domain\Entity\OutgoingEmail;

interface OutgoingEmailReadRepositoryInterface
{
    public function findById(int $id): ?OutgoingEmail;
}

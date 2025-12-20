<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Repository;

use App\EmailSender\Domain\Entity\OutboxEmail;

interface OutboxEmailWriteRepositoryInterface
{
    public function save(OutboxEmail $outgoingEmail): OutboxEmail;

    public function delete(OutboxEmail $outgoingEmail): void;
}

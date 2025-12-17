<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Repository;

use App\EmailSender\Domain\Entity\OutgoingEmail;

interface OutgoingEmailWriteRepositoryInterface
{
    public function save(OutgoingEmail $outgoingEmail): OutgoingEmail;

    public function delete(OutgoingEmail $outgoingEmail): void;
}

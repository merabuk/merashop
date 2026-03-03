<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Service;

interface OutboxEmailRelayServiceInterface
{
    public function execute(): int;
}

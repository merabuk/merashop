<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus\TraceId;

use App\Shared\Domain\ValueObject\Identity\TraceId;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class TraceIdStamp implements StampInterface
{
    public function __construct(public TraceId $traceId)
    {
    }
}

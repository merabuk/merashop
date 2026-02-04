<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class CurrentAuthEntityIdentity
{
}

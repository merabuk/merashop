<?php

namespace App\IdentityAccess\Presentation\Http\InternalApiVersion1\Resource;

use App\IdentityAccess\Presentation\Http\Common\Resource\AccessTokenResourceTrait;
use JsonSerializable;

final readonly class AccessTokenResource implements JsonSerializable
{
    use AccessTokenResourceTrait;
}

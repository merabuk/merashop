<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

final readonly class ApiRouteParams
{
    /**
     * Key for translating the name of an entity.
     */
    public const string ENTITY_LABEL = '_api_entity_label';

    /**
     * Translation domain for entity name.
     */
    public const string ENTITY_DOMAIN = '_api_entity_domain';
}

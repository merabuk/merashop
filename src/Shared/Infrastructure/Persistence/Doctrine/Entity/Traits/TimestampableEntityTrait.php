<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits;

use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Modernized Timestampable Entity Trait.
 *
 * @see TimestampableEntity
 */
trait TimestampableEntityTrait
{
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
}

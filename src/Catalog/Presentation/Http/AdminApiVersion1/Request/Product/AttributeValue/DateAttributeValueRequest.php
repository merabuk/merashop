<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\DateAttributeValueData;
use App\Shared\Domain\ValueObject\Temporal\DateValueObject;
use Symfony\Component\Validator\Constraints as Assert;

final class DateAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\DateTime(format: DateValueObject::INPUT_FORMAT, groups: [self::BASE_GROUP])]
    public ?string $date;

    public function toValueData(): DateAttributeValueData
    {
        return new DateAttributeValueData(value: $this->date);
    }
}

<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\DateAttributeValueData;
use App\Shared\Domain\ValueObject\Temporal\DateValueObject;
use Symfony\Component\Validator\Constraints as Assert;

final class DateAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\DateTime(format: DateValueObject::INPUT_FORMAT)]
    public ?string $date;

    public function toData(): DateAttributeValueData
    {
        return new DateAttributeValueData(value: $this->date);
    }
}

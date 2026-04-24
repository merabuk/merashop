<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Resolver;

use App\Shared\Infrastructure\Exception\Traits\UnprocessableEntityErrorTrait;
use App\Shared\Presentation\Http\Attribute\MapPagination;
use App\Shared\Presentation\Http\Request\PaginationRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class PaginationRequestResolver implements ValueResolverInterface
{
    use UnprocessableEntityErrorTrait;

    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return iterable<PaginationRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (PaginationRequest::class !== $argument->getType()) {
            return [];
        }

        /** @var ?MapPagination $attribute */
        $attribute = $argument->getAttributes(MapPagination::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;

        $dto = new PaginationRequest();
        $dto->setAllowedSortFields($attribute->allowedSortFields ?? []);
        $dto->lastSeenId = $request->query->get('lastSeenId');
        $dto->perPage = $request->query->has('perPage')
            ? (int) $request->query->get('perPage')
            : $dto->perPage;
        $dto->sortField = $request->query->get('sortField');
        $sortDir = $request->query->get('sortDir');
        $dto->sortDir = is_string($sortDir) ? mb_strtoupper($sortDir) : $dto->sortDir;
        $dto->filters = $request->query->all('filters');

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw $this->makeSystemValidationException(message: 'Map Pagination request validation failed', value: $dto, violations: $violations);
        }

        yield $dto;
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Resolver;

use App\Shared\Presentation\Http\Attribute\MapPagination;
use App\Shared\Presentation\Http\Request\PaginationRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class PaginationRequestResolver implements ValueResolverInterface
{
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
        $dto->filters = $request->query->all('filter');

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw new HttpException(statusCode: Response::HTTP_UNPROCESSABLE_ENTITY, message: 'Validation failed', previous: new ValidationFailedException($dto, $violations));
        }

        yield $dto;
    }
}

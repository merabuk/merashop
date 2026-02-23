<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Http\Resolver;

use App\Shared\Presentation\Http\Attribute\MapPagination;
use App\Shared\Presentation\Http\Request\PaginationRequest;
use App\Shared\Presentation\Http\Resolver\PaginationRequestResolver;
use App\Tests\Shared\Support\Traits\ResolverTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PaginationRequestResolverTest extends TestCase
{
    use ResolverTrait;

    public function testItPopulatesDtoFromRequest(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $resolver = new PaginationRequestResolver($validator);

        $request = new Request(query: [
            'perPage' => '20',
            'sortField' => 'name',
            'sortDir' => 'desc',
            'filter' => ['active' => '1'],
        ]);

        $attribute = new MapPagination(allowedSortFields: ['name']);

        $argument = $this->makeArgumentMetadata(
            name: 'pagination',
            type: PaginationRequest::class,
            attributes: [$attribute]
        );

        $results = iterator_to_array($resolver->resolve($request, $argument));

        self::assertNotEmpty($results);
        /** @var PaginationRequest $dto */
        $dto = $results[0];
        self::assertSame(20, $dto->perPage);
        self::assertSame('name', $dto->sortField);
        self::assertSame('DESC', $dto->sortDir);
        self::assertSame(['active' => '1'], $dto->filters);
    }

    public function testThrowsExceptionWhenValidationFails(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList([$violation]));

        $resolver = new PaginationRequestResolver($validator);

        $attribute = $this->makeArgumentMetadata(name: 'pagination', type: PaginationRequest::class);

        $this->expectException(HttpException::class);

        iterator_to_array($resolver->resolve(new Request(), $attribute));
    }

    public function testItReturnsEmptyIfWrongType(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $resolver = new PaginationRequestResolver($validator);

        $attribute = $this->makeArgumentMetadata(name: 'pagination', type: stdClass::class);

        self::assertEmpty(iterator_to_array($resolver->resolve(new Request(), $attribute)));
    }
}

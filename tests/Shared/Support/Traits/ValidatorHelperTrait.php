<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @mixin TestCase
 */
trait ValidatorHelperTrait
{
    protected ValidatorInterface&MockObject $validator;
    protected ContextualValidatorInterface&MockObject $contextualValidator;

    protected function setValidator(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    protected function setContextualValidator(): void
    {
        $this->contextualValidator = $this->createMock(ContextualValidatorInterface::class);
    }

    protected function makeViolations(int $count = 0): ConstraintViolationListInterface
    {
        $mock = $this->createMock(ConstraintViolationListInterface::class);

        $mock->method('count')->willReturn($count);

        return $mock;
    }
}

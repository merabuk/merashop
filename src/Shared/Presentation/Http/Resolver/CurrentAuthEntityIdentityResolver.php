<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Resolver;

use App\Shared\Application\Security\AuthEntityContextInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class CurrentAuthEntityIdentityResolver implements ValueResolverInterface
{
    public function __construct(
        private AuthEntityContextInterface $authEntityContext,
    ) {
    }

    /**
     * @return iterable<?AuthIdentity>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributes(CurrentAuthEntityIdentity::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;

        if (!$attribute instanceof CurrentAuthEntityIdentity) {
            return [];
        }

        $authEntityIdentity = $this->authEntityContext->getIdentity();

        if (null === $authEntityIdentity && !$argument->isNullable()) {
            throw new AccessDeniedException('Not authenticated.');
        }

        yield $authEntityIdentity;
    }
}

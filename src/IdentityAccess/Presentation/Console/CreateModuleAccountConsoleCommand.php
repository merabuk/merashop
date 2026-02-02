<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Console;

use App\IdentityAccess\Application\Command\CreateModuleAccount\CreateModuleAccountCommand;
use App\IdentityAccess\Application\Command\CreateModuleAccount\CreateModuleAccountHandler;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\Shared\Domain\Enum\ScopeEnum;
use App\Shared\Presentation\Console\BaseConsoleCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:identity-access:create-module',
    description: 'Create a new module account for M2M authentication'
)]
final class CreateModuleAccountConsoleCommand extends BaseConsoleCommand
{
    public function __construct(
        ValidatorInterface $validator,
        private readonly CreateModuleAccountHandler $handler,
        private readonly ModuleAccountReadRepositoryInterface $readRepository,
    ) {
        parent::__construct(validator: $validator);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'clientId',
                mode: InputArgument::OPTIONAL,
                description: 'The unique ID of the module'
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->io->title('Module Account Creation');

        if (null === $input->getArgument('clientId')) {
            $clientId = $this->askValid(
                question: 'Enter Client ID (e.g. billing-service)',
                constraints: [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 3),
                    new Assert\Callback(callback: function (string $value, ExecutionContextInterface $context) {
                        if ($this->readRepository->existsByClientId(ClientId::fromString($value))) {
                            $context->buildViolation('This Client ID is already in use.')
                                ->addViolation();
                        }
                    }),
                ]
            );
            $input->setArgument('clientId', $clientId);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clientId = (string) $input->getArgument('clientId');

        $scopes = ScopeEnum::getValues();
        $selectedScopes = $this->io->choice(
            question: 'Select Scopes for the module account',
            choices: $scopes,
            multiSelect: true
        );

        try {
            $command = new CreateModuleAccountCommand(clientId: $clientId, scopes: $selectedScopes);
            $plainSecret = ($this->handler)($command);

            $this->io->success('Module account created!');
            $this->io->info("Secret: {$plainSecret}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }
    }
}

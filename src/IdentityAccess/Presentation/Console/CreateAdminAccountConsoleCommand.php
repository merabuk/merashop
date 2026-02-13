<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Console;

use App\IdentityAccess\Application\Command\CreateAdminAccount\CreateAdminAccountCommand;
use App\IdentityAccess\Application\Command\CreateAdminAccount\CreateAdminAccountHandler;
use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountEmailException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Presentation\Console\BaseConsoleCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

#[AsCommand(
    name: 'app:identity-access:create-admin',
    description: 'Create a new admin account'
)]
final class CreateAdminAccountConsoleCommand extends BaseConsoleCommand
{
    public function __construct(
        ValidatorInterface $validator,
        private readonly CreateAdminAccountHandler $handler,
        private readonly AdminAccountReadRepositoryInterface $readRepository,
    ) {
        parent::__construct(validator: $validator);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'email',
                mode: InputArgument::OPTIONAL,
                description: 'Admin unique email'
            )
            ->addArgument(
                name: 'status',
                mode: InputArgument::OPTIONAL,
                description: 'Admin status'
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->io->title('Admin Account Creation');

        if (null === $input->getArgument('email')) {
            $email = $this->askValid(
                question: 'Enter admin email',
                constraints: [
                    new Assert\NotBlank(),
                    new Assert\Length(max: EmailAddress::MAX_LENGTH),
                    new Assert\Email(),
                    new Assert\Callback(callback: function (string $value, ExecutionContextInterface $context) {
                        try {
                            $email = EmailAddress::fromString($value);
                        } catch (InvalidAdminAccountEmailException) {
                            return;
                        }

                        if ($this->readRepository->existsByEmail($email)) {
                            $context->buildViolation('This email is already in use.')
                                ->addViolation();
                        }
                    }),
                ]
            );
            $input->setArgument('email', $email);
        }
        if (null === $input->getArgument('status')) {
            $status = $this->io->choice(
                question: 'Select admin status',
                choices: $this->getAvailableStatuses(),
                default: StatusEnum::Active->value
            );
            $input->setArgument('status', $status);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $status = (string) $input->getArgument('status');

        $roles = [RoleEnum::Admin->value];
        $selectedRoles = $this->io->choice(
            question: 'Select Roles for the admin account',
            choices: $roles,
            multiSelect: true
        );

        try {
            $command = new CreateAdminAccountCommand(
                email: $email,
                status: $status,
                roles: $selectedRoles
            );
            $plainSecret = ($this->handler)($command);

            $this->io->success('Admin account created!');
            $this->io->info("Temporary password: {$plainSecret}");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @return string[]
     */
    private function getAvailableStatuses(): array
    {
        return [
            StatusEnum::Active->value,
            StatusEnum::Inactive->value,
            StatusEnum::Draft->value,
        ];
    }
}

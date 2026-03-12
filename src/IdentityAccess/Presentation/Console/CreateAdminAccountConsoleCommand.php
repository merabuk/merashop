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
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

#[AsCommand(
    name: self::COMMAND_NAME,
    description: 'Create a new admin account'
)]
final class CreateAdminAccountConsoleCommand extends BaseConsoleCommand
{
    public const string COMMAND_NAME = 'app:identity_access:create-admin';

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
            )
            ->addOption(
                name: 'role',
                shortcut: 'r',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'Roles for the admin account'
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
                            $context->buildViolation('This email is already in use')
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
        if (empty($input->getOption('role'))) {
            $selectedRoles = $this->io->choice(
                question: 'Select Roles for the admin account',
                choices: $this->getAvailableRoles(),
                default: RoleEnum::Admin->value,
                multiSelect: true
            );
            $input->setOption('role', $selectedRoles);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $status = (string) $input->getArgument('status');
        $roles = (array) $input->getOption('role');

        try {
            $this->validateInputs($email, $status, $roles);

            $command = new CreateAdminAccountCommand(email: $email, status: $status, roles: $roles);
            $plainSecret = ($this->handler)($command);

            $this->io->success('Admin account created!');
            $this->io->writeln("Temporary password: <fg=yellow;options=bold>{$plainSecret}</fg>");

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

    /**
     * @return string[]
     */
    private function getAvailableRoles(): array
    {
        return [RoleEnum::Admin->value];
    }

    /**
     * @param string[] $roles
     */
    private function validateInputs(string $email, string $status, array $roles): void
    {
        if (empty($email) || empty($status) || empty($roles)) {
            throw new InvalidArgumentException('Missing required data. Provide email, status and role');
        }

        try {
            $emailVo = EmailAddress::fromString($email);
        } catch (Throwable) {
            throw new InvalidArgumentException(sprintf('Invalid email format: %s', $email));
        }

        if ($this->readRepository->existsByEmail($emailVo)) {
            throw new InvalidArgumentException(sprintf('Email "%s" already exists', $email));
        }

        if (!in_array($status, $this->getAvailableStatuses(), true)) {
            throw new InvalidArgumentException(sprintf('Invalid status "%s". Available: %s', $status, implode(', ', $this->getAvailableStatuses())));
        }

        $diff = array_diff($roles, $this->getAvailableRoles());
        if ([] !== $diff) {
            throw new InvalidArgumentException(sprintf('Invalid roles: "%s". Available: %s', implode('", "', $diff), implode(', ', $this->getAvailableRoles())));
        }
    }
}

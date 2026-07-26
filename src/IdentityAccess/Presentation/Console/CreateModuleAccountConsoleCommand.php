<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Console;

use App\IdentityAccess\Application\Command\CreateModuleAccount\CreateModuleAccountCommand;
use App\IdentityAccess\Application\Command\CreateModuleAccount\CreateModuleAccountHandler;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountClientIdException;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\Shared\Domain\Enum\ScopeEnum;
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
    description: 'Create a new module account for M2M authentication'
)]
final class CreateModuleAccountConsoleCommand extends BaseConsoleCommand
{
    public const string COMMAND_NAME = 'app:identity-access:create-module';

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
            )
            ->addOption(
                name: 'scope',
                shortcut: 's',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'Scopes for the module account'
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->output()->title('Module Account Creation');

        if (null === $input->getArgument('clientId')) {
            $clientId = $this->askValid(
                question: 'Enter Client ID (e.g. billing-service)',
                constraints: [
                    new Assert\NotBlank(),
                    new Assert\Length(min: ClientId::MIN_LENGTH),
                    new Assert\Callback(callback: function (string $value, ExecutionContextInterface $context) {
                        try {
                            $clientId = ClientId::fromString($value);
                        } catch (InvalidModuleAccountClientIdException) {
                            return;
                        }

                        if ($this->readRepository->existsByClientId($clientId)) {
                            $context->buildViolation('This Client ID is already in use')
                                ->addViolation();
                        }
                    }),
                ]
            );
            $input->setArgument('clientId', $clientId);
        }
        if (empty($input->getOption('scope'))) {
            $selectedScopes = $this->output()->choice(
                question: 'Select Scopes for the module account',
                choices: $this->getAvailableScopes(),
                multiSelect: true
            );
            $input->setOption('scope', $selectedScopes);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clientId = self::castToString(value: $input->getArgument('clientId'));
        $scopes = self::castToArrayOfStrings(value: $input->getOption('scope'));

        try {
            $this->validateInputs($clientId, $scopes);

            $command = new CreateModuleAccountCommand(clientId: $clientId, scopes: $scopes);
            $plainSecret = ($this->handler)($command);

            $this->output()->success('Module account created!');
            $this->output()->writeln("Secret: <fg=yellow;options=bold>{$plainSecret}</fg>");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->output()->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @return string[]
     */
    private function getAvailableScopes(): array
    {
        return ScopeEnum::getValues();
    }

    /**
     * @param string[] $scopes
     */
    private function validateInputs(string $clientId, array $scopes): void
    {
        if (empty($clientId) || empty($scopes)) {
            throw new InvalidArgumentException('Missing required data. Provide clientId and scope');
        }

        if (ClientId::MIN_LENGTH > mb_strlen($clientId)) {
            throw new InvalidArgumentException(sprintf('Client ID must be at least %d characters long', ClientId::MIN_LENGTH));
        }

        try {
            $clientIdVo = ClientId::fromString($clientId);
        } catch (InvalidModuleAccountClientIdException) {
            throw new InvalidArgumentException(sprintf('Invalid Client ID format: %s', $clientId));
        }

        if ($this->readRepository->existsByClientId($clientIdVo)) {
            throw new InvalidArgumentException(sprintf('Client ID "%s" already exists', $clientId));
        }

        $diff = array_diff($scopes, $this->getAvailableScopes());
        if ([] !== $diff) {
            throw new InvalidArgumentException(sprintf('Invalid scopes: "%s". Available: %s', implode('", "', $diff), implode(', ', $this->getAvailableScopes())));
        }
    }
}

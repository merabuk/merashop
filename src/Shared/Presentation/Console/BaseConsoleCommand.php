<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseConsoleCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        protected readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    public function output(): SymfonyStyle
    {
        if (null === $this->io) {
            throw new RuntimeException('Output property is not set. Please check that property is initialized properly.');
        }

        return $this->io;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param array<int, Constraint> $constraints
     */
    protected function askValid(string $question, array $constraints, ?string $default = null): mixed
    {
        $value = $this->output()->ask($question, $default);

        $violations = $this->validator->validate($value, $constraints);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->output()->error((string) $violation->getMessage());
            }

            return $this->askValid($question, $constraints, $default);
        }

        return $value;
    }

    protected function confirmAction(string $question, bool $default = true): bool
    {
        return $this->output()->confirm($question, $default);
    }
}

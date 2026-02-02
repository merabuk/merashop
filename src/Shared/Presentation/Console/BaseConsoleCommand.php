<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseConsoleCommand extends Command
{
    protected SymfonyStyle $io;

    public function __construct(
        protected readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
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
        $value = $this->io->ask($question, $default);

        $violations = $this->validator->validate($value, $constraints);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->io->error($violation->getMessage());
            }

            return $this->askValid($question, $constraints, $default);
        }

        return $value;
    }

    protected function confirmAction(string $question, bool $default = true): bool
    {
        return $this->io->confirm($question, $default);
    }
}

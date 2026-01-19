<?php

declare(strict_types=1);

namespace App\Tests\Users\Functional\Application\Command\CreateUser;

use App\Shared\Application\Command\CommandBusInterface;
use App\Users\Application\Command\CreateUser\CreateUserCommand;
use App\Users\Domain\Repository\UserReadRepositoryInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateUserCommandHandlerTest extends WebTestCase
{
    use ResetDatabase;

    private Generator $factory;
    private CommandBusInterface $commandBus;
    private UserReadRepositoryInterface $userReadRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::create();
        $this->commandBus = self::getContainer()->get(CommandBusInterface::class);
        $this->userReadRepository = self::getContainer()->get(UserReadRepositoryInterface::class);
    }

    public function testCreateUserIsSuccessful(): void
    {
        $command = new CreateUserCommand(
            firstName: $this->factory->firstName(),
            lastName: $this->factory->lastName(),
            email: $this->factory->safeEmail(),
            password: $this->factory->password(),
            phoneNumber: $this->factory->regexify('/\+380\d{9}/')
        );
        $userId = $this->commandBus->execute($command);

        $user = $this->userReadRepository->findById($userId);

        self::assertNotEmpty($user);
    }
}

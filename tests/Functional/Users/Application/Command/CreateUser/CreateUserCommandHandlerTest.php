<?php

declare(strict_types=1);

namespace App\Tests\Functional\Users\Application\Command\CreateUser;

use App\Shared\Application\Command\CommandBusInterface;
use App\Users\Application\Command\CreateUser\CreateUserCommand;
use App\Users\Application\Dto\CreateUserDto;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateUserCommandHandlerTest extends WebTestCase
{
    use ResetDatabase;

    private Generator $factory;
    private CommandBusInterface $commandBus;
    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::create();
        $this->commandBus = self::getContainer()->get(CommandBusInterface::class);
        $this->userRepository = self::getContainer()->get(UserRepositoryInterface::class);
    }

    public function testCreateUserIsSuccessful(): void
    {
        $data = new CreateUserDto(
            firstName: $this->factory->firstName(),
            lastName: $this->factory->lastName(),
            email: $this->factory->safeEmail(),
            password: $this->factory->password(),
            phoneNumber: $this->factory->phoneNumber()
        );

        $command = new CreateUserCommand($data);
        $userId = $this->commandBus->execute($command);

        $user = $this->userRepository->findById($userId);

        $this->assertNotEmpty($user);
    }
}

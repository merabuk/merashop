<?php

declare(strict_types=1);

namespace App\Tests\Functional\EmailSender\Domain;

use App\EmailSender\Domain\Exception\OutboxEmailAlreadyInProcessException;
use App\Tests\Resource\Fixture\EmailSender\OutboxEmailMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OutboxEmailTest extends KernelTestCase
{
    private OutboxEmailMother $mother;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->mother = $container->get(OutboxEmailMother::class);
    }

    public function testSuccessfulLock(): void
    {
        $email = $this->mother->createBaseEmail();

        $email->lock(new \DateTimeImmutable());

        $this->assertTrue($email->getStatus()->isProcessing());
    }

    public function testCannotLockAlreadyProcessingEmail(): void
    {
        $email = $this->mother->createLockedEmail();

        $this->expectException(OutboxEmailAlreadyInProcessException::class);
        $email->lock(new \DateTimeImmutable());
    }

    public function testCanBeProcessedOnlyWhenTimeHasCome(): void
    {
        $email = $this->mother->createFailedEmail();

        $this->assertFalse($email->canBeProcessed());
    }
}

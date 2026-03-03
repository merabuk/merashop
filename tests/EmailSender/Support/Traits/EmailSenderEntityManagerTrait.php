<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Support\Traits;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait EmailSenderEntityManagerTrait
{
    protected function getEmailSenderEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine')->getManager('email_sender');
    }

    protected function findOrmEntity(string $entityClass, int|string $id): object
    {
        return $this->getEmailSenderEntityManager()->find($entityClass, $id);
    }

    protected function clearEntityManager(): void
    {
        $this->getEmailSenderEntityManager()->clear();
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Support\Traits;

use App\Tests\Shared\Support\Traits\BaseEntityManagerTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait EmailSenderEntityManagerTrait
{
    use BaseEntityManagerTrait;

    protected function getEmailSenderEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine')->getManager('email_sender');
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getEmailSenderEntityManager();
    }
}

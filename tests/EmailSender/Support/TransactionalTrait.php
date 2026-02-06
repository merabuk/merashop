<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Support;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Throwable;

trait TransactionalTrait
{
    protected function executeInTransaction(EntityManagerInterface $em, callable $callback): void
    {
        $em->beginTransaction();
        try {
            $callback();
            $em->commit();
        } catch (Throwable $e) {
            $em->rollback();

            throw new RuntimeException('Error executing transaction: '.$e->getMessage(), previous: $e);
        }
    }
}

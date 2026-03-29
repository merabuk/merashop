<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Support\Traits;

use App\Tests\EmailSender\Support\OutboxEmailFixture;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait OutboxEmailFactoryTrait
{
    protected function getOutboxEmailMother(): OutboxEmailMother
    {
        return self::getContainer()->get(OutboxEmailMother::class);
    }

    protected function getOutboxEmailFixture(): OutboxEmailFixture
    {
        return self::getContainer()->get(OutboxEmailFixture::class);
    }
}

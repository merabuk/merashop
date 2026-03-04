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
        /** @var OutboxEmailMother $mother */
        $mother = self::getContainer()->get(OutboxEmailMother::class);

        return $mother;
    }

    protected function getOutboxEmailFixture(): OutboxEmailFixture
    {
        /** @var OutboxEmailFixture $fixture */
        $fixture = self::getContainer()->get(OutboxEmailFixture::class);

        return $fixture;
    }
}

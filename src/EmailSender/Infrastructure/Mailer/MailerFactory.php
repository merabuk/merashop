<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Mailer;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

readonly class MailerFactory
{
    public function __construct(
        #[AutowireLocator(
            services: 'email_sender.mailer',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $mailers,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function make(DriverEnum $driver): MailerInterface
    {
        if (!$this->mailers->has($driver->value)) {
            throw new \RuntimeException(sprintf('Mailer driver "%s" not found', $driver->value));
        }

        $mailer = $this->mailers->get($driver->value);

        if ($mailer instanceof MailerInterface) {
            return $mailer;
        }

        throw new \RuntimeException(sprintf('Mailer driver "%s" is not an instance of MailerInterface', $driver->value));
    }
}

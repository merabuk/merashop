<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Mailer;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Infrastructure\Exception\MailerFactoryException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

readonly class MailerFactory implements MailerFactoryInterface
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
     * @throws MailerFactoryException
     */
    public function make(DriverEnum $driver): MailerInterface
    {
        try {
            $id = $driver->value;

            if (!$this->mailers->has($id)) {
                throw new MailerFactoryException(sprintf('Mailer driver "%s" not found', $driver->value));
            }

            $mailer = $this->mailers->get($id);

            if ($mailer instanceof MailerInterface) {
                return $mailer;
            }

            throw new MailerFactoryException(sprintf('Mailer driver "%s" is not an instance of %s', $driver->value, MailerInterface::class));
        } catch (ContainerExceptionInterface $e) {
            throw new MailerFactoryException(message: 'Fail to get mailer', previous: $e);
        }
    }
}

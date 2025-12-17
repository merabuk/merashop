<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Entity;

use App\EmailSender\Domain\ValueObject\OutgoingEmail\Driver;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\From;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\Id;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\Status;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\To;

readonly class OutgoingEmail
{
    public function __construct(
        private ?Id $id,
        private Status $status,
        private Driver $driver,
        private Subject $subject,
        private From $from,
        private FromName $fromName,
        private To $to,
    ) {
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getFrom(): From
    {
        return $this->from;
    }

    public function getFromName(): FromName
    {
        return $this->fromName;
    }

    public function getTo(): To
    {
        return $this->to;
    }
}

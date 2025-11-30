<?php

namespace App\Users\Domain\Service;

interface PasswordHasherInterface
{
    public function hash(string $plainPassword): string;
}

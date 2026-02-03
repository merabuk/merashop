<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class JwtConfigFactory
{
    public static function create(string $privateKey, string $publicKey, string $passphrase): Configuration
    {
        $signer = new Sha256();
        $signingKey = InMemory::plainText($privateKey, $passphrase);
        $verificationKey = InMemory::plainText($publicKey);

        $config = Configuration::forAsymmetricSigner(
            signer: $signer,
            signingKey: $signingKey,
            verificationKey: $verificationKey
        );

        return $config->withValidationConstraints(
            new SignedWith($signer, $verificationKey),
            new LooseValidAt(SystemClock::fromSystemTimezone())
        );
    }
}

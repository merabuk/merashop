<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Jwt;

use App\Shared\Domain\Helpers\TypeCastingTrait;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\HasClaim;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class JwtConfigFactory
{
    use TypeCastingTrait;

    public const string CLAIM_ROLES = 'roles';
    public const string CLAIM_SCOPES = 'scopes';
    public const string CLAIM_SUBJECT_TYPE = 'sub_type';

    public static function create(string $privateKey, string $publicKey, string $passphrase): Configuration
    {
        $privateKey = self::castToNonEmptyString(string: $privateKey, message: 'Giving private key is empty');
        $publicKey = self::castToNonEmptyString(string: $publicKey, message: 'Giving public key is empty');

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
            new LooseValidAt(SystemClock::fromSystemTimezone()),
            new HasClaim(self::CLAIM_ROLES),
            new HasClaim(self::CLAIM_SCOPES),
            new HasClaim(self::CLAIM_SUBJECT_TYPE)
        );
    }
}

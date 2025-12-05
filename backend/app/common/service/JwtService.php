<?php
declare(strict_types=1);

namespace app\common\service;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Signer\Key\InMemory;

class JwtService
{
    private function __construct(){}

    public static function encode(array $claims, int $ttl = 3600, string $subject = '', ?string $audience = null): string
    {
        $cfg = self::config();
        $now = new DateTimeImmutable();
        $iss = self::env('JWT.ISSUER', '');
        $builder = $cfg->builder()
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->expiresAt($now->modify('+' . $ttl . ' seconds'));
        if ($iss !== '') {
            $builder = $builder->issuedBy($iss);
        }
        if ($subject !== '') {
            $builder = $builder->relatedTo($subject);
        }
        if (!empty($audience)) {
            $builder = $builder->permittedFor($audience);
        }
        foreach ($claims as $key => $value) {
            $builder = $builder->withClaim((string)$key, $value);
        }
        return $builder->getToken($cfg->signer(), $cfg->signingKey())->toString();
    }

    public static function decode(string $jwt): array
    {
        $cfg = self::config();
        $token = $cfg->parser()->parse(self::bearer($jwt));
        if (!$token instanceof Plain) {
            return [];
        }
        return $token->claims()->all();
    }

    public static function validate(string $jwt, ?string $expectedAudience = null): bool
    {
        $cfg = self::config();
        $token = $cfg->parser()->parse(self::bearer($jwt));
        $clock = new SystemClock(new DateTimeZone(\date_default_timezone_get()));
        $leeway = (int)self::env('JWT.LEEWAY', 0);
        $constraints = [
            new SignedWith($cfg->signer(), $cfg->verificationKey()),
            new ValidAt($clock, new DateInterval('PT' . max(0, $leeway) . 'S')),
        ];
        $iss = self::env('JWT.ISSUER', '');
        if ($iss !== '') {
            $constraints[] = new IssuedBy($iss);
        }
        $aud = $expectedAudience ?: self::env('JWT.AUDIENCE', '');
        if ($aud !== '') {
            $constraints[] = new PermittedFor($aud);
        }
        try {
            return $cfg->validator()->validate($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            return false;
        }
    }

    public static function verify(string $jwt): bool
    {
        $cfg = self::config();
        $token = $cfg->parser()->parse(self::bearer($jwt));
        try {
            return $cfg->validator()->validate($token, new SignedWith($cfg->signer(), $cfg->verificationKey()));
        } catch (RequiredConstraintsViolated $e) {
            return false;
        }
    }

    public static function refresh(string $jwt, int $ttl = 3600): ?string
    {
        if (!self::validate($jwt)) {
            return null;
        }
        $claims = self::decode($jwt);
        $subject = (string)($claims['sub'] ?? '');
        $audience = (string)($claims['aud'] ?? '');
        unset($claims['iat'], $claims['nbf'], $claims['exp'], $claims['jti']);
        return self::encode($claims, $ttl, $subject, $audience !== '' ? $audience : null);
    }

    public static function bearer(string $value): string
    {
        $token = trim($value);
        if (stripos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        return trim($token, " \t\n\r\0\x0B\"'");
    }

    private static function config(): Configuration
    {
        $secret = (string)self::env('JWT.SECRET', 'changeme');
        return Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($secret));
    }

    private static function env(string $key, $default = null)
    {
        if (function_exists('env')) {
            return env($key, $default);
        }
        $alt = str_replace('.', '_', $key);
        $v = getenv($alt);
        if ($v === false && isset($_ENV[$alt])) {
            $v = $_ENV[$alt];
        }
        return $v !== false ? $v : $default;
    }
}

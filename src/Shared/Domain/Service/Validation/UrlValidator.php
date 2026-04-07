<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\Validation\InvalidUrlFormatException;

final class UrlValidator
{
    public const array ONLY_HTTP = ['http', 'https'];

    /**
     * @param string[] $allowedSchemes
     *
     * @throws InvalidUrlFormatException
     */
    public static function normalize(string $url, array $allowedSchemes = []): string
    {
        $url = mb_trim($url);

        if (preg_match('/[^\x00-\x7f]/', $url)) {
            $url = self::encodeIdn($url);
        }

        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw InvalidUrlFormatException::becauseItIsNotAValidUrl($url);
        }

        $parts = self::parse($url);

        $scheme = mb_strtolower($parts['scheme'] ?? '');
        if (!empty($allowedSchemes) && !in_array($scheme, $allowedSchemes, true)) {
            throw InvalidUrlFormatException::becauseSchemeIsNotAllowed($scheme, $allowedSchemes);
        }

        return self::buildUrl($parts);
    }

    /**
     * @throws InvalidUrlFormatException
     */
    private static function encodeIdn(string $url): string
    {
        $parts = self::parse($url);

        $host = $parts['host'] ?? throw InvalidUrlFormatException::becauseItIsNotAValidUrl($url);

        $idnHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if (false === $idnHost) {
            return $url;
        }

        $parts['host'] = $idnHost;

        return self::buildUrl($parts);
    }

    /**
     * @return array{
     *     scheme?: string,
     *     user?: string,
     *     pass?: string,
     *     host?: string,
     *     port?: int,
     *     path?: string,
     *     query?: string,
     *     fragment?: string
     * }
     *
     * @throws InvalidUrlFormatException
     */
    private static function parse(string $url): array
    {
        $parts = parse_url($url);

        if (false === $parts || !isset($parts['host'])) {
            throw InvalidUrlFormatException::becauseItIsNotAValidUrl($url);
        }

        /*
         * @var array{
         *     scheme?: string,
         *     user?: string,
         *     pass?: string,
         *     host?: string,
         *     port?: int,
         *     path?: string,
         *     query?: string,
         *     fragment?: string
         * } $parts
         */
        return $parts;
    }

    /**
     * @param array{
     *     scheme?: string,
     *     user?: string,
     *     pass?: string,
     *     host?: string,
     *     port?: int,
     *     path?: string,
     *     query?: string,
     *     fragment?: string
     * } $parts
     */
    private static function buildUrl(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? mb_strtolower($parts['scheme']).'://' : 'https://';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':'.$parts['pass'] : '';
        $auth = ($user || $pass) ? "{$user}{$pass}@" : '';
        $host = isset($parts['host']) ? mb_strtolower($parts['host']) : '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        if (empty($path) && ($query || $fragment)) {
            $path = '/';
        }

        return "{$scheme}{$auth}{$host}{$port}{$path}{$query}{$fragment}";
    }
}

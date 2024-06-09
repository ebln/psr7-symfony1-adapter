<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response;

use brnc\Symfony1\Message\Factory\NowFactory;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Applies `Set-Cookie` headers from PSR7 to  `sfWebResponse` via `setCookie`
 *
 * SameSite attribute is lost, as `sfWebResponse` doesn't support it.
 */
class CookieHeaderTranscriptor implements CookieTranscriptorInterface
{
    /** @var array<string, null|bool|string> */
    private const ATTRIBUTES = [
        'expires'  => '',
        'max-age'  => '',
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => false,
        'samesite' => 'Lax', // Default to Lax if not specified
        'ttl'      => null,
    ];
    private ClockInterface $nowFactory;

    public function __construct(?ClockInterface $nowFactory = null)
    {
        $this->nowFactory = $nowFactory ?? new NowFactory();
    }

    public function transcribeCookies(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void
    {
        $cookies = $psrResponse->getHeader('Set-Cookie');

        foreach ($cookies as $cookie) {
            $parts          = explode(';', $cookie);
            $cookieData     = array_shift($parts);
            [$name, $value] = [...explode('=', $cookieData, 2), ''];
            /** @var array{expires: string, max-age: string, path: string, domain: string, secure: bool, httponly: bool, samesite:string, ttl: null|int} $attributes */
            $attributes = self::ATTRIBUTES;

            foreach ($parts as $part) {
                $part                   = trim($part);
                [$attrName, $attrValue] = [...explode('=', $part, 2), ''];
                /** @noinspection PhpStrictTypeCheckingInspection */
                $attrName = strtolower($attrName);
                if (array_key_exists($attrName, self::ATTRIBUTES)) {
                    if ('secure' === $attrName || 'httponly' === $attrName) {
                        $attributes[$attrName] = ('' === $attrValue);
                    } else {
                        $attributes[$attrName] = $attrValue;
                    }
                }
            }

            // Calculate expiration from Max-Age if present
            if ('' !== $attributes['max-age']) {
                $maxAgeTime = $this->nowFactory->now()->getTimestamp() + (int)$attributes['max-age'];
                if ('' === $attributes['expires'] || $maxAgeTime < strtotime($attributes['expires'])) {
                    $attributes['ttl'] = $maxAgeTime;
                }
            }

            if ('' !== $attributes['expires'] && !is_numeric($attributes['ttl'])) {
                $epoch             = strtotime($attributes['expires']);
                $attributes['ttl'] = false === $epoch ? null : $epoch;
            }

            // Apply the cookie using your internal function
            $sfWebResponse->setCookie(
                $name,
                $value,
                $attributes['ttl'],
                $attributes['path'],
                $attributes['domain'],
                $attributes['secure'],
                $attributes['httponly']
            );
        }
    }
}

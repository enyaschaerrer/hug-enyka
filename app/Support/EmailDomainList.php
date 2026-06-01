<?php

namespace App\Support;

class EmailDomainList
{
    /**
     * @return array<int, string>
     */
    public static function parse(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn (string $domain) => self::normalize($domain))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function normalize(string $domain): string
    {
        return strtolower(trim($domain, " \t\n\r\0\x0B@."));
    }

    public static function isValidDomain(string $domain): bool
    {
        return preg_match('/^(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,63}$/', self::normalize($domain)) === 1;
    }

    public static function firstEmailExample(?string $value): string
    {
        $domain = self::parse($value)[0] ?? 'entreprise.ch';

        return 'exemple@' . $domain;
    }

    public static function emailMatches(string $email, ?string $allowedDomains): bool
    {
        $emailDomain = strtolower((string) str($email)->afterLast('@'));

        if ($emailDomain === '') {
            return false;
        }

        return in_array($emailDomain, self::parse($allowedDomains), true);
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

class LinkedInProfileUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Enter a valid LinkedIn profile URL.');

            return;
        }

        $host = Str::lower((string) parse_url($value, PHP_URL_HOST));
        $path = (string) parse_url($value, PHP_URL_PATH);
        $isLinkedInHost = $host === 'linkedin.com' || Str::endsWith($host, '.linkedin.com');
        $isProfilePath = preg_match('#^/(?:in/[^/]+|pub/.+)/?$#i', $path) === 1;

        if (! $isLinkedInHost || ! $isProfilePath || parse_url($value, PHP_URL_PORT) !== null) {
            $fail('Enter a LinkedIn profile URL such as https://www.linkedin.com/in/your-name.');
        }
    }

    public static function prepare(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $value = trim($value);

        return Str::startsWith(Str::lower($value), ['http://', 'https://']) ? $value : 'https://'.$value;
    }

    public static function canonicalize(string $value): string
    {
        $host = Str::lower((string) parse_url($value, PHP_URL_HOST));
        $path = (string) parse_url($value, PHP_URL_PATH);

        return 'https://'.($host === 'linkedin.com' ? 'www.linkedin.com' : $host).'/'.ltrim($path, '/');
    }
}

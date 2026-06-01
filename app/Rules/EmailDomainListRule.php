<?php

namespace App\Rules;

use App\Support\EmailDomainList;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailDomainListRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domains = EmailDomainList::parse(is_string($value) ? $value : null);

        if ($domains === []) {
            $fail('Renseigne au moins un domaine email autorisé.');
            return;
        }

        foreach ($domains as $domain) {
            if (! EmailDomainList::isValidDomain($domain)) {
                $fail('Le domaine "' . $domain . '" n\'est pas valide.');
                return;
            }
        }
    }
}

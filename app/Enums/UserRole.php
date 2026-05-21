<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case User = 'user';

    /**
     * @param  array<int, string>  $roles
     */
    public function isOneOf(array $roles): bool
    {
        return in_array($this->value, $roles, true);
    }
}

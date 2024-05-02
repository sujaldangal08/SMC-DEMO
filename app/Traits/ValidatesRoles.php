<?php

namespace App\Traits;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait ValidatesRoles
{
    protected function roleRule($roleName): Exists
    {
        return Rule::exists('users', 'id')->where(function ($query) use ($roleName) {
            $query->whereIn('role_id', function ($query) use ($roleName) {
                $query->select('id')->from('roles')->where('role', $roleName);
            });
        });
    }
}

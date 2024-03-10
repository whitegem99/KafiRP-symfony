<?php declare(strict_types=1);

namespace App\Helper;

use App\Enum\RoleType;

class RoleTypeHelper
{
    public function map(int $roleType): string
    {
        $roleText = '';
        switch ($roleType) {
            case RoleType::ROLE_1:
                $roleText = 'Role 1';
                break;
            case RoleType::ROLE_2:
                $roleText = 'Role 2';
                break;
            case RoleType::ROLE_3:
                $roleText = 'Role 3';
                break;
            case RoleType::ROLE_4:
                $roleText = 'Role 4';
                break;
            case RoleType::ROLE_5:
                $roleText = 'Role 5';
                break;
        }
        return $roleText;
    }
}
<?php

namespace App\Enum;

enum UserRolesEnum:string
{
    //
    case TENANT_ADMIN ='tenant_admin';
    case PROJECT_MANAGER ='project_manager';

    public static function values():array
    {
        return array_map(fn($roles)=>$roles->value, self::cases());
    } 
}

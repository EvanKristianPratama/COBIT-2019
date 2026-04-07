<?php

namespace App\Support\Authorization;

final class PermissionCatalog
{
    public const UsersManage = 'users.manage';
    public const RolesManage = 'roles.manage';
    public const CobitView = 'cobit.view';
    public const DesignFactorsView = 'design-factors.view';
    public const DesignFactorsInput = 'design-factors.input';
    public const AssessmentsView = 'assessments.view';
    public const AssessmentsInput = 'assessments.input';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::UsersManage,
            self::RolesManage,
            self::CobitView,
            self::DesignFactorsView,
            self::DesignFactorsInput,
            self::AssessmentsView,
            self::AssessmentsInput,
        ];
    }

    public static function label(string $permission): string
    {
        return self::labels()[$permission] ?? $permission;
    }

    /**
     * @return list<string>
     */
    public static function profileAssignable(): array
    {
        return [
            self::CobitView,
            self::DesignFactorsView,
            self::DesignFactorsInput,
            self::AssessmentsView,
            self::AssessmentsInput,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::UsersManage => 'Kelola User',
            self::RolesManage => 'Kelola Role & Akses',
            self::CobitView => 'Lihat COBIT Component',
            self::DesignFactorsView => 'Lihat Design Factor',
            self::DesignFactorsInput => 'Input Design Factor',
            self::AssessmentsView => 'Lihat Assessment',
            self::AssessmentsInput => 'Input Assessment',
        ];
    }
}

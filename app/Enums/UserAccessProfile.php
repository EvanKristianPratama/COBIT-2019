<?php

namespace App\Enums;

use App\Support\Authorization\PermissionCatalog;

enum UserAccessProfile: string
{
    case Viewer = 'viewer';
    case DesignFactorEditor = 'df_editor';
    case Assessor = 'assessor';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $profile): string => $profile->value,
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Viewer => 'Auditee',
            self::DesignFactorEditor => 'Client DF Editor',
            self::Assessor => 'Assessor',
        };
    }

    /**
     * @return list<string>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::Viewer => [
                PermissionCatalog::CobitView,
                PermissionCatalog::DesignFactorsView,
                PermissionCatalog::AssessmentsView,
            ],
            self::DesignFactorEditor => [
                PermissionCatalog::CobitView,
                PermissionCatalog::DesignFactorsView,
                PermissionCatalog::DesignFactorsInput,
                PermissionCatalog::AssessmentsView,
            ],
            self::Assessor => [
                PermissionCatalog::CobitView,
                PermissionCatalog::DesignFactorsView,
                PermissionCatalog::DesignFactorsInput,
                PermissionCatalog::AssessmentsView,
                PermissionCatalog::AssessmentsInput,
            ],
        };
    }

    /**
     * @return list<string>
     */
    public function permissions(): array
    {
        return $this->defaultPermissions();
    }

    public function canInputDesignFactors(): bool
    {
        return in_array(PermissionCatalog::DesignFactorsInput, $this->defaultPermissions(), true);
    }

    public function canInputAssessments(): bool
    {
        return in_array(PermissionCatalog::AssessmentsInput, $this->defaultPermissions(), true);
    }
}

<?php

namespace App\Providers;

use App\Enums\UserAccessProfile;
use App\Models\Assessment;
use App\Models\MstEval;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Run before Spatie's callback so older write grants cannot override auditee access.
        $this->app->booting(function (): void {
            Gate::before(function (User $user, string $ability): ?bool {
                if (! in_array($ability, [PermissionCatalog::AssessmentsView, PermissionCatalog::AssessmentsInput], true)
                    || $user->accessProfileEnum() !== UserAccessProfile::Viewer
                    || $user->isAdmin()) {
                    return null;
                }

                return $ability === PermissionCatalog::AssessmentsView && ! $user->requiresAdminApproval();
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'assessment' => Assessment::class,
            'evaluation' => MstEval::class,
        ]);
    }
}

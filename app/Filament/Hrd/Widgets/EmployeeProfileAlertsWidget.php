<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\User;
use App\Models\EmployeeDocument;
use Filament\Widgets\Widget;

class EmployeeProfileAlertsWidget extends Widget
{
    protected static string $view = 'filament.hrd.widgets.employee-profile-alerts';
    
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'incompleteProfiles' => User::withIncompleteProfile()->limit(5)->get(),
            'unverifiedDocuments' => EmployeeDocument::unverified()->with('user')->limit(5)->get(),
            'newEmployees' => User::where('created_at', '>=', now()->subDays(7))->get(),
        ];
    }
}

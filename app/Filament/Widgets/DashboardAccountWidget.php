<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget;

class DashboardAccountWidget extends AccountWidget
{
    protected static ?int $sort = -4;

    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 2];
}

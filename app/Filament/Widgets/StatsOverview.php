<?php

namespace App\Filament\Widgets;

use App\Models\JumpHost;
use App\Models\Key;
use App\Models\Server;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Keys', Key::count())
                ->description('Total keys managed by the system.')
                ->url('/admin/keys')
                ->icon('heroicon-o-key'),
            Stat::make('Servers', Server::count())
                ->description('Total servers managed by the system.')
                ->url('/admin/servers')
                ->icon('heroicon-o-server'),
            Stat::make('Jump Hosts', JumpHost::count())
                ->url('/admin/jump-hosts')
                ->description('Total jump hosts managed by the system.')
                ->icon('heroicon-o-forward'),
        ];
    }
}

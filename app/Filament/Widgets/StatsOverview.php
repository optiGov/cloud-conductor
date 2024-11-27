<?php

namespace App\Filament\Widgets;

use App\Models\CronJob;
use App\Models\DockerContainer;
use App\Models\IPSecTunnel;
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
            Stat::make('Servers', Server::count())
                ->description('Total servers managed by the system.')
                ->url('/admin/servers')
                ->icon('heroicon-o-server'),
            Stat::make('Jump Hosts', JumpHost::count())
                ->url('/admin/jump-hosts')
                ->description('Total jump hosts managed by the system.')
                ->icon('heroicon-o-forward'),
            Stat::make('IPSec-Tunnels', IPSecTunnel::count())
                ->description('Total IPSec tunnels managed by the system.')
                ->url('/admin/server')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Containers', DockerContainer::count())
                ->description('Total docker containers managed by the system.')
                ->url('/admin/server')
                ->icon('heroicon-o-cube'),
            Stat::make('Networks', DockerContainer::count())
                ->description('Total docker networks managed by the system.')
                ->url('/admin/server')
                ->icon('heroicon-o-globe-alt'),
            Stat::make('Cron Jobs', CronJob::count())
                ->description('Total cron jobs managed by the system.')
                ->url('/admin/server')
                ->icon('heroicon-o-clock'),
        ];
    }
}

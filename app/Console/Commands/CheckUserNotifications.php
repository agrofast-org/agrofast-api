<?php

namespace App\Console\Commands;

use App\Models\Hr\User;
use App\Services\System\NotificationRuleService;
use Illuminate\Console\Command;

class CheckUserNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all()->shuffle();

        $service = new NotificationRuleService();

        foreach ($users as $user) {
            $service->checkAndNotify($user);
        }

        $this->info('Notificações verificadas com sucesso.');
    }
}

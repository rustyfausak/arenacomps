<?php

namespace App\Console\Commands;

use App\BattleNetApi;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class GetLeaderboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'leaderboard:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the PvP leaderboard data.';

    /**
     * The BattleNetApi service.
     *
     * @var BattleNetApi
     */
    protected $battle_net_api;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BattleNetApi $battle_net_api)
    {
        parent::__construct();

        $this->battle_net_api = $battle_net_api;
    }

    /**
     * Specify the command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['region', 'r', InputOption::VALUE_OPTIONAL, 'The leaderboard region.', 'us'],
            ['bracket', 'b', InputOption::VALUE_OPTIONAL, 'The leaderboard bracket.', '3v3'],
            ['locale', 'l', InputOption::VALUE_OPTIONAL, 'The locale for the response. Defaults to english.', null],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $response = $this->battle_net_api->getPvpLeaderboard(
                $this->option('region'),
                $this->option('bracket'),
                $this->option('locale')
            );
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

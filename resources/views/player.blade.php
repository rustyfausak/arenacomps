@extends('templates.base')

@section('page-title', $player->name)

@section('content')
    <div class="row">
        <div class="col-sm-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Character
                </div>
                <table class="table table-condensed">
                    <tbody>
                        <tr>
                            <th>Armory</th>
                            <td>
                                <a href="http://{{ $player->realm->region->name }}.battle.net/wow/en/character/{{ $player->realm->slug }}/{{ $player->name }}/simple" target="_blank">
                                    Armory
                                    @include('icons.ext-link')
                                </a>
                            </td>
                        </tr>
                        <tr><th>Region</th><td>{{ $player->realm->region->name }}</td></tr>
                        <tr><th>Realm</th><td>{{ $player->realm->name }}</td></tr>
                        <tr><th>Faction</th><td>{{ $player->faction->name }}</td></tr>
                        <tr>
                            <th>Race</th>
                            <td>
                                @include('snippets.race', [
                                    'race' => $player->race->getName(),
                                    'gender' => $player->gender->name
                                ])
                            </td>
                        </tr>
                        <tr>
                            <th>Class/Spec</th>
                            <td>
                                @include('snippets.role-spec', [
                                    'role' => $player->role->name,
                                    'spec' => $player->spec->name
                                ])
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @if ($stat)
            <div class="col-sm-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Stats
                    </div>
                    <table class="table table-condensed">
                        <tbody>
                            <tr><th>Ranking</th><td>{{ $stat->ranking }}</td></tr>
                            <tr><th>Rating</th><td>{{ $stat->rating }}</td></tr>
                            <tr><th>W</th><td>{{ $stat->season_wins }}</td></tr>
                            <tr><th>L</th><td>{{ $stat->season_losses }}</td></tr>
                            <tr><th>Week W</th><td>{{ $stat->weekly_wins }}</td></tr>
                            <tr><th>Week L</th><td>{{ $stat->weekly_losses }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @if ($om->region && $player->realm->region->id != $om->region->id)
        <div class="alert alert-warning">
            No player data for this region.
        </div>
    @else
        <div class="panel panel-default">
            <div class="panel-heading">
                Teams
            </div>
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th colspan="3">Players</th>
                        <th colspan="3">Comp</th>
                        <th>W</th>
                        <th>L</th>
                        <th>Comp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teams as $team)
                        <?php
                        $comps = $team->getComps();
                        $remove = [];
                        $team_om = $om;
                        $team_om->team = $team;
                        foreach ($comps as $k => $comp) {
                            $comp_performance = $comp->getPerformance($team_om);
                            if ($comp_performance->numGames() < config('app.min_games')) {
                                unset($comps[$k]);
                            }
                        }
                        $num_comps = sizeof($comps);
                        if (!$num_comps) {
                            continue;
                        }
                        $comp = $comps->shift();
                        $comp_performance = $comp->getPerformance($team_om);
                        ?>
                        <tr>
                            <td><a href="{{ route('player', $team->player_id1) }}">{{ $team->player1->name }}</a></td>
                            <td><a href="{{ route('player', $team->player_id2) }}">{{ $team->player2->name }}</a></td>
                            <td><a href="{{ route('player', $team->player_id3) }}">{{ $team->player3->name }}</a></td>
                            @foreach (App\Models\Spec::sort($comp->getSpecs()) as $spec)
                                <td>
                                    @include('snippets.role-spec', [
                                        'role' => $spec->role->name,
                                        'spec' => $spec->name
                                    ])
                                </td>
                            @endforeach
                            <td>{{ $comp_performance->wins }}</td>
                            <td>{{ $comp_performance->losses }}</td>
                            <td><a href="{{ route('comp', $comp->id) }}">view</a></a>
                        </tr>
                        @foreach ($comps as $comp)
                            <?php
                            $comp_performance = $comp->getPerformance($team_om);
                            ?>
                            <tr>
                                <td colspan="3"></td>
                                @foreach (App\Models\Spec::sort($comp->getSpecs()) as $spec)
                                    <td>
                                        @include('snippets.role-spec', [
                                            'role' => $spec->role->name,
                                            'spec' => $spec->name
                                        ])
                                    </td>
                                @endforeach
                                <td>{{ $comp_performance->wins }}</td>
                                <td>{{ $comp_performance->losses }}</td>
                                <td><a href="{{ route('comp', $comp->id) }}">view</a></a>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                Activity
            </div>
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Spec</th>
                        <th class="text-right">Rating</th>
                        <th class="text-right">Wins</th>
                        <th class="text-right">Losses</th>
                        <th class="text-right">Team</th>
                        <th class="text-right">Comp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($snapshots as $snapshot)
                        <tr>
                            <td><a href="{{ route('activity', $snapshot->group->leaderboard->id) }}">{{ $snapshot->group->leaderboard->completed_at }}</a></td>
                            <td>
                                @include('snippets.spec', [
                                    'role' => $snapshot->spec->role->name,
                                    'spec' => $snapshot->spec->name
                                ])
                            </td>
                            <td class="text-right">{{ $snapshot->rating }}</td>
                            <td class="text-right">{{ $snapshot->group->wins }}</td>
                            <td class="text-right">{{ $snapshot->group->losses }}</td>
                            <td class="text-right">
                                @if ($snapshot->team)
                                    {{ implode('/', $snapshot->team->getPlayers()) }}
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($snapshot->comp)
                                    {{ implode('/', $snapshot->comp->getSpecs()) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="panel-footer">
                {{ $snapshots->links() }}
            </div>
        </div>
    @endif
@endsection

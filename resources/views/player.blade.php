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
                        <tr><th>Region</th><td>{{ $player->realm->region->name }}</td></tr>
                        <tr><th>Realm</th><td>{{ $player->realm->name }}</td></tr>
                        <tr><th>Faction</th><td>{{ $player->faction->name }}</td></tr>
                        <tr>
                            <th>Race</th>
                            <td>
                                @include('snippets.race', [
                                    'race' => $player->race->name ,
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

    @if ($region && $player->realm->region->id != $region->id)
        <div class="alert alert-warning">
            Player from different region
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
                        <th>Rating</th>
                        <th>W</th>
                        <th>L</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($player->getTeams($bracket) as $team)
                        <?php
                        $performance = $team->getPerformance($season, $region, null, $term);
                        $comps = $team->getComps();
                        $num_comps = sizeof($comps);
                        $comp = $comps->shift();
                        $comp_performance = $comp->getPerformance($season, $region, $team, $term);
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
                            <td>{{ $comp_performance->avg_rating }}</td>
                            <td>{{ $comp_performance->wins }}</td>
                            <td>{{ $comp_performance->losses }}</td>
                        </tr>
                        @foreach ($comps as $comp)
                            <?php
                            $comp_performance = $comp->getPerformance($season, $region, $team, $term);
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
                                <td>{{ $comp_performance->avg_rating }}</td>
                                <td>{{ $comp_performance->wins }}</td>
                                <td>{{ $comp_performance->losses }}</td>
                            </tr>
                        @endforeach
                        @if ($num_comps > 1)
                            <tr>
                                <td colspan="6" class="text-right">Total</td>
                                <td>{{ $performance->avg_rating }}</td>
                                <td>{{ $performance->wins }}</td>
                                <td>{{ $performance->losses }}</td>
                            </tr>
                        @endif
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
                        <th>Rating</th>
                        <th>Wins</th>
                        <th>Losses</th>
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
                            <td>{{ $snapshot->rating }}</td>
                            <td>{{ $snapshot->group->wins }}</td>
                            <td>{{ $snapshot->group->losses }}</td>
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

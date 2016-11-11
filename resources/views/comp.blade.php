@extends('templates.base')

@section('page-title', implode(' / ', $specs))

@section('content')
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Comp
                </div>
                <div class="panel-body">
                    @foreach ($specs as $spec)
                        <div class="group-bubble">
                            @include('snippets.role-spec', [
                                'role' => $spec->role->name,
                                'spec' => $spec->name
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Stats
                </div>
                <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        <tr>
                            <th>Avg Rating</th>
                            <td>{{ $performance->avg_rating }}</td>
                        </tr>
                        <tr>
                            <th>Wins</th>
                            <td>{{ $performance->wins }}</td>
                        </tr>
                        <tr>
                            <th>Losses</th>
                            <td>{{ $performance->losses }}</td>
                        </tr>
                        <tr>
                            <th>W/L Ratio</th>
                            <td>{{ round($performance->wins / max(1, $performance->losses), 2) }}</td>
                        </tr>
                        <tr>
                            <th># Teams</th>
                            <td>{{ $performance->num_teams }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $performance->updated_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            Teams
        </div>
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th colspan="{{ sizeof($specs) }}">Players</th>
                    <th>Avg Rating</th>
                    <th>W</th>
                    <th>L</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($teams as $team)
                    <tr>
                        @foreach ($team->players as $player)
                            <td>
                                <a href="{{ route('player', $player->id) }}">
                                    @include('snippets.role-text', [
                                        'role' => $player->role->name,
                                        'text' => $player->name
                                    ])
                                </a>
                            </td>
                        @endforeach
                        <td>{{ $team->performance->avg_rating }}</td>
                        <td>{{ $team->performance->wins }}</td>
                        <td>{{ $team->performance->losses }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-footer">
            {{ $teams->links() }}
        </div>
    </div>
@endsection

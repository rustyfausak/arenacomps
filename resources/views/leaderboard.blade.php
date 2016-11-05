@extends('templates.base')

@section('content')
    <h1>Leaderboard</h1>

    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>Player</th>
                <th>Region</th>
                <th>Realm</th>
                <th>Faction</th>
                <th>Race</th>
                <th>Class</th>
                <th>Spec</th>
                <th>Gender</th>
                <th>Rating</th>
                <th>Ranking</th>
                <th>W</th>
                <th>L</th>
                <th>Week W</th>
                <th>Week L</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stats as $stat)
                <tr>
                    <td><a href="{{ route('player', $stat->player->id) }}">{{ $stat->player->name }}</a></td>
                    <td>{{ $stat->player->realm->region->name }}</td>
                    <td>{{ $stat->player->realm->name }}</td>
                    <td>{{ $stat->player->faction->name }}</td>
                    <td>{{ $stat->player->race->name }}</td>
                    <td>{{ $stat->player->role->name }}</td>
                    <td>{{ $stat->player->spec->name }}</td>
                    <td>{{ $stat->player->gender->name }}</td>
                    <td>{{ $stat->rating }}</td>
                    <td>{{ $stat->ranking }}</td>
                    <td>{{ $stat->season_wins }}</td>
                    <td>{{ $stat->season_losses }}</td>
                    <td>{{ $stat->weekly_wins }}</td>
                    <td>{{ $stat->weekly_losses }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $stats->links() }}
@endsection

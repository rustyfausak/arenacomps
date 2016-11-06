@extends('templates.base')

@section('page-title')
    Activity
    <small>
        @if ($leaderboard)
            Showing activity for timestamp {{ $leaderboard->completed_at }}. <a href="{{ route('activity') }}">show all</a>
        @else
            All activity
        @endif
    </small>
@endsection

@section('content')
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Player</th>
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
                    <td><a href="{{ route('player', $snapshot->player_id) }}">{{ $snapshot->player->name }}</a></td>
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
    {{ $snapshots->links() }}
@endsection

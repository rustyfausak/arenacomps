@extends('templates.base')

@section('page-title')
    Activity
    <small>
        @if ($leaderboard)
            Showing activity for update #{{ $leaderboard->id }} at {{ $leaderboard->completed_at }}.
            <a href="{{ route('activity') }}">show all</a>
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
                    <td>
                        <a href="{{ route('activity', $snapshot->group->leaderboard->id) }}">
                            {{ $snapshot->group->leaderboard->completed_at }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('player', $snapshot->player_id) }}">
                            {{ $snapshot->player->name }}
                        </a>
                     </td>
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
    {{ $snapshots->links() }}
@endsection

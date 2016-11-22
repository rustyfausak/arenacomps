@extends('templates.base')

@section('page-title', 'Leaderboard')

@section('page-header-bar')
    <hr>
    <form action="" method="get" class="form-inline">
        <div class="form-group">
            <label for="class">Class</label>
            <select name="class" id="class" class="form-control" data-submit-on-change>
                <option value="any">Any</option>
                @foreach (App\Models\Role::all() as $_role)
                    <option class="color-{{ strtolower(str_replace(' ', '', $_role->name)) }}" value="{{ $_role->id }}" {{ $role && $_role->id == $role->id ? 'selected="selected"' : '' }}>{{ $_role->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
@endsection

@section('content')
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th colspan="2">Player</th>
                <th>Realm</th>
                <th>Race</th>
                <th>Class/Spec</th>
                <th class="text-right">Rating</th>
                <th class="text-right">Ranking</th>
                <th class="text-right">W</th>
                <th class="text-right">L</th>
                <th class="text-right">Week W</th>
                <th class="text-right">Week L</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stats as $stat)
                <tr>
                    <td>
                        <a href="{{ route('player', $stat->player->id) }}">{{ $stat->player->name }}</a>
                    </td>
                    <td class="text-center">
                        <a href="http://{{ $stat->player->realm->region->name }}.battle.net/wow/en/character/{{ $stat->player->realm->slug }}/{{ $stat->player->name }}/simple" target="_blank">
                            @include('icons.armory')
                        </a>
                    </td>
                    <td>
                        @include('icons.region', ['region' => $stat->player->realm->region->name])
                        {{ $stat->player->realm->name }}
                    </td>
                    <td>
                        @include('snippets.race', [
                            'race' => $stat->player->race->getName(),
                            'gender' => $stat->player->gender->name
                        ])
                    </td>
                    <td>
                        @include('snippets.role-spec', [
                            'role' => $stat->player->role->name,
                            'spec' => $stat->player->spec->name
                        ])
                    </td>
                    <td class="text-right">{{ $stat->rating }}</td>
                    <td class="text-right">{{ $stat->ranking }}</td>
                    <td class="text-right">{{ $stat->season_wins }}</td>
                    <td class="text-right">{{ $stat->season_losses }}</td>
                    <td class="text-right">{{ $stat->weekly_wins }}</td>
                    <td class="text-right">{{ $stat->weekly_losses }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $stats->appends(['class' => $role ? $role->id : ''])->links() }}
@endsection

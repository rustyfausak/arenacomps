@extends('templates.base')

@section('page-title', 'Comps')

@section('page-header-bar')
    <hr>
    <form action="" method="get" class="form-inline">
        @for ($i = 1; $i <= $bracket_size; $i++)
            <div class="form-group group-bubble">
                <label>{{ $i }}</label>
                <select name="class{{ $i }}" class="form-control" data-waterfall-to="#specs{{ $i }}">
                    <option value="any">Any</option>
                    @foreach (App\Models\Role::all() as $_role)
                        <option data-waterfall-value="{{ $_role->id }}" class="color-{{ strtolower(str_replace(' ', '', $_role->name)) }}" value="{{ $_role->id }}" {{ $roles[$i - 1] && $_role->id == $roles[$i - 1]->id ? 'selected="selected"' : '' }}>{{ $_role->name }}</option>
                    @endforeach
                </select>
                <select name="spec{{ $i }}" class="form-control" id="specs{{ $i }}">
                    <option value="any">Any</option>
                    @foreach (App\Models\Spec::all() as $_spec)
                        <option data-waterfall-value="{{ $_spec->role->id }}" class="color-{{ strtolower(str_replace(' ', '', $_spec->role->name)) }}" value="{{ $_spec->id }}" {{ $specs[$i - 1] && $_spec->id == $specs[$i - 1]->id ? 'selected="selected"' : '' }}>{{ $_spec->name }}</option>
                    @endforeach
                </select>
            </div>
        @endfor
        <div class="form-group form-group-bubble">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
@endsection

@section('content')
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th colspan="3">Comp</th>
                <th class="text-right"><a href="?s=avg_rating&d={{ !$sort_dir }}">Avg Rating</a></th>
                <th class="text-right"><a href="?s=wins&d={{ !$sort_dir }}">W</a></th>
                <th class="text-right"><a href="?s=losses&d={{ !$sort_dir }}">L</a></th>
                <th class="text-right"><a href="?s=ratio&d={{ !$sort_dir }}">W/L Ratio</a></th>
                <th class="text-right">Teams</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($performances as $performance)
                <tr>
                    @foreach (App\Models\Spec::sort($performance->comp->getSpecs()) as $spec)
                        <td>
                            @include('snippets.role-spec', [
                                'role' => $spec->role->name,
                                'spec' => $spec->name
                            ])
                        </td>
                    @endforeach
                    <td class="text-right">{{ $performance->avg_rating }}</td>
                    <td class="text-right">{{ $performance->wins }}</td>
                    <td class="text-right">{{ $performance->losses }}</td>
                    <td class="text-right">{{ round($performance->wins / max(1, $performance->losses),2) }}</td>
                    <td class="text-right"><a href="{{ route('comp', $performance->comp->id) }}">{{ $performance->comp->numTeams() }}</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $performances->links() }}
@endsection

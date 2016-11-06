@extends('templates.base')

@section('page-title', 'Comps')

@section('page-header-bar')
    <hr>
    <form action="" method="get" class="form-inline">
        @for ($i = 1; $i <= $bracket_size; $i++)
            <div class="form-group form-group-bubble">
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
                <th>Power</th>
                <th>Avg Rating</th>
                <th>W</th>
                <th>L</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($performances as $performance)
                <tr>
                    @foreach (App\Models\Spec::sort([
                        $performance->comp->spec1,
                        $performance->comp->spec2,
                        $performance->comp->spec3,
                    ]) as $spec)
                        <td>
                            @include('snippets.role-spec', [
                                'role' => $spec->role->name,
                                'spec' => $spec->name
                            ])
                        </td>
                    @endforeach
                    <td>{{ $performance->skill }}</td>
                    <td>{{ $performance->avg_rating }}</td>
                    <td>{{ $performance->wins }}</td>
                    <td>{{ $performance->losses }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $performances->links() }}
@endsection

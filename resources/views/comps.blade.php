@extends('templates.base')

@section('page-title', 'Comps')

@section('content')
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th colspan="3">Comp</th>
                <th>OP Value</th>
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

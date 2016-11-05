@extends('templates.base')

@section('content')
    <h1>Comps</h1>

    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>ID</th>
                <th>Classes</th>
                <th>Specs</th>
                <th>OP</th>
                <th>Avg Rating</th>
                <th>W</th>
                <th>L</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($performances as $performance)
                <tr>
                    <td>{{ $performance->comp->id }}</td>
                    <td>
                        @include('icons.role', ['role' => $performance->comp->spec1->role->name])
                        @include('icons.role', ['role' => $performance->comp->spec2->role->name])
                        @include('icons.role', ['role' => $performance->comp->spec3->role->name])
                    </td>
                    <td>
                        @include('icons.spec', [
                            'role' => $performance->comp->spec1->role->name,
                            'spec' => $performance->comp->spec1->name
                        ])
                        @include('icons.spec', [
                            'role' => $performance->comp->spec2->role->name,
                            'spec' => $performance->comp->spec2->name
                        ])
                        @include('icons.spec', [
                            'role' => $performance->comp->spec3->role->name,
                            'spec' => $performance->comp->spec3->name
                        ])
                    </td>
                    <td>{{ $performance->skill }}</td>
                    <td>{{ $performance->avg_rating }}</td>
                    <td>{{ $performance->wins }}</td>
                    <td>{{ $performance->losses }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

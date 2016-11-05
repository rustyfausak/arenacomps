@extends('templates.base')

@section('content')
    <h1>Comps</h1>

    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>ID</th>
                <th colspan="3">Specs</th>
                <th>Rating</th>
                <th>W</th>
                <th>L</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comps as $comp)
                <?php
                $performance = $comp->getPerformance($season);
                ?>
                <tr>
                    <td>{{ $comp->id }}</td>
                    <td>{{ $comp->spec1->name }} {{ $comp->spec1->role->name }}</td>
                    <td>{{ $comp->spec2->name }} {{ $comp->spec2->role->name }}</td>
                    <td>{{ $comp->spec3->name }} {{ $comp->spec3->role->name }}</td>
                    <td>{{ $performance->avg_rating }}</td>
                    <td>{{ $performance->wins }}</td>
                    <td>{{ $performance->losses }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

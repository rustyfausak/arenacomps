@extends('templates.base')

@section('content')
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    Brackets
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Bracket::all() as $bracket)
                            <tr>
                                <td>{{ $bracket->id }}</td>
                                <td>{{ $bracket->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    Regions
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Region::all() as $region)
                            <tr>
                                <td>{{ $region->id }}</td>
                                <td>{{ $region->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    Factions
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>GameID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Faction::all() as $faction)
                            <tr>
                                <td>{{ $faction->id }}</td>
                                <td>{{ $faction->name }}</td>
                                <td>{{ $faction->id_game }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

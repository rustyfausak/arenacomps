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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Faction::all() as $faction)
                            <tr>
                                <td>{{ $faction->id }}</td>
                                <td>{{ $faction->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    Roles
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Role::all() as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    Races
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Faction</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Race::all() as $race)
                            <tr>
                                <td>{{ $race->id }}</td>
                                <td>{{ $race->name }}</td>
                                <td>{{ $race->faction_id }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    Specs
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Spec::all() as $spec)
                            <tr>
                                <td>{{ $spec->id }}</td>
                                <td>{{ $spec->name }}</td>
                                <td>{{ $spec->role_id }}</td>
                                <td>{{ $spec->spec_type_id }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    Realms
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Region</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (App\Models\Realm::all() as $realm)
                            <tr>
                                <td>{{ $realm->id }}</td>
                                <td>{{ $realm->name }}</td>
                                <td>{{ $realm->slug }}</td>
                                <td>{{ $realm->region_id }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

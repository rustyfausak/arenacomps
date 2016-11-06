@extends('templates.base')

@section('page-title', 'Stats')

@section('content')
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Classes
                </div>
                <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        @foreach ($role_data as $row)
                            <tr>
                                <td class="text-right">{{ $row->ranking }}</td>
                                <td>
                                    @include('snippets.role', ['role' => $row->role_name])
                                </td>
                                <td class="text-right">{{ sprintf("%01.1f", $row->pct) }}%</td>
                                <td class="text-right">{{ $row->num }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Specs
                </div>
                <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        @foreach ($spec_data as $row)
                            <tr>
                                <td class="text-right">{{ $row->ranking }}</td>
                                <td>
                                    @include('snippets.role', ['role' => $row->role_name])
                                </td>
                                <td>
                                    @include('snippets.spec', [
                                        'role' => $row->role_name,
                                        'spec' => $row->spec_name
                                    ])
                                </td>
                                <td class="text-right">{{ sprintf("%01.1f", $row->pct) }}%</td>
                                <td class="text-right">{{ $row->num }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Races
                </div>
                <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        @foreach ($race_data as $row)
                            <tr>
                                <td class="text-right">{{ $row->ranking }}</td>
                                <td>
                                    @include('snippets.race', [
                                        'race' => $row->race_name,
                                        'gender' => null,
                                    ])
                                </td>
                                <td class="text-right">{{ sprintf("%01.1f", $row->pct) }}%</td>
                                <td class="text-right">{{ $row->num }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

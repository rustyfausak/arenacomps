@extends('templates.base')

@section('page-title')
    Stats
    <small>
        stats for the top 5000 players in
        {{ $region_str }}
    </small>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Classes
                </div>
                <table class="table table-striped table-bordered table-condensed table-hover">
                    <tbody>
                        @foreach ($stats['by_role'] as $role_id => $arr)
                            @include('stats.role')
                            @foreach ($arr['by_race'] as $race_id => $arr)
                                @include('stats.race')
                            @endforeach
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
                        @foreach ($stats['by_spec'] as $spec_id => $arr)
                            @include('stats.spec')
                            @foreach ($arr['by_race'] as $race_id => $arr)
                                @include('stats.race')
                            @endforeach
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
                        @foreach ($stats['by_race'] as $race_id => $arr)
                            @include('stats.race')
                            @foreach ($arr['by_role'] as $role_id => $arr)
                                @include('stats.role')
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

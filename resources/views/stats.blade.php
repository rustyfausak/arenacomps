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
                <table class="table table-striped table-bordered table-condensed">
                    <tbody>
                        @foreach ($stats['by_role'] as $role_id => $arr)
                            @include('stats.role', ['expando' => '#expando-role-' . $role_id])
                            <tr id="expando-role-{{ $role_id }}" class="hide">
                                <td colspan="100%" style="padding: 12px;">
                                    <table class="table table-striped table-bordered table-condensed table-sm no-margin-bottom">
                                        <tbody>
                                            @foreach ($arr['by_race'] as $race_id => $arr)
                                                @include('stats.race')
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="panel-body">
                    <div id="chart-roles" class="ct-chart ct-minor-sixth"></div>
                </div>
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
                            @include('stats.spec', ['expando' => '#expando-spec-' . $spec_id])
                            <tr id="expando-spec-{{ $spec_id }}" class="hide">
                                <td colspan="100%" style="padding: 12px;">
                                    <table class="table table-striped table-bordered table-condensed table-sm no-margin-bottom">
                                        <tbody>
                                            @foreach ($arr['by_race'] as $race_id => $arr)
                                                @include('stats.race')
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
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
                        @foreach ($stats['by_race'] as $race_id => $arr)
                            @include('stats.race', ['expando' => '#expando-race-' . $race_id])
                            <tr id="expando-race-{{ $race_id }}" class="hide">
                                <td colspan="100%" style="padding: 12px;">
                                    <table class="table table-striped table-bordered table-condensed table-sm no-margin-bottom">
                                        <tbody>
                                           @foreach ($arr['by_role'] as $role_id => $arr)
                                                @include('stats.role')
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('foot')
    <script type="text/javascript">
    $(document).ready(function () {
        var data = {
            // A labels array that can contain any sort of values
            labels: {!! json_encode($chart['labels']) !!},
            // Our series array that contains series objects or in this case series data arrays
            series: [
            @foreach ($chart['series'] as $series)
                {!! json_encode($series) !!},
            @endforeach
            ]
        };

        // Create a new line chart object where as first parameter we pass in a selector
        // that is resolving to our chart container element. The Second parameter
        // is the actual data object.
        new Chartist.Line('#chart-roles', data);
    });
    </script>
@endpush

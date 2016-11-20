@extends('templates.base')

@section('page-title')
    FAQ
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Where does the data come from?
                </div>
                <div class="panel-body">
                    <p>
                        The <a href="https://dev.battle.net/">Battle.net API</a>. Specifically, the <code>PVP /wow/leaderboard/:bracket</code> request.
                    </p>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    How are comps determined?
                </div>
                <div class="panel-body">
                    <p>
                        The leaderboard is grabbed every few minutes. A delta is computed for each player from the last leaderboard to the current one. <a href="{{ route('activity') }}">View the deltas here</a>. Then deltas are compared between players. When consecutive deltas match, players are put on a team with one another.
                    </p>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    How accurate is the comp data?
                </div>
                <div class="panel-body">
                    <p>
                        It is impossible to say how accurate it is, since there is no way to know definitively which players play together. Also players with more erratic queueing will tend not to match up since the matching algorithm requires consecutive games played. Furthermore the data returned by Battle.net is not perfect.
                    </p>
                    <p>
                        We can judge the quality of the data based on what is reasonable. For example, most teams:
                    </p>
                    <ul>
                        <li>have 1 healer and 2 dps</li>
                        <li>are about the same rating</li>
                        <li>play together throughout the season</li>
                    </ul>
                    <p>
                        If the data we find agrees with these reasonable assumptions, we can give it more creedence. Otherwise we will be skeptical of it. You can draw your own conclusions by looking into the comp. See what teams play it, who is on those teams and what type of activity those players have.
                    </p>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    More questions?
                </div>
                <div class="panel-body">
                    <p>
                        <a href="{{ route('contact') }}">Get in touch</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

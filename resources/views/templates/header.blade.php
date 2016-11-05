<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{ route('index') }}">
                AC
            </a>
        </div>
        <ul class="nav navbar-nav">
            <li><a href="{{ route('stats', $qs) }}">Stats</a></li>
            <li><a href="{{ route('leaderboard', $qs) }}">Leaderboard</a></li>
            <li><a href="{{ route('comps', $qs) }}">Comps</a></li>
        </ul>
        <form action="" method="get" class="navbar-form navbar-left">
            <div class="form-group">
                <select name="region" class="form-control" data-submit-on-change>
                    <option value="all">All</option>
                    @foreach (App\Models\Region::all() as $_region)
                        <option value="{{ $_region->name }}" {{ $region && $_region->id == $region->id ? 'selected="selected"' : '' }}>{{ $_region->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="bracket" class="form-control" data-submit-on-change>
                    @foreach (App\Models\Bracket::all() as $_bracket)
                        <option value="{{ $_bracket->name }}" {{ $_bracket->id == $bracket->id ? 'selected="selected"' : '' }}>{{ $_bracket->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="season" class="form-control" data-submit-on-change>
                    @foreach (App\Models\Season::all() as $_season)
                        <option value="{{ $_season->id }}" {{ $_season->id == $season->id ? 'selected="selected"' : '' }}>{{ $_season->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="term" class="form-control" data-submit-on-change>
                    <option value="all">All</option>
                    @foreach (App\Models\Term::where('season_id', '=', $season->id)->get() as $_term)
                        <option value="{{ $_term->id }}" {{ $term && $_term->id == $term->id ? 'selected="selected"' : '' }}>{{ $_term->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</nav>

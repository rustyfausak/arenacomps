<nav class="navbar navbar-inverse navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{ route('index') }}" id="brand">
                arenacomps<span class="beta">beta</span>
            </a>
        </div>
        <ul class="nav navbar-nav">
            <li><a href="{{ route('comps') }}">Comps</a></li>
            <li><a href="{{ route('stats') }}">Stats</a></li>
            <li><a href="{{ route('leaderboard') }}">Leaderboard</a></li>
        </ul>
        <form action="" method="get" class="navbar-form navbar-left">
            <div class="form-group">
                <select name="region" class="form-control" data-submit-on-change>
                    <option value="all">All</option>
                    @foreach (App\Models\Region::all() as $region)
                        <option value="{{ $region->name }}" {{ $om->region && $region->id == $om->region->id ? 'selected="selected"' : '' }}>{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="bracket" class="form-control" data-submit-on-change>
                    @foreach (App\Models\Bracket::all() as $bracket)
                        <option value="{{ $bracket->name }}" {{ $bracket->id == $om->bracket->id ? 'selected="selected"' : '' }}>{{ $bracket->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="season" class="form-control" data-submit-on-change>
                    @foreach (App\Models\Season::all() as $season)
                        <option value="{{ $season->id }}" {{ $season->id == $om->season->id ? 'selected="selected"' : '' }}>{{ $season->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="term" class="form-control" data-submit-on-change>
                    <option value="all">All</option>
                    @foreach (App\Models\Term::where('season_id', '=', $season->id)->get() as $term)
                        <option value="{{ $term->id }}" {{ $om->term && $term->id == $om->term->id ? 'selected="selected"' : '' }}>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</nav>

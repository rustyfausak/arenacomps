<img
    class="wowicon wowicon-race"
    src="{{ url('img/icons/races/' . strtolower(str_replace(' ', '', preg_replace('/ \((h|a)\)$/i', '', $race))) . '-' . ($gender ? strtolower($gender) : 'male') . '.png') }}"
    title="{{ $race . ' ' . ($gender ? $gender : '') }}">

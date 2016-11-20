<div class="footer">
    <div class="container text-center">
        &copy; {{ date("Y") }} arenacomps &mdash; <a href="{{ route('contact') }}">contact</a>
        <br>
        <small><a href="https://icons8.com/">icon credits</a></small>
        <div>{{ sprintf("%01.2f", round(microtime(true) - $xtime, 2)) }}</div>
    </div>
</div>

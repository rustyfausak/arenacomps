<tr>
    <td class="text-right">{{ $loop->iteration }}</td>
    <td>
        <a href="{{ route('leaderboard', ['class' => $role_id]) }}">
            @include('snippets.role', ['role' => $roles[$role_id]])
        </a>
    </td>
    <td class="text-right">{{ sprintf("%01.1f", $arr['pct']) }}%</td>
    <td class="text-right">{{ $arr['num'] }}</td>
</tr>

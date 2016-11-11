<tr>
    <td class="text-right">{{ $loop->iteration }}</td>
    <td>
        @include('snippets.role-spec', [
            'role' => $roles[$specs[$spec_id]->role_id]->name,
            'spec' => $specs[$spec_id]->name
        ])
        @include('stats.expando')
    </td>
    <td class="text-right">{{ sprintf("%01.1f", $arr['pct']) }}%</td>
    <td class="text-right">{{ $arr['num'] }}</td>
</tr>

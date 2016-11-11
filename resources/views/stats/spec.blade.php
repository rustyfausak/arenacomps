<tr>
    <td class="text-right">{{ $loop->iteration }}</td>
    <td>
        @include('snippets.role', ['role' => $roles[$specs[$spec_id]->role_id]->name])
    </td>
    <td>
        @include('snippets.spec', [
            'role' => $roles[$specs[$spec_id]->role_id]->name,
            'spec' => $specs[$spec_id]->name
        ])
    </td>
    <td class="text-right">{{ sprintf("%01.1f", $arr['pct']) }}%</td>
    <td class="text-right">{{ $arr['num'] }}</td>
</tr>

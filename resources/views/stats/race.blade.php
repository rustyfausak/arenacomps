<tr>
    <td class="text-right">{{ $loop->iteration }}</td>
    <td>
        @include('snippets.race', [
            'race' => $races[$race_id]->name,
            'gender' => null,
        ])
        @include('stats.expando')
    </td>
    <td class="text-right">{{ sprintf("%01.1f", $arr['pct']) }}%</td>
    <td class="text-right">{{ $arr['num'] }}</td>
</tr>

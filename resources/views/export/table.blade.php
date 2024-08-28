<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Nama Penerima</th>
            <th>Lokasi</th>
            <th>Nama User</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mapsData as $map)
            <tr>
                <td>{{ $map['date'] }}</td>
                <td>{{ $map['name_penerima'] }}</td>
                <td>{{ $map['lokasi'] }}</td>
                <td>{{ $map['name'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

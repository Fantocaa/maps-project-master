<!DOCTYPE html>
<html>

<head>
    <title>Maps Export</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Perusahaan</th>
                <th>Customer</th>
                <th>Jenis Barang</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Harga Modal</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mapsData as $map)
                <tr>
                    <td>{{ $map->id }}</td>
                    <td>{{ $map->name }}</td>
                    <td>{{ $map->perusahaan->name_company ?? 'Unknown' }}</td>
                    <td>{{ $map->customer->name_company ?? 'Unknown' }}</td>
                    <td>{{ $map->jenisbarang->jenis_barang_name ?? 'Unknown' }}</td>
                    <td>{{ $map->satuan->name_satuan ?? 'Unknown' }}</td>
                    <td>{{ $map->harga }}</td>
                    <td>{{ $map->harga_modal }}</td>
                    <td>{{ $map->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

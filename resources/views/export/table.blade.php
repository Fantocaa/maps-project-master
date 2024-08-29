<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama User</th>
            <th>Tanggal Input</th>
            <th>Lokasi</th>
            <th>Nama Penerima</th>
            <th>Perusahaan</th>
            <th>Customer</th>
            <th>Satuan</th>
            <th>Jenis Barang</th>
            <th>Biaya</th>
            <th>Harga Jual</th>
            <th>Harga Modal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mapsData as $map)
            @php
                $firstMap = true;
            @endphp
            @foreach ($map->customers as $customer)
                @php
                    $firstCustomer = true;
                @endphp
                @foreach ($customer['satuan'] as $satuan)
                    @php
                        $firstSatuan = true;
                    @endphp
                    @foreach ($satuan['biaya'] as $biaya)
                        <tr>
                            <!-- Kolom untuk data $map -->
                            <td>{{ $firstMap ? $map->id : '' }}</td>
                            <td>{{ $firstMap ? $map->name : '' }}</td>
                            <td>{{ $firstMap ? $map->date : '' }}</td>
                            <td>{{ $firstMap ? $map->lokasi : '' }}</td>
                            <td>{{ $firstMap ? $map->name_penerima : '' }}</td>
                            <td>{{ $firstMap ? $map->name_company : '' }}</td>

                            <!-- Kolom untuk data customer -->
                            <td>{{ $firstCustomer ? $customer['name_customer'] : '' }}</td>

                            <!-- Kolom untuk data satuan -->
                            <td>{{ $firstSatuan ? $satuan['name_satuan'] : '' }}</td>
                            <td>{{ $firstSatuan ? $satuan['jenis_barang_name'] : '' }}</td>

                            <!-- Kolom untuk data biaya -->
                            <td>{{ $biaya['name_biaya'] }}</td>
                            <td>{{ $biaya['harga'] }}</td>
                            <td>{{ $biaya['harga_modal'] }}</td>
                        </tr>
                        @php
                            $firstMap = false;
                            $firstCustomer = false;
                            $firstSatuan = false;
                        @endphp
                    @endforeach
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>

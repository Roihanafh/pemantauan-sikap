<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <tr>
            <th colspan="8">Penilaian Siswa</th>
        </tr>
        <tr>
            <td>Kelas</td>
            <td colspan="7">{{ $kelas->nama }}</td>
        </tr>
        <tr>
            <td>Pertemuan</td>
            <td colspan="7">{{ $pertemuan }}</td>
        </tr>
    </table>

    <br>

    <table border="1">
        <thead>
            <tr>
                @foreach (['No', 'Nama Siswa', 'Gender', 'Respect', 'Participation', 'Self Direction', 'Caring', 'Transfer'] as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Serah Terima Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
        }
        .header {
            width: 100%;
            text-align: center;
            position: relative;
            margin-bottom: 30px;
            line-height: 1;
            font-size: 13pt;
        }
        .header img {
            position: absolute;
            left: 0;
            top: 50px;
            transform: translateY(-50%);
            width: 110px;
            height: auto;
        }
        .header-text {
            display: inline-block;
            margin-left: 115px;
            width: calc(100% - 115px);
        }
        .header-institution {
            font-weight: 900;
            letter-spacing: 2px;
        } 
        .divider {
            border-top: 3px solid black;
            margin: 10px 0;
        }
        .title {
            text-align: center;
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .nomor {
            text-align: center;
            margin-bottom: 20px;
        }
        .date {
            margin-bottom: 20px;
        }
        .party {
            margin-bottom: 20px;
        }
        .party-label {
            font-weight: bold;
            text-transform: uppercase;
        }
        .party-table {
            width: 100%;
            border-collapse: collapse;
        }
        .party-table td {
            padding: 3px 0;
            border: none;
        }
        .party-label-col {
            width: 15%;
            text-align: left;
            padding-right: 10px;
        }
        .party-colon-col {
            width: 2%;
            text-align: left;
        }
        .party-value-col {
            width: 83%;
            text-align: left;
        }
        .table-assets {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-assets th, .table-assets td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .table-assets tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .table-assets th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .asset-group {
            page-break-inside: avoid;
        }
        .category-header {
            background-color: #e8e8e8;
            font-weight: bold;
            page-break-after: avoid;
        }
        .category-header td {
            padding: 10px 8px;
            border: 1px solid black;
        }
        .statement {
            margin-bottom: 20px;
        }
        .closing {
            margin-bottom: 40px;
        }
        .bold {
            font-weight: bold;
        }
        .signatures-container {
            margin-top: 60px;
            width: 100%;
        }
        .signatures {
            width: 100%;
            margin: 0 auto 80px auto;
        }
        .signature-column {
            width: 50%;
            float: left;
            text-align: center;
        }
        .signature-space {
            height: 80px;
            margin: 20px 0;
        }
        .knowing {
            clear: both;
            text-align: center;
            padding-top: 20px;
        }
        @page {
            margin: 2cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://upload.wikimedia.org/wikipedia/commons/0/01/Lambang_Kabupaten_Muara_Enim.gif" alt="Logo">
        <div class="header-text">
            PEMERINTAH KABUPATEN MUARA ENIM<br>
            <span class="header-institution">DINAS KOMUNIKASI, INFORMATIKA<br>
            STATISTIK DAN PERSANDIAN</span><br>
           <span class="header-address">Jln. Jend. Ahmad Yani No. 14 Kel. Psr I Telp./Fax 0734 421175<br>
           www.muaraenimkab.go.id email: kominfo@muaraenimkab.co.id<br>
           MUARA ENIM SUMATERA SELATAN 31311</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="title">
        BERITA ACARA SERAH TERIMA BARANG
    </div>

    <div class="nomor">
        {{ $nomor }}
    </div>

    <div class="date">
        Pada hari <span class="bold">{{ $date_day }}</span> tanggal <span class="bold">{{ $date_date }}</span> bulan <span class="bold">{{ $date_month }}</span> tahun <span class="bold">{{ $date_year }}</span> ({{ $date_numeric }}).
    </div>

    <div>
        Yang bertandatangan di bawah ini :
    </div>

    <div class="party">
        <table class="party-table">
            <tr>
                <td class="party-label-col">Nama</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $first_party['name'] }}</td>
            </tr>
            <tr>
                <td class="party-label-col">NIP</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $first_party['nip'] }}</td>
            </tr>
            <tr>
                <td class="party-label-col">Jabatan</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $first_party['position'] }}</td>
            </tr>
            <tr>
                <td class="party-label-col">Instansi</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $first_party['agency'] }}</td>
            </tr>
        </table>
        Selanjutnya disebut <span class="party-label">PIHAK PERTAMA</span>
    </div>

    <div class="party">
        <table class="party-table">
            <tr>
                <td class="party-label-col">Nama</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $second_party['name'] }}</td>
            </tr>
            <tr>
                <td class="party-label-col">NIP</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $second_party['nip'] }}</td>
            </tr>
            <tr>
                <td class="party-label-col">Jabatan</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $second_party['position'] }}</td>
            </tr>
            <tr>
                <td class="party-label-col">Instansi</td>
                <td class="party-colon-col">:</td>
                <td class="party-value-col">{{ $second_party['agency'] }}</td>
            </tr>
        </table>
        Selanjutnya disebut <span class="party-label">PIHAK KEDUA</span>.
    </div>

    <div class="location">
        bertempat di Dinas Komunikasi, Informatika, Statistik dan Persandian Kabupaten Muara Enim
    </div>

    <div class="statement">
        <span class="party-label">PIHAK PERTAMA</span> telah menyerahkan Aset kepada <span class="party-label">PIHAK KEDUA</span> berupa :
    </div>

    <table class="table-assets">
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis Asset</th>
                <th>Jumlah</th>
                <th>Merk / Type</th>
                <th>Nomor</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($grouped_assets as $group)
                {{-- Category Header Row --}}
                <tr class="category-header">
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td colspan="2">{{ $group['category_name'] }} ({{ $group['count'] }} Unit)</td>
                    <td colspan="3"></td>
                </tr>
                {{-- Item Rows --}}
                @foreach($group['items'] as $item)
                    <tr>
                        <td>-</td>
                        <td colspan="2"></td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['number'] }}</td>
                        <td>{{ $item['description'] }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="statement">
        <span class="party-label">PIHAK KEDUA</span> dengan ini menyatakan telah menerima barang – barang tersebut dari <span class="party-label">PIHAK PERTAMA</span> dalam keadaan baik dan lengkap.
    </div>

    <div class="closing">
        Demikian berita acara ini dibuat dengan sebenarnya untuk dapat digunakan sebagaimana mestinya.<br>
        Muara Enim, {{ $date_year_numeric }}
    </div>

    <div class="signatures-container">
        <div class="signatures">
            <div class="signature-column">
                <div class="party-label">PIHAK KEDUA</div>
                <div class="signature-space"></div>
                <span class="bold">{{ $second_party['name'] }}</span><br>
                {{ $second_party['rank'] }}<br>
                NIP. {{ $second_party['nip'] }}
            </div>
            <div class="signature-column">
                <div class="party-label">PIHAK PERTAMA</div>
                <div class="signature-space"></div>
                <span class="bold">{{ $first_party['name'] }}</span><br>
                {{ $first_party['rank'] }}<br>
                NIP. {{ $first_party['nip'] }}
            </div>
        </div>

        <div class="knowing">
            Mengetahui<br>
            Kepala Dinas KominfoSP<br>
            Kabupaten Muara Enim<br>
            <div class="signature-space"></div>
            <span class="bold">{{ $knowing['name'] }}</span><br>
            {{ $knowing['rank'] }}<br>
            NIP. {{ $knowing['nip'] }}
        </div>
    </div>
</body>
</html>
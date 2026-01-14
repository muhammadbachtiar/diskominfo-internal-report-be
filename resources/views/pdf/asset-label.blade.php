<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Aset</title>
    <style>
        @page {
            margin: 1cm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .page-break {
            page-break-after: always;
        }
        .label-container {
            width: 100%;
            height: 3cm;
            border: 4px solid #000;
            box-sizing: border-box;
            background-color: #fff;
            margin-bottom: 0.5cm; /* Space between labels */
            position: relative;
            page-break-inside: avoid;
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .table-layout {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 20%; /* Adjust based on preference, roughly 120px equivalent */
            text-align: center;
            vertical-align: middle;
            padding-left: 10px;
        }
        .content-cell {
            vertical-align: middle;
            padding: 5px 10px;
        }
        .header-logo {
            width: 90px; /* Reduced slightly to fit 4cm height nicely */
            height: auto;
            max-height: 3.5cm;
        }
        .asset-name {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .asset-info {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            line-height: 1.4;
        }
        .separator {
            margin: 0 4px;
            color: #000;
        }
        .footer {
            font-size: 9pt;
            text-align: center;
            margin-top: 6px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @foreach($assets as $index => $asset)
        <div class="label-container">
            <table class="table-layout">
                <tr>
                    <td class="logo-cell">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/01/Lambang_Kabupaten_Muara_Enim.gif" alt="Lambang Kabupaten Muara Enim" class="header-logo">
                    </td>
                    <td class="content-cell">
                        <div class="asset-name">
                            {{ $asset['name'] }}
                        </div>

                        <div class="asset-info">
                            TAHUN {{ $asset['year'] }} <span class="separator">|</span> 
                            NO. ASET {{ $asset['code'] }} <span class="separator">|</span> 
                            @if($asset['serial_number'])
                            NO. SERI {{ $asset['serial_number'] }} <span class="separator">|</span> 
                            @endif
                            {{ $asset['category_name'] }}
                        </div>

                        <div class="footer">
                            DISKOMINFO SP KABUPATEN MUARA ENIM
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>

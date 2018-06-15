<table style="width: 600px; background-color: #efefef;" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            EASY SYSTEMS - RRHH
        </td>
    </tr>
    <tr>
        <td style="font-family: Arial; font-size: 14px; background-color: #efefef;">
            <table>
                <tr>
                    <td colspan="2" style="font-size: 15px; font-weight: bold;">Respaldo de Base de Datos Ejecutada.</td>
                </tr>
                <tr>
                    <td style="width: 150px;">Fecha:</td>
                    <td style="width: 450px;">{{ date("d-m-Y") }}</td>
                </tr>
                <tr>
                    <td>Hora:</td>
                    <td>{{ date("H:i:s") }}</td>
                </tr>
                <tr>
                    <td>Archivo SQL:</td>
                    <td>{{ $archivo }}</td>
                </tr>
                <tr>
                    <td>Cliente:</td>
                    <td>{{ $cliente }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
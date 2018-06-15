<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Planilla Costo Empresa</title>
</head>
<body>
    
    <table>
        <thead>
            <tr>
                <th>RUT</th>        
                <th>Nombre</th>                
                <th>Cargo</th>                
                <th>Sueldo</th>                
                <th>Total Imponibles</th>                
                <th>Total No Imponibles</th>                
                <th>S. Cesantía</th>                
                <th>S.I.S.</th>                
                <th>Fonasa</th>                
                <th>Caja</th>                
                <th>Mutual</th>                
                <th>Total Aportes</th>                
                <th>Sueldo Líquido</th>                
            </tr>
        </thead>
        <tbody>
            @foreach($planilla as $dato)
                <tr>
                    <td>{{ $dato['rutFormato'] }}</td>
                    <td>{{ $dato['nombreCompleto'] }}</td>
                    <td>{{ $dato['cargo'] }}</td>
                    <td>{{ Funciones::formatoPesos($dato['sueldo']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['imponibles']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['noImponibles']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['seguroCesantia']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['sis']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['fonasa']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['caja']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['mutual']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['aportes']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['sueldoLiquido']) }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>
    
</body>
</html>
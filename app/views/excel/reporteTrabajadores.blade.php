<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Reporte Trabajadores</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>RUT</th>        
                <th>Nombre</th>                
                <th>Dirección</th>                
                <th>Cargo</th>                               
                <th>Sección</th>                               
                <th>Tipo Contrato</th>                               
                <th>C. Costo</th>                               
                <th>F. Ingreso</th>                               
                <th>Sueldo Base</th>                               
                <th>AFP</th>                               
                <th>Salud</th>                               
                <th>S. Cesantía</th>                               
            </tr>
        </thead>
        <tbody>
            @foreach($trabajadores as $dato)
                <tr>
                    <td>{{ $dato['rutFormato'] }}</td>
                    <td>{{ $dato['nombreCompleto'] }}</td>
                    <td>{{ $dato['direccion'] }}, {{ $dato['comuna']['comuna'] }}</td>
                    <td>{{ $dato['cargo']['nombre'] }}</td>
                    <td>{{ $dato['seccion']['nombre'] }}</td>
                    <td>{{ $dato['tipoContrato']['nombre'] }}</td>
                    <td>{{ $dato['centroCosto']['nombre'] }}</td>
                    <td>{{ $dato['fechaIngreso'] }}</td>
                    <td>{{ Funciones::formatoPesos($dato['sueldoBase']) }}</td>
                    <td>{{ $dato['afp']['nombre'] }}</td>
                    <td>{{ $dato['isapre']['nombre'] }}</td>
                    <td>{{ $dato['seguroDesempleo'] ? 'Sí' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>
    
</body>
</html>
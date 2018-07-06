<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Provisión Vacaciones</title>
  <style type="text/css">  
    #mes td 
    {
        text-align:center; 
        vertical-align:middle;
        font-weight: 900;
        font-size: 15px;
    }    
    #provision td, th {
        border:1px solid #000000; 
    }  
    #encabezado td, th {
        font-weight: 900;
        font-size: 13px;
    }   
  </style>
</head>
<body>    
    <table id="encabezado">
        <tbody>
            <tr>
                <td colspan="15">{{ $empresa->razon_social }}</td>
            </tr>
            <tr>
                <td colspan="15">RUT: {{ Funciones::formatear_rut($empresa->rut) }}</td>
            </tr>
            <tr>
                <td colspan="15">{{ $empresa->actividad_economica }}</td>
            </tr>
        </tbody>
    </table>
    <table id="mes">
        <tbody>
            <tr>
                <td colspan="15">PROVISIÓN DE VACACIONES</td>
            </tr>
            <tr>
                <td colspan="15">{{ $mes->mesActivo }}</td>
            </tr>
        </tbody>
    </table>
    <table id="provision">
        <thead>
            <tr>
                <th>#</th>   
                <th>RUT</th>   
                <th>NOMBRE</th>                    
                <th>CARGO</th>                    
                <th>SECCIÓN</th>                    
                <th>FECHA DE INGRESO</th>                    
                <th>SALDO</th>                    
                <th>DÍAS DEL MES</th>                    
                <th>ACUMULADAS</th>                    
                <th>TOMADAS</th>                    
                <th>DÍAS HÁBILES</th>                    
                <th>DÍAS CORRIDOS</th>                    
                <th>PROVISIÓN</th>                    
                <th>SUELDO BASE</th>                    
                <th>SUELDO DIARIO</th>  
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $index => $dato)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dato['rutFormato'] }}</td>
                    <td>{{ $dato['nombreCompleto'] }}</td>
                    <td>{{ $dato['cargo']['nombre'] }}</td>
                    <td>{{ $dato['seccion']['nombre'] }}</td>
                    <td>{{ $dato['fechaIngreso'] }}</td>
                    <td>{{ $dato['provision']['saldo'] }}</td>
                    <td>{{ $dato['provision']['dias'] }}</td>
                    <td>{{ $dato['provision']['acumuladas'] }}</td>
                    <td>{{ $dato['provision']['tomadas'] }}</td>
                    <td>{{ $dato['provision']['diasHabiles'] }}</td>
                    <td>{{ $dato['provision']['diasCorridos'] }}</td>
                    <td>{{ Funciones::formatoPesos($dato['provision']['provision']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['provision']['sueldoBase']) }}</td>
                    <td>{{ Funciones::formatoPesos($dato['provision']['sueldoDiario']) }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>

</body>
</html>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Libro de Remuneraciones</title>
</head>
<body>
    
    <table>
        <thead>
            <tr>
                <th>RUT</th>        
                <th>Nombre</th>                
                <th>Cargo</th>                
                <th>Código Banco</th>                
                <th>Nombre Banco</th>                
                <th>Tipo Cuenta</th>                
                <th>N° Cuenta</th>                
                <th>Monto</th>                
            </tr>
        </thead>
        <tbody>
            @foreach($trabajadores as $dato)
                <tr>
                    <td>{{ $dato['rut'] }}</td>
                    <td>{{ $dato['nombreCompleto'] }}</td>
                    <td>{{ $dato['cargo'] }}</td>
                    <td>{{ $dato['codigoBanco'] }}</td>
                    <td>{{ $dato['nombreBanco'] }}</td>
                    <td>{{ $dato['tipoCuenta'] }}</td>
                    <td>{{ $dato['numeroCuenta'] }}</td>
                    <td>{{ $dato['monto'] }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>
    
</body>
</html>
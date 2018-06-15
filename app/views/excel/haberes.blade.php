<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Haberes</title>
</head>
<body>    
    <table>
        <thead>
            <tr>
                <th>RUT</th>        
                <th>Moneda</th>                
                <th>Monto</th>                
                <th>Temporalidad</th>                            
            </tr>
        </thead>
        <tbody>
            @foreach($trabajadores as $dato)
                <tr>
                    <td>{{ $dato['rut'] }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>        
    </table>
    
</body>
</html>
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
                <th>Código</th>                       
                <th>Glosa</th>                
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $dato)
                <tr>
                    <td>{{ $dato['id'] }}</td>
                    <td>{{ $dato['nombre'] }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>
    
</body>
</html>
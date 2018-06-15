<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Códigos</title>
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
            @foreach($comunas as $comuna)
                <tr>
                    <td>{{ $comuna['id'] }}</td>
                    <td>{{ $comuna['glosa'] }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>
    
</body>
</html>
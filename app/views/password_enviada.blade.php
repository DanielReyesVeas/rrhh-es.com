<html>
<head>
    <title>EasySystems - Contabilidad Multi Empresa</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        div{
            position: absolute;
            font-family: Arial;
            font-size: 16px;
            width: 500px;
            height: 250px;
            top: 50%;
            left: 50%;
            margin-left: -250px;
            margin-top: -150px;
            box-shadow: 10px 10px 8px #dddddd;background:#ffffff;
            padding: 25px;
        }
    </style>
    @if( !$empresa )
        <meta http-equiv="refresh" content="10; url=http://{{ $_SERVER['SERVER_NAME'] }}" />
    @else
        <meta http-equiv="refresh" content="10; url=http://{{ $_SERVER['SERVER_NAME'] }}/#/login/{{ $empresa }}" />
    @endif
</head>
<body bgcolor="#eeeeee">
<div>
    Estimado Usuario: <br/>
    Tu nueva contraseña para acceder al sistema ha sido enviada a tu correo electrónico:<br/> <b>{{ $email }}</b><br/>
    <br/><br/>
    En unos instantes serás redireccionado al sistema.<br/>
    Saludos<br/>
    <img src="http://www.cme-es.com/logo.png" style="width: 200px;" />
</div>
</body>
</html>

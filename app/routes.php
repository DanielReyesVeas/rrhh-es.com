<?php
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

//ini_set('safe_mode_exec_dir', 'On');
//ini_set('display_errors', 'On');

ini_set('max_execution_time', 30000);
define('VERSION_SISTEMA', '1.7.9');
ini_set('memory_limit', '3048M');

if(Config::get('cliente.LOCAL')){
    header('Access-Control-Allow-Origin: http://localhost:9000');
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type');

Route::get('respaldo/basededatos/Q1W2E3Y6T5R4', 'RespaldoController@index');

if( \Session::get('basedatos') ){
    Config::set('database.default', \Session::get('basedatos') );
}else{
    Config::set('database.default', 'principal' );
}

Route::get('/', function(){
    return View::make('index');
});

Route::get('aaa', function(){
    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $empleados = array();
            Config::set('database.default', $empresa->base_datos);
            $empleados = Trabajador::all();
            echo '<h1>' . $empresa->razon_social . '</h1>';
            echo '<h3>' . $empresa->rut . '</h3>';
            $count = 0;
            foreach($empleados as $empleado){
                $ultimaFicha = $empleado->ultimaFicha();
                if($ultimaFicha){
                    if($ultimaFicha->estado!='En Creación'){
                        $haberes = $empleado->haberes;
                        if($haberes->count()){
                            echo '<br />' . $ultimaFicha->nombreCompleto() . '<br />';
                            foreach($haberes as $haber){
                                if($haber->tipoHaber->nombre=='Colación'){
                                    echo $haber->tipoHaber->nombre;
                                    if($ultimaFicha->proporcional_colacion){
                                        echo ' proporcional<br />';
                                        $haber->proporcional = 1;
                                        $haber->save();
                                    }else{
                                        echo ' no proporcional<br />';                                        
                                    }
                                }else if($haber->tipoHaber->nombre=='Movilización'){
                                    echo $haber->tipoHaber->nombre;
                                    if($ultimaFicha->proporcional_movilizacion){
                                        echo ' proporcional<br />';
                                        $haber->proporcional = 1;
                                        $haber->save();
                                    }else{
                                        echo ' no proporcional<br />';                                        
                                    }
                                }else if($haber->tipoHaber->nombre=='Viático'){
                                    echo $haber->tipoHaber->nombre;
                                    if($ultimaFicha->proporcional_viatico){
                                        echo ' proporcional<br />';
                                        $haber->proporcional = 1;
                                        $haber->save();
                                    }else{
                                        echo ' no proporcional<br />';                                        
                                    }
                                }
                            }
                        }
                    }                    
                }                
            }            
        }        
    }else{
        echo "Sin Empresas";
    }
    
});

Route::get('liq', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $empleados = array();
            Config::set('database.default', $empresa->base_datos);
            $empleados = Trabajador::all();
            echo '<h1>' . $empresa->razon_social . '</h1>';
            echo '<h3>' . $empresa->rut . '</h3>';
            $count = 0;
            foreach($empleados as $empleado){
                $ultimaFicha = $empleado->ultimaFicha();
                if($ultimaFicha){
                    if($ultimaFicha->estado!='En Creación'){
                        $liquidaciones = $empleado->liquidaciones;

                        if($liquidaciones->count()){
                            foreach($liquidaciones as $index => $liquidacion){
                                if($liquidacion->created_at>'2018-05-15 00:00:00'){
                                    echo $liquidacion->id . ' <br />';
                                }
                            }
                        }else{
                            echo 'Sin liquidaciones<br />';
                        }

                    }
                }
            }
            echo '<br />Total: ' . $count . '<br />';
        }
        
    }else{
        echo "Sin Empresas";
    }
});

Route::get('prest', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $prestamos = array();
            Config::set('database.default', $empresa->base_datos);
            $prestamos = Prestamo::all();
            echo '<h1>' . $empresa->razon_social . '</h1>';
            echo '<h3>' . $empresa->rut . '</h3>';
            $count = 0;
            foreach($prestamos as $prestamo){
                if($prestamo->codigo==0){
                    $prestamo->codigo = $prestamo->id;
                    $prestamo->save();
                }
            }
            echo '<br />Total: ' . $count . '<br />';
        }
        
    }else{
        echo "Sin Empresas";
    }
});

Route::get('cuadrar', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $liquidaciones = array();
            Config::set('database.default', $empresa->base_datos);
            $liquidaciones = Liquidacion::all();
            echo '<br /><h1>' . $empresa->razon_social . '</h1><br />';
            echo '<h3>' . $empresa->rut . '</h3><br />';
            $count = 0;
            foreach($liquidaciones as $liquidacion){
                $montoCaja = 0;
                $montoSalud = 0;
                $montoFonasa = 0;
                $fonasa = $liquidacion->detalleIpsIslFonasa;
                if($fonasa){
                    $montoFonasa = $fonasa->cotizacion_fonasa;
                    if($montoFonasa){
                        $salud = $liquidacion->detalleSalud;
                        $caja = $liquidacion->detalleCaja;
                        
                        if($salud){
                            $montoSalud = $salud->cotizacion_obligatoria;
                        }
                        if($caja){
                            $montoCaja = $caja->cotizacion;
                            $suma = ($montoFonasa + $montoCaja);
                            if($suma != $montoSalud){
                                $count++;
                                echo '<br />' . $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos . ':<br />';
                                echo 'Salud: ' . Funciones::formatoPesos($montoSalud) . '<br />';
                                echo 'Fonasa: ' . Funciones::formatoPesos($montoFonasa) . '<br />';
                                echo 'Caja: ' . Funciones::formatoPesos($montoCaja) . '<br />';
                                echo 'SUMA: ' . Funciones::formatoPesos($suma) . '<br />';
                                if($suma<$montoSalud){
                                    echo 'Menor<br />';
                                    $montoCaja += 1;
                                }else{
                                    echo 'Mayor<br />'; 
                                    $montoCaja -= 1;
                                }
                                /*$caja->cotizacion = $montoCaja;
                                $caja->save();*/
                                $suma = ($montoFonasa + $montoCaja);
                                echo 'Salud: ' . Funciones::formatoPesos($montoSalud) . '<br />';
                                echo 'Fonasa: ' . Funciones::formatoPesos($montoFonasa) . '<br />';
                                echo 'Caja: ' . Funciones::formatoPesos($montoCaja) . '<br />';
                                echo 'SUMA: ' . Funciones::formatoPesos($suma) . '<br />';
                                if($suma != $montoSalud){
                                    echo '<h1>No</h1><br />';
                                }else{
                                    echo '<h1>OK</h1><br />';
                                }
                            }
                        }else{
                            /*echo 'ISL<br />';
                            $montoCaja = $fonasa->cotizacion_isl;
                            $suma = ($montoSalud + $montoCaja);
                            if($suma != $montoFonasa){
                                $count++;
                                echo '<br />' . $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos . ':<br />';
                                echo 'Salud: ' . Funciones::formatoPesos($montoSalud) . '<br />';
                                echo 'Fonasa: ' . Funciones::formatoPesos($montoFonasa) . '<br />';
                                echo 'Caja: ' . Funciones::formatoPesos($montoCaja) . '<br />';
                                echo 'SUMA: ' . Funciones::formatoPesos($suma) . '<br />';
                                if($suma<$montoFonasa){
                                    echo 'Menor<br />';
                                    $montoCaja += 1;
                                }else{
                                    echo 'Mayor<br />'; 
                                    $montoCaja -= 1;
                                }
                                $suma = ($montoSalud + $montoCaja);
                                echo 'Salud: ' . Funciones::formatoPesos($montoSalud) . '<br />';
                                echo 'Fonasa: ' . Funciones::formatoPesos($montoFonasa) . '<br />';
                                echo 'Caja: ' . Funciones::formatoPesos($montoCaja) . '<br />';
                                echo 'SUMA: ' . Funciones::formatoPesos($suma) . '<br />';
                                if($suma != $montoFonasa){
                                    echo '<h1>Nope</h1><br />';
                                }
                            }*/
                        }        
                    }
                }
            }
            echo '<br />Total: ' . $count . '<br />';
        }
    }else{
        echo "Sin Empresas";
    }
});

Route::get('desc', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){                        
            Config::set('database.default', $empresa->base_datos);
            $es = EstructuraDescuento::find(10);
            if($es){
                echo '<h1>' . $empresa->razon_social . '</h1><br />';
                echo 'NOPE<br />';
            }else{
                $e = new EstructuraDescuento();
                $e->id = 10;
                $e->nombre = 'Préstamos';
                $e->save();
            }
            
            DB::table('detalle_liquidacion')->where('tipo_id', 4)->update(array('detalle_id' => 321));            
            
            $desc = TipoDescuento::find(321);
            if($desc){
                echo '<h1>' . $empresa->razon_social . '</h1><br />';
                echo 'NOPE<br />';
            }else{
                $d = new TipoDescuento();
                $d->id = 321;
                $d->estructura_descuento_id = 10;
                $d->cuenta_id = NULL;
                $d->sid = 'Q20180530153348LTW3578';
                $d->codigo = 50010;
                $d->nombre = 'Préstamos';
                $d->caja = 0;
                $d->descripcion = 'Préstamos';
                $d->afp_id = NULL;
                $d->forma_pago = NULL;
                $d->save();    
                echo 'OK<br />';
            }
        }
        
    }else{
        echo "Sin Empresas";
    }
});


Route::group(array('prefix' => 'rest/cme', 'before'=>'auth_ajax'), function() {
    
    Route::post('registrar-centralizacion', function(){
        $rut = Input::get('rutEmpresa');
        $periodo = Input::get('periodo');
        $numero = Input::get('numero');
        $empresa = Empresa::where('rut', $rut)->first();
        $datos=array('success' => false, 'rut' => $rut);
        
        if($empresa){
            \Session::put('basedatos', $empresa->base_datos);
            \Session::put('empresa', $empresa);
            Config::set('database.default', $empresa->base_datos);

            $comprobante = ComprobanteCentralizacion::where('mes', $periodo)->first();
            if($comprobante){
                $comprobante->numero = $numero;
                $comprobante->save();
            }
        }
    });
    
    Route::post('eliminar-centralizacion', function(){
        $rut = Input::get('rutEmpresa');
        $periodo = Input::get('periodo');
        $empresa = Empresa::where('rut', $rut)->first();
        if($empresa){
            \Session::put('basedatos', $empresa->base_datos);
            \Session::put('empresa', $empresa);
            Config::set('database.default', $empresa->base_datos);

            $comprobante = ComprobanteCentralizacion::where('mes', $periodo)->first();
            if($comprobante){
                $detalles = $comprobante->detalles;
                if($detalles->count()){
                    foreach($detalles as $detalle){
                        $detalle->delete();
                    }
                }
                $comprobante->delete();
            }
        }
    });
    
    Route::post('datos-empresa', function(){
        $rut = Input::get('rutEmpresa');
        $empresa = Empresa::where('rut', $rut)->first();
        $datos=array('success' => false, 'rut' => $rut);
        if($empresa){

            \Session::put('basedatos', $empresa->base_datos);
            \Session::put('empresa', $empresa);
            Config::set('database.default', $empresa->base_datos);

            $cuentas = Cuenta::listaCuentas();
            $listaCentrosCostos = CentroCosto::listaCentrosCosto();
            $listaCentrosCostosCuentas= CentroCosto::listaCentrosCostoCuentas();

            $cuentasCodigo=null;
            $listaCuentas=array();
            $cuentasCotizaciones=array();
            $cuentasRemuneraciones =array();
            if (count($cuentas)) {
                foreach ($cuentas as $cuenta) {
                    $listaCuentas[$cuenta['codigo']] = $cuenta;
                }
                $cuentasCodigo = Funciones::array_column($listaCuentas, 'codigo', 'id');
            }
            if( $empresa->centro_costo ){
                if (count($listaCuentas)) {
                
                    $remuneraciones = Aporte::where('tipo_aporte', 7)->first();
                    if(count($listaCentrosCostos)){
                        foreach ($listaCentrosCostos as $key => $value) {
                            $cuentasRemuneraciones[ $value['id'] ] = $remuneraciones->cuenta($cuentasCodigo, $value['id']);
                        }
                    }

                    $cotizaciones = Aporte::where('tipo_aporte', 8)->first();
                    if(count($listaCentrosCostos)){
                        foreach ($listaCentrosCostos as $key => $value) {
                            $cuentasCotizaciones[ $value['id'] ] = $cotizaciones->cuenta($cuentasCodigo, $value['id']);
                        }
                    }
                }

            }else{
                $remuneraciones = Aporte::where('tipo_aporte', 7)->first();
                $cuentasRemuneraciones= $remuneraciones->cuenta($cuentasCodigo, 0);

                $cotizaciones = Aporte::where('tipo_aporte', 8)->first();
                $cuentasCotizaciones = $cotizaciones->cuenta($cuentasCodigo, 0);
            }

            $datos=array(
                'success' => true,
                'razonSocial' => $empresa->razon_social, 
                'centroCosto' => $empresa->centro_costo? true : false,
                'nivelesCentroCosto' => $empresa->niveles_centro_costo,
                'listaCentrosCostos' => $listaCentrosCostos,
                'listaCentrosCostosCuentas' => $listaCentrosCostosCuentas,
                'cuentaCotizaciones' => $cuentasCotizaciones,
                'cuentaRemuneraciones' => $cuentasRemuneraciones
            );
        }
        return Response::json($datos);
    });
});

Route::get('restablecer/{sid}/{portal?}', function($sid, $portal=null){

    $usuario=null;
    $trabajador=null;
    $empresa="";
    if(!$portal) {
        $usuario = User::where('recuperacion', $sid)->first();
    }else{
        $empresa = base64_decode($portal);
        $emp = Empresa::where('portal', $empresa)->first();
        if( $emp ){
            Config::set('database.default', $emp->base_datos);
            $trabajador = Usuario::where('recuperacion', $sid)->first();
        }
    }
    if($usuario || $trabajador){

        // se genera una nueva contraseña
        if($usuario){
            $correo = $usuario->funcionario->email;
        }else{
            $correo = $trabajador->trabajador->fichaTrabajodorUltima->email;
        }

        if($correo) {
            $pass=rand(1000, 9999);
            if( $usuario ) {
                $usuario->password = Hash::make($pass);
                $usuario->recuperacion = "";
                $usuario->save();
            }else{
                $trabajador->password = Hash::make($pass);
                $trabajador->recuperacion = "";
                $trabajador->save();
            }


            $config = array(
                'driver' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'from' => array('address' => 'no-reply@easysystems.cl', 'name' => 'Soporte EasySystems'),
                'encryption' => 'ssl',
                'username' => 'soporte@easysystems.cl',
                'password' => 'easy1q2w3e',
                'sendmail' => '/usr/sbin/sendmail -bs',
                'pretend' => false
            );

            Config::set('mail', $config);

            $datos=array(
                'pass' => $pass,
                'empresa' => $empresa
            );

            Mail::send('envio_password', $datos, function ($message) use ($correo) {
                $message->to($correo);
                $message->from('no-reply@easysystems.cl', 'EasySystems - Restablecer Contraseña de Acceso');
                $message->replyTo('no-reply@easysystems.cl', 'EasySystems - Restablecer Contraseña de Acceso');
                $message->subject("Nueva Contraseña de Acceso");
            });
            return View::make('password_enviada')->with('email', $correo)->with('empresa', $empresa);
        }
    }elseif($empresa){
        echo "<html><head> <meta http-equiv=\"refresh\" content=\"0; url=http://".$_SERVER['SERVER_NAME']."/#/login/".$empresa."\" /> </head><body>recurso no encontrado</body></html>";
    }else{
        echo "<html><head> <meta http-equiv=\"refresh\" content=\"0; url=http://".$_SERVER['SERVER_NAME']."/\" /> </head><body>recurso no encontrado</body></html>";
    }
});


Route::post('login/password/reestablecer', function (){
    $usuario = Input::get('usuario');
    $email = Input::get('email');
    $empresa = Input::get('empresa');

    $trabajador=null;
    $funcionario=null;
    if( !$empresa ) {
        // se busca en funcionarios
        $funcionario = Funcionario::where('email', $email)->whereHas('usuario', function ($sq) use ($usuario) {
            $sq->where('username', $usuario);
        })->first();
    }else{
        $emp = Empresa::where('portal', $empresa)->first();
        if( $emp ){
            Config::set('database.default', $emp->base_datos);
            $trabajador = Usuario::whereHas('trabajador', function($sq) use($email){
                $sq->whereHas('fichaTrabajodorUltima', function($sq2) use($email){
                    $sq2->where('email', $email);
                });
            })->where('username', $usuario)->first();
        }
    }

    if($funcionario or $trabajador){
        $sid = Funciones::generarSID();
        $dominio  = $_SERVER['SERVER_NAME'];
        $urlRecuperacion = $dominio."/restablecer/".$sid."/";
        if($trabajador){
            $urlRecuperacion.=base64_encode($empresa);
        }

        $datos=array(
            'url' => $urlRecuperacion
        );

        if($funcionario)  $correo = $funcionario->email;
        elseif($trabajador){
            $correo = $trabajador->trabajador->fichaTrabajodorUltima->email;
        }

        $config = array(
            'driver' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'from' => array('address' => 'no-reply@easysystems.cl', 'name' => 'Soporte EasySystems'),
            'encryption' => 'ssl',
            'username' => 'soporte@easysystems.cl',
            'password' => 'easy1q2w3e',
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false
        );

        Config::set('mail', $config);

        Mail::send('restablecer_password', $datos, function ($message) use ($correo) {
            $message->to($correo);
            $message->from('no-reply@easysystems.cl', 'EasySystems - Restablecer Contraseña de Acceso');
            $message->replyTo('no-reply@easysystems.cl', 'EasySystems - Restablecer Contraseña de Acceso');
            $message->subject("Restablecer Contraseña de Acceso");
        });

        if($funcionario) {
            $funcionario->usuario->recuperacion = $sid;
            $funcionario->usuario->save();
        }else{
            $trabajador->recuperacion = $sid;
            $trabajador->save();
        }

        $resultado=array(
            'success' => true,
            'mensaje' => 'Un enlace para restablecer su contraseña fue enviado a su correo electrónico'
        );
    }else{
        $resultado=array(
            'success' => false,
            'mensaje' => 'El Nombre de Usuario y/o Correo Electrónico ingresado son incorrectos!'
        );
    }
    return Response::json($resultado);
});

Route::get('inicio/version', function(){
    // comprobar sesion activa
    if (Auth::usuario()->guest() && Auth::empleado()->guest()) return Response::make('login-error-status', 200);
    return Response::json(array('version' => VERSION_SISTEMA ));
});

Route::get('actualizacion-sql', function(){

    $archivoActualizacion = "actualizacionesSQL/actualizacion-sql-".date("d-m-Y").".sql";

    if( file_exists(public_path()."/".$archivoActualizacion) ) {
        $userBaseDatos = Config::get('cliente.CLIENTE.USUARIO');

        // ejecucion local
       // $userBaseDatos = "root";
        echo "archivo SQL : ".$archivoActualizacion."<br/>";

        // obtengo las empresas
        $empresas = Empresa::all();
        if ($empresas->count()) {
            foreach ($empresas as $empresa) {
                $nombreBaseDatos = $empresa->base_datos;
                shell_exec("mysql -u ".$userBaseDatos." -peasy1q2w3e4r ".$nombreBaseDatos." < ".$archivoActualizacion);
                echo "Base de datos: " . $empresa->base_datos . " fue actualizada <br/>";
            }
        }
        echo "Finalizo el Proceso de Actualización";
    }else{
        echo "No se encontro el archivo de actualización";
    }
});

Route::get('crear-factores', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $jornadas = array();
            Config::set('database.default', $empresa->base_datos);
            DB::table('jornadas_tramos')->delete();
            $jornadas = Jornada::all();
            foreach($jornadas as $jornada){
                $jornadaTramo = new JornadaTramo();
                $jornadaTramo->jornada_id = $jornada->id;
                $jornadaTramo->tramo_id = $jornada->tramo_hora_extra_id;
                $jornadaTramo->save();

                echo 'jornada : ' . $jornada->nombre . '<br />';
            }
        }
        
    }else{
        echo "Sin Empresas";
    }
});

Route::get('clon', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            if($empresa->rut=='763025128'){
                $trabajadores = array();
                Config::set('database.default', $empresa->base_datos);
                $trabajadores = Trabajador::all();
                foreach($trabajadores as $trabajador){

                    echo 'Trabajador : ' . $trabajador->rut . '<br />';
                }
            }
        }        
    }else{
        echo "Sin Empresas";
    }
});

Route::get('factores', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            echo '<br /><br /><h1>' . $empresa->razon_social . '</h1><br /><br />';
            $horas = array();
            Config::set('database.default', $empresa->base_datos);
            $horas = HoraExtra::all();
            foreach($horas as $hora){
                if($hora->factor=='0.000000000'){
                    $trab = $hora->trabajador;
                    $ficha = $trab->ultimaFicha();
                    echo '<br />' . $ficha->tipoJornada->nombre . '<br />';
                    $tramos = $ficha->tipoJornada->jornadaTramo;
                    echo $tramos[0]->tramo->factor . '<br />';
                    echo $ficha->nombres . '<br />';
                    $hora->factor = $tramos[0]->tramo->factor;
                    $hora->save();
                }
            }
        }        
    }else{
        echo "Sin Empresas";
    }
});


Route::get('crear-haberes', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
        
    if($empresas->count()){
        foreach($empresas as $empresa){
            Config::set('database.default', $empresa->base_datos);
            $trabajadores = Trabajador::all();
            echo '<br /><br /><h2>' . $empresa->razon_social . '</h2><br /><br />';
            if($trabajadores->count()){
                foreach($trabajadores as $trabajador){
                    $fichas = $trabajador->fichaTrabajador;        
                    $colaciones = array();
                    $movilizaciones = array();
                    $viaticos = array();
                    if($fichas->count()){
                        $colacionAnterior = 101010101010101010;
                        $movilizacionAnterior = 101010101010101010;
                        $viaticoAnterior = 101010101010101010;
                        $colacionIguales = true;
                        $movilizacionIguales = true;
                        $viaticoIguales = true;
                        $colacionInicial = false;
                        $movilizacionInicial = false;
                        $viaticoInicial = false;
                        echo '<br /><b>' . $trabajador->ultimaFicha()->nombreCompleto() . '</b><br />';
                        foreach($fichas as $ficha){
                            if($ficha->estado!='En Creación'){                                
                                echo '<br />' . $ficha->fecha . '<br />';
                                $colacion = $ficha->monto_colacion ? $ficha->monto_colacion : 0;
                                $movilizacion = $ficha->monto_movilizacion ? $ficha->monto_movilizacion : 0;
                                $viatico = $ficha->monto_viatico ? $ficha->monto_viatico : 0;
                                $fecha = $ficha->fecha;
                                
                                if($colacionAnterior==101010101010101010){
                                    if($colacion>0){
                                        echo 'Colacion inicial: ' . $colacion . '<br />';
                                        $colaciones[] = array(
                                            'idTrabajador' => $trabajador->id,
                                            'idHaber' => 3,
                                            'monto' => $colacion,
                                            'moneda' => $ficha->moneda_colacion,
                                            'proporcional' => $ficha->proporcional_colacion ? true : false,
                                            'desde' => $ficha->fecha,
                                            'hasta' => NULL
                                        );
                                        $colacionInicial = true;
                                    }else{
                                        echo 'Sin Colacion<br />';
                                    }                                    
                                }else{
                                    if($colacionAnterior!=$colacion){
                                        echo 'Colacion: anterior ' . $colacionAnterior . ' - actual ' . $colacion . '<br />';
                                        $colaciones[] = array(
                                            'idTrabajador' => $trabajador->id,
                                            'idHaber' => 3,
                                            'monto' => $colacion,
                                            'moneda' => $ficha->moneda_colacion,
                                            'proporcional' => $ficha->proporcional_colacion ? true : false,
                                            'desde' => $ficha->fecha,
                                            'hasta' => NULL
                                        );
                                        $colacionIguales = false;
                                    }else{
                                        echo 'Iguales<br />';
                                    }
                                }
                                if($movilizacionAnterior==101010101010101010){
                                    if($movilizacion>0){
                                        echo 'Movilizacion inicial: ' . $movilizacion. '<br />';
                                        $movilizaciones[] = array(
                                            'idTrabajador' => $trabajador->id,
                                            'idHaber' => 4,
                                            'monto' => $movilizacion,
                                            'moneda' => $ficha->moneda_movilizacion,
                                            'proporcional' => $ficha->proporcional_movilizacion ? true : false,
                                            'desde' => $ficha->fecha,
                                            'hasta' => NULL
                                        );
                                        $movilizacionInicial = true;
                                    }else{
                                        echo 'Sin Movilizacion<br />';
                                    }                                    
                                }else{
                                    if($movilizacionAnterior!=$movilizacion){
                                        echo 'Movilizacion: anterior ' . $movilizacionAnterior . ' - actual ' . $movilizacion . '<br />';
                                        $movilizaciones[] = array(
                                            'idTrabajador' => $trabajador->id,
                                            'idHaber' => 4, 
                                            'monto' => $movilizacion,
                                            'moneda' => $ficha->moneda_movilizacion,
                                            'proporcional' => $ficha->proporcional_movilizacion ? true : false,
                                            'desde' => $ficha->fecha,
                                            'hasta' => NULL
                                        );
                                        $movilizacionIguales = false;
                                    }else{
                                        echo 'Iguales<br />';
                                    }
                                }
                                if($viaticoAnterior==101010101010101010){
                                    if($viatico>0){
                                        echo 'Viatico inicial: ' . $viatico . '<br />';
                                        $viaticos[] = array(
                                            'idTrabajador' => $trabajador->id,
                                            'idHaber' => 5,
                                            'monto' => $viatico,
                                            'moneda' => $ficha->moneda_viatico,
                                            'proporcional' => $ficha->proporcional_viatico ? true : false,
                                            'desde' => $ficha->fecha,
                                            'hasta' => NULL
                                        );
                                        $viaticoInicial = true;
                                    }else{
                                        echo 'Sin Viatico<br />';
                                    }                                    
                                }else{
                                    if($viaticoAnterior!=$viatico){
                                        echo 'Viatico: anterior ' . $viaticoAnterior . ' - actual ' . $viatico . '<br />';
                                        $viaticos[] = array(
                                            'idTrabajador' => $trabajador->id,
                                            'idHaber' => 5,
                                            'monto' => $viatico,
                                            'moneda' => $ficha->moneda_viatico,
                                            'proporcional' => $ficha->proporcional_viatico ? true : false,
                                            'desde' => $ficha->fecha,
                                            'hasta' => NULL
                                        );
                                        $viaticoIguales = false;
                                    }else{
                                        echo 'Iguales<br />';
                                    }
                                }
                                $colacionAnterior = $colacion;
                                $movilizacionAnterior = $movilizacion;
                                $viaticoAnterior = $viatico;
                                $fechaAnterior = $fecha;
                            }                            
                        }
                        if($colaciones){
                            $fechaAnterior = NULL;
                            echo '<br /><b>Colaciones: </b><br />';   
                            
                            if($colacionIguales){
                                if($colacionInicial){
                                    echo '<b>Siempre iguales</b><br />';
                                    $colaciones[0]['desde'] = NULL;
                                }else{
                                    echo '<b>Iguales</b><br />';
                                }
                            }else{
                                foreach($colaciones as $index => $colacion){                                    
                                    if($index && isset($colaciones[($index - 1)])){
                                        $hasta = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($colacion['desde'])));
                                        $colaciones[($index - 1)]['hasta'] = $hasta;
                                    }
                                    if($fechaAnterior){
                                        $hasta = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fechaAnterior)));
                                        $colacion['hasta'] = $hasta;                                    
                                    }
                                    if(($index + 1)==count($colaciones)){
                                        $colacion['hasta'] = NULL;
                                    }                                    
                                    $fechaAnterior = $colacion['desde'];
                                    if($index==0 && $colacionInicial){
                                        $colaciones[$index]['desde'] = NULL;
                                    }
                                    if($colacion['monto']==0){
                                        unset($colaciones[$index]);
                                    }
                                }                                    
                            }        
                            print_r($colaciones);
                            echo '<br />';
                        }
                        if($movilizaciones){
                            $fechaAnterior = NULL;
                            echo '<br /><b>Movilizaciones: </b><br />';
                            
                            if($movilizacionIguales){
                                if($movilizacionInicial){
                                    echo '<b>Siempre iguales</b><br />';
                                    $movilizaciones[0]['desde'] = NULL;
                                }else{
                                    echo '<b>Iguales</b><br />';
                                }
                            }else{
                                foreach($movilizaciones as $index => $movilizacion){
                                    if($index && isset($movilizaciones[($index - 1)])){
                                        $hasta = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($movilizacion['desde'])));
                                        $movilizaciones[($index - 1)]['hasta'] = $hasta;
                                    }
                                    if($fechaAnterior){
                                        $hasta = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fechaAnterior)));
                                        $movilizacion['hasta'] = $hasta;                                    
                                    }
                                    if(($index + 1)==count($colaciones)){
                                        $movilizacion['hasta'] = NULL;
                                    }                                    
                                    $fechaAnterior = $movilizacion['desde'];
                                    if($movilizacion['monto']==0){
                                        unset($movilizaciones[$index]);
                                    }
                                }                                    
                            } 
                            print_r($movilizaciones);
                            echo '<br />';
                        }
                        if($viaticos){
                            $fechaAnterior = NULL;
                            echo '<br /><b>Viaticos: </b><br />';
                            
                            if($viaticoIguales){
                                if($viaticoInicial){
                                    echo '<b>Siempre iguales</b><br />';
                                    $viaticos[0]['desde'] = NULL;
                                }else{
                                    echo '<b>Iguales</b><br />';
                                }
                            }else{
                                foreach($viaticos as $index => $viatico){
                                    if($index && isset($viaticos[($index - 1)])){
                                        $hasta = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($viatico['desde'])));
                                        $viaticos[($index - 1)]['hasta'] = $hasta;
                                    }
                                    if($fechaAnterior){
                                        $hasta = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fechaAnterior)));
                                        $viatico['hasta'] = $hasta;                                    
                                    }
                                    if(($index + 1)==count($viaticos)){
                                        $viatico['hasta'] = NULL;
                                    }
                                    
                                    $fechaAnterior = $viatico['desde'];
                                    if($viatico['monto']==0){
                                        unset($viaticos[$index]);
                                    }
                                }                                    
                            } 
                            print_r($viaticos);
                            echo '<br />';
                        }                        
                    }
                    echo '<br /><h1>Inserts:</h1><br />';
                    if(count($colaciones)){
                        foreach($colaciones as $colacion){
                            echo $colacion['idTrabajador'] . '<br />';
                        }
                    }
                    if(count($movilizaciones)){
                        foreach($movilizaciones as $movilizacion){
                            echo $movilizacion['idTrabajador'] . '<br />';
                        }
                    }
                    if(count($viaticos)){
                        foreach($viaticos as $viatico){
                            echo $viatico['idTrabajador'] . '<br />';
                        }
                    }
                    
                    if(count($colaciones)){
                        foreach($colaciones as $colacion){
                            $haber = new Haber();
                            $haber->sid = Funciones::generarSID();
                            $haber->trabajador_id = $colacion['idTrabajador'];
                            $haber->tipo_haber_id = $colacion['idHaber'];
                            $haber->mes_id = NULL;
                            $haber->moneda = $colacion['moneda'];
                            $haber->monto = $colacion['monto'];
                            $haber->por_mes = 0;
                            $haber->rango_meses = 0;
                            $haber->permanente = 1;
                            $haber->todos_anios = 0;
                            $haber->todos_anios = 0;
                            $haber->mes = NULL;
                            $haber->desde = $colacion['desde'];
                            $haber->hasta = $colacion['hasta'];
                            $haber->save();
                        }
                    }
                    if(count($movilizaciones)){
                        foreach($movilizaciones as $movilizacion){
                            $haber = new Haber();
                            $haber->sid = Funciones::generarSID();
                            $haber->trabajador_id = $movilizacion['idTrabajador'];
                            $haber->tipo_haber_id = $movilizacion['idHaber'];
                            $haber->mes_id = NULL;
                            $haber->moneda = $movilizacion['moneda'];
                            $haber->monto = $movilizacion['monto'];
                            $haber->por_mes = 0;
                            $haber->rango_meses = 0;
                            $haber->permanente = 1;
                            $haber->todos_anios = 0;
                            $haber->todos_anios = 0;
                            $haber->mes = NULL;
                            $haber->desde = $movilizacion['desde'];
                            $haber->hasta = $movilizacion['hasta'];
                            $haber->save();
                        }
                    }
                    if(count($viaticos)){
                        foreach($viaticos as $viatico){
                            $haber = new Haber();
                            $haber->sid = Funciones::generarSID();
                            $haber->trabajador_id = $viatico['idTrabajador'];
                            $haber->tipo_haber_id = $viatico['idHaber'];
                            $haber->mes_id = NULL;
                            $haber->moneda = $viatico['moneda'];
                            $haber->monto = $viatico['monto'];
                            $haber->por_mes = 0;
                            $haber->rango_meses = 0;
                            $haber->permanente = 1;
                            $haber->todos_anios = 0;
                            $haber->todos_anios = 0;
                            $haber->mes = NULL;
                            $haber->desde = $viatico['desde'];
                            $haber->hasta = $viatico['hasta'];
                            $haber->save();
                        }
                    }
                }
            }else{
                echo "Sin Trabajadores <br />";
            }
        }
        
    }else{
        echo "Sin Empresas";
    }
});

Route::get('crear-usuarios', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $empleados = array();
            Config::set('database.default', $empresa->base_datos);
            DB::table('usuarios')->delete();
            DB::table('permisos')->delete();
            $empleados = Trabajador::all();
            foreach($empleados as $empleado){
                $ultimaFicha = $empleado->ultimaFicha();
                if($ultimaFicha){
                    if($ultimaFicha->estado!='En Creación'){
                        $user = new Usuario();
                        $user->sid = Funciones::generarSID();
                        $user->funcionario_id = $empleado->id;
                        $user->username = $empleado->rut;
                        $pass = $user->generarClave();
                        $user->password = Hash::make($pass);
                        $user->activo = 2;
                        $user->save();

                        $permiso = new Permiso();
                        $permiso->usuario_id = $user->id;
                        $permiso->documentos_empresa = 1;
                        $permiso->cartas_notificacion = 1;
                        $permiso->certificados = 1;
                        $permiso->liquidaciones = 1;
                        $permiso->solicitudes = 1;
                        $permiso->save();

                        echo 'user_id : ' . $empleado->rut . '<br />';
                        echo 'empresa_id : ' . $empresa->id . '<br />';
                        echo 'pass : ' . $pass . '<br /><br />';
                    }
                }
            }
        }
        
    }else{
        echo "Sin Usuarios";
    }
});

Route::get('sis', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $empleados = array();
            Config::set('database.default', $empresa->base_datos);
            $empleados = Trabajador::all();
            echo '<h1>' . $empresa->razon_social . '</h1>';
            $count = 0;
            foreach($empleados as $empleado){
                $ultimaFicha = $empleado->ultimaFicha();
                if($ultimaFicha){
                    if($ultimaFicha->estado!='En Creación'){
                        $liquidaciones = $empleado->liquidaciones;

                        if($liquidaciones->count()){
                            $sis = false;
                            foreach($liquidaciones as $index => $liquidacion){
                                if($liquidacion->mes>='2018-01-01'){
                                    if($liquidacion->detalleAfp){
                                        if($liquidacion->detalleAfp->sis==0){
                                            $sc = $liquidacion->detalleSeguroCesantia;
                                            if(!$sis){
                                                $count++;
                                                echo '<br />RUT: ' . Funciones::formatear_rut($empleado->rut) . '<br />';
                                                echo 'Nombre: ' . $ultimaFicha->nombreCompleto() . '<br />';
                                                echo 'AFP: ' . $liquidacion->detalleAfp->afp->glosa . '<br />';
                                                if($sc){
                                                    echo 'SC: ' . $sc->afp->glosa . '<br />';
                                                }else{
                                                    $seguro = $ultimaFicha->seguro_desempleo ? 'Sí' : 'Sin Seguro';
                                                    echo 'SC: ' . $seguro . '<br />';
                                                }
                                                echo 'Liquidaciones:<br /><br />';
                                            }
                                            echo '-Liquidacion ' . $liquidacion->mes . '<br />';
                                            echo 'Ingreso: ' . $liquidacion->trabajador_fecha_ingreso . '<br />';
                                            echo 'Dias trabajados: ' . $liquidacion->dias_trabajados . '<br />';
                                            echo 'AFP: ' . Funciones::formatoPesos($liquidacion->detalleAfp->cotizacion) . '<br />';
                                            echo 'SIS: ' . Funciones::formatoPesos($liquidacion->detalleAfp->sis) . '<br />';
                                            if($sc){
                                                echo 'SC: ' . Funciones::formatoPesos($sc->aporte_empleador) . '<br />';
                                            }

                                            $mutual = $liquidacion->detalleMutual;
                                            if($mutual){
                                                echo 'Mutual: ' . Funciones::formatoPesos($mutual->cotizacion_accidentes) . '<br />';
                                            }
                                            $sis = true;
                                        }
                                    }
                                    /*$mutual = $liquidacion->detalleMutual;
                                    if($mutual){
                                        if($mutual->cotizacion_accidentes==0){
                                            if(!$sis){
                                                $count++;
                                                echo '<br />RUT: ' . Funciones::formatear_rut($empleado->rut) . '<br />';
                                                echo 'Nombre: ' . $ultimaFicha->nombreCompleto() . '<br />';
                                                echo 'Liquidaciones:<br />';
                                            }
                                            echo '-Liquidacion ' . $liquidacion->mes . '<br />';
                                            echo 'Ingreso: ' . $liquidacion->trabajador_fecha_ingreso . '<br />';
                                            echo 'Dias trabajados: ' . $liquidacion->dias_trabajados . '<br />';
                                            echo 'AFP: ' . Funciones::formatoPesos($liquidacion->detalleAfp->cotizacion) . '<br />';
                                            echo 'SIS: ' . Funciones::formatoPesos($liquidacion->detalleAfp->sis) . '<br />';
                                            $sc = $liquidacion->detalleSeguroCesantia;
                                            if($sc){
                                                echo 'SC: ' . Funciones::formatoPesos($sc->aporte_empleador) . '<br />';
                                            }


                                            if($mutual){
                                                echo 'Mutual: ' . Funciones::formatoPesos($mutual->cotizacion_accidentes) . '<br />';
                                            }
                                            $sis = true;
                                        }
                                    }*/
                                }
                            }
                        }else{
                            echo 'Sin liquidaciones<br />';
                        }

                    }
                }
            }
            echo '<br />Total: ' . $count . '<br />';
        }
        
    }else{
        echo "Sin Empresas";
    }
});


Route::get('crear-usuario', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            echo $empresa->rut;
            if($empresa->rut=='765748798'){
                echo '<b>' . $empresa->rut . '</b>';
                Config::set('database.default', $empresa->base_datos);
                $empleados = DB::table('trabajadores')->whereIn('id', [])->get();
                print_r($empleados);
                foreach($empleados as $empleado){
                    
                    $ultimaFicha = FichaTrabajador::where('trabajador_id', $empleado->id)->orderBy('id', 'DESC')->first();
                    if($ultimaFicha->estado!='En Creación'){
                        
                        $user = new Usuario();
                        $user->sid = Funciones::generarSID();
                        $user->funcionario_id = $empleado->id;
                        $user->username = $empleado->rut;
                        $pass = $user->generarClave();
                        $user->password = Hash::make($pass);
                        $user->activo = 2;
                        $user->save();

                        $permiso = new Permiso();
                        $permiso->usuario_id = $user->id;
                        $permiso->documentos_empresa = 1;
                        $permiso->cartas_notificacion = 1;
                        $permiso->certificados = 1;
                        $permiso->liquidaciones = 1;
                        $permiso->solicitudes = 1;
                        $permiso->save();

                        echo 'user_id : ' . $empleado->rut . '<br />';
                        echo 'empresa_id : ' . $empresa->id . '<br />';
                        echo 'pass : ' . $pass . '<br /><br />';
                    }
                }
            }
        }
        
    }else{
        echo "Sin Usuarios";
    }
});

Route::get('session', function(){

    session_destroy();
});

Route::get('crear-cuentas-centros-costo', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            if($empresa->razon_social=='Comercial e Importadora Audiomusica Spa'){                
                Config::set('database.default', $empresa->base_datos);
                $aportes = Aporte::all();
                $haberes = TipoHaber::all();
                $descuentos = TipoDescuento::all();
                $centrosCosto = CentroCosto::all();
                foreach($aportes as $aporte){
                    if($aporte->cuenta_id){
                        foreach($centrosCosto as $centroCosto){
                            if($centroCosto->id==3){
                                DB::table('cuenta_centro_costo')->insert(array(
                                    array( 'centro_costo_id' => $centroCosto->id, 'concepto' => 'aporte', 'concepto_id' => $aporte->id, 'cuenta_id' => $aporte->cuenta_id)
                                ));
                            }
                        }
                    }
                }
                foreach($haberes as $haber){
                    if($haber->cuenta_id){
                        foreach($centrosCosto as $centroCosto){
                            if($centroCosto->id==3){
                                DB::table('cuenta_centro_costo')->insert(array(
                                    array( 'centro_costo_id' => $centroCosto->id, 'concepto' => 'haber', 'concepto_id' => $haber->id, 'cuenta_id' => $haber->cuenta_id)
                                ));
                            }
                        }
                    }
                }
                foreach($descuentos as $descuento){
                    if($descuento->cuenta_id){
                        foreach($centrosCosto as $centroCosto){
                            if($centroCosto->id==3){
                            DB::table('cuenta_centro_costo')->insert(array(
                                array( 'centro_costo_id' => $centroCosto->id, 'concepto' => 'descuento', 'concepto_id' => $descuento->id, 'cuenta_id' => $descuento->cuenta_id)
                            ));
                            }
                        }
                    }
                }
            }
        }
        
    }else{
        echo "Sin Usuarios";
    }
});

Route::get('crear-cuentas', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            Config::set('database.default', $empresa->base_datos);
            $aportes = Aporte::all();
            $haberes = TipoHaber::all();
            $descuentos = TipoDescuento::all();
            foreach($aportes as $aporte){
                $aporte->cuenta_id = 176;
                $aporte->save();
            }
            foreach($haberes as $haber){
                $haber->cuenta_id = 172;
                $haber->save();
            }
            foreach($descuentos as $descuento){
                $descuento->cuenta_id = 176;
                $descuento->save();
            }
        }
        
    }else{
        echo "Sin Usuarios";
    }
});

Route::get('eliminar-trabajadores', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            Config::set('database.default', $empresa->base_datos);
            $trabajadores = DB::table('trabajadores')->get();
            echo "<br /><br />Empresa: ".$empresa->razon_social."<br />";
            if(count($trabajadores)){                
                foreach($trabajadores as $trabajador){
                    if($trabajador->deleted_at){
                        echo '<br />user_id : ' . $trabajador->rut;
                        DB::table('trabajadores')->where('id', $trabajador->id)->delete();
                    }
                }
            }else{
                echo "<br />Sin Trabajadores";
            }
        }
        
    }else{
        echo "<br />Sin Empresas";
    }
});

Route::get('crear-mutual-ccfa', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            Config::set('database.default', $empresa->base_datos);
            $anios = DB::table('anios_remuneraciones')->get();
            DB::table('mutuales')->delete();
            DB::table('cajas')->delete();
            echo "<br /><br />Empresa: ".$empresa->razon_social."<br />";
            if(count($anios)){                
                foreach($anios as $anio){
                    echo "Anios: ".$anio->anio."<br />";
                    DB::table('mutuales')->insert(array(
                        array( 'mutual_id' => $empresa->mutual_id, 'tasa_fija' => $empresa->tasa_fija_mutual, 'tasa_adicional' => $empresa->tasa_adicional_mutual, 'codigo' => $empresa->codigo_mutual, 'anio_id' => $anio->id )
                    ));
                    DB::table('cajas')->insert(array(
                        array( 'caja_id' => $empresa->caja_id, 'codigo' => $empresa->codigo_mutual, 'anio_id' => $anio->id )
                    ));
                }
            }else{
                echo "<br />No hay Años";
            }
        }
        
    }else{
        echo "<br />Sin Empresas";
    }
});

Route::get('crear-codigos-secciones', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $secciones = array();
            Config::set('database.default', $empresa->base_datos);
            $secciones = Seccion::all();
            if($secciones->count()){
                echo '<br />Empresa : ' . $empresa->razon_social . '<br />';
                foreach($secciones as $seccion){
                    if(!$seccion->codigo){
                        $seccion->codigo = $seccion->nombre;
                        $seccion->save();

                        echo 'Sección : ' . $seccion->nombre . '<br />';
                        echo 'Código : ' . $seccion->codigo . '<br />';
                    }
                }
            }
        }
        
    }else{
        echo "No empresas";
    }
});

Route::get('crear-vacaciones', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $empleados = array();
            Config::set('database.default', $empresa->base_datos);
            DB::table('vacaciones')->truncate();
            echo '<br />Empresa : ' . $empresa->razon_social . '<br /><br />';
            $empleados = Trabajador::all();
            foreach($empleados as $empleado){
                $ficha = $empleado->ultimaFicha();
                if($ficha){
                    $fichas = FichaTrabajador::where('trabajador_id', $empleado->id)->get();
                    foreach($fichas as $f){
                        $f->vacaciones = 0;
                        $f->save();
                    }
                    $empleado->recalcularVacaciones(0);
                    echo 'Trabajador : ' . $ficha->nombreCompleto() . '<br />';
                }                
            }
        }
        
    }else{
        echo "Sin Empleados";
    }
});

Route::get('recalcular-vacaciones', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $empleados = array();
            Config::set('database.default', $empresa->base_datos);
            DB::table('vacaciones')->truncate();
            echo '<br />Empresa : ' . $empresa->razon_social . '<br /><br />';
            $empleados = Trabajador::all();
            foreach($empleados as $empleado){
                $ficha = $empleado->ultimaFicha();
                if($ficha){
                    $dias = $ficha->vacaciones ? $ficha->vacaciones : 0;                    
                    $calcularDesde = $ficha->calculo_vacaciones;
                    if($calcularDesde=='p'){
                        $primerMes = MesDeTrabajo::orderBy('mes')->first();
                        $desde = $primerMes->mes;
                    }else{
                        $desde = Funciones::primerDia($ficha->fecha_ingreso);
                    }
                    $empleado->recalcularVacaciones($dias, $desde);
                    echo 'Trabajador : ' . $ficha->nombreCompleto() . '<br />';
                }                
            }
        }        
    }else{
        echo "Sin Empleados";
    }
});

Route::get('corregir-observaciones', function(){

    Config::set('database.default', 'principal' );
    $empresas = Empresa::all();
    
    if($empresas->count()){
        foreach($empresas as $empresa){
            $empleados = array();
            Config::set('database.default', $empresa->base_datos);
            
            echo '<br />Empresa : ' . $empresa->razon_social . '<br /><br />';
            
            $observaciones = LiquidacionObservacion::where('periodo', '2018-01-01')->get();
            foreach($observaciones as $observacion){
                $ficha = FichaTrabajador::find($observacion->trabajador_id);
                if($ficha){
                    if($ficha->trabajador_id==$observacion->trabajador_id){
                        echo 'Iguales<br />';
                    }else{
                        $observacion->trabajador_id = $ficha->trabajador_id;
                        $observacion->save();
                        echo $ficha->nombres . '<br />';
                        echo $observacion->trabajador_id . '<br />';
                        echo $ficha->trabajador_id . '<br />';
                    }
                }else{
                    echo $observacion->trabajador_id;
                    echo '<br /> Sin Ficha <br />';
                }
            }
        }
        
    }else{
        echo "Sin Empleados";
    }
});


Route::get('empresas/datos-empresa/portal-trabajador/{portal}', function($portal){
    $datos=array(
        'empresa' => '',
        'logo' => ''
    );
    $empresa = Empresa::where('portal', $portal)->first();
    if($empresa){
        $datos=array(
            'empresa' => $empresa->razon_social,
            'logo' => $empresa->logo
        );
    }
    return $datos;
});

Route::get('empresas/lista-empresas/json', 'EmpresasController@lista_empresas_select');

Route::group(array('before'=>'auth_ajax'), function() {

    Route::post('cambiar-empresa', function(){
        $empresa_id = Input::get('empresa');
        if(!$empresa_id){
            return Response::json(array("success" => true, 'accesos' => array()));
        }
        $urlActual = Input::get('actual');
        $empresa = Empresa::find($empresa_id);        
        if($empresa){
            $recargar=false;                        
            \Session::put('basedatos', $empresa->base_datos);
            Config::set('database.default', $empresa->base_datos);
            $empresa->ultimoMes = $empresa->ultimoMes();
            $empresa->primerMes = $empresa->primerMes();
            \Session::put('empresa', $empresa);
            $a = \Session::get('empresa');
            Empresa::configuracion();
            $menuController = new MenuController();
            // se carga el menu con los permisos permitidos para la empresa
            if( Auth::usuario()->user()->id > 1 ){
                $MENU_USUARIO = $menuController->generarMenuSistema($empresa_id, false);
                // si la posicion actual esta en el menu de la empresa se realiza una recarga
                // de lo contrario se deriva al inicio
                $url = "#".substr($urlActual, 1, 1000);
                $recargar = Auth::usuario()->user()->comprobarUrlMenuEmpresa($url, $empresa_id);
                $recargar=true;

            }else if(Auth::usuario()->user()->id == 1){
                $MENU_USUARIO = $menuController->generarMenuSistema($empresa_id, false);
                $recargar=true;
            }

            $anioActual=date("Y");
            $varSistema = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
            if($varSistema){
                $anioActual = $varSistema->valor1;
            }
            
            $mesActual = MesDeTrabajo::selectMes();
            \Session::put('mesActivo', $mesActual);
            
            $fecha = \Session::get('mesActivo')->fechaRemuneracion;

            $indicadores = ValorIndicador::valorFecha($fecha);
            
            $empresaActual = array(
                'id' => $empresa->id,
                'e' => $a,
                'logo' => $empresa->logo? "/stories/".$empresa->logo : "images/dashboard/EMPRESAS.png",
                'empresa' => $empresa->razon_social,
                'mesDeTrabajo' => $mesActual,
                'ultimoMes' => $empresa->ultimoMes,
                'primerMes' => $empresa->primerMes,
                'rutFormato' => $empresa->rut_formato(),
                'rut' => $empresa->rut,
                'direccion' => $empresa->direccion,
                'gratificacion' => $empresa->gratificacion,
                'tipoGratificacion' => $empresa->tipo_gratificacion,
                'isMutual' => Empresa::isMutual(),
                'isCaja' => Empresa::isCaja(),
                'cme' => $empresa->cme ? true : false,
                'impuestoUnico' => $empresa->impuesto_unico,
                'centroCosto' => array(
                    'isCentroCosto' => $empresa->centro_costo ? true : false,
                    'niveles' => $empresa->niveles_centro_costo
                ),
                'comuna' => array(
                    'id' => $empresa->comuna->id,
                    'nombre' => $empresa->comuna->comuna,
                    'provincia' => $empresa->comuna->provincia->provincia,
                    'localidad' => $empresa->comuna->localidad(),
                    'domicilio' => $empresa->domicilio()
                )
            );
            
            if($mesActual){
                $listaMesesDeTrabajo = MesDeTrabajo::listaMesesDeTrabajo();
            }

            return Response::json(array('empresa' => $empresaActual, 'listaMesesDeTrabajo' => $listaMesesDeTrabajo, 'success' => true, "menu" => $MENU_USUARIO, "recargar" => $recargar, "anioActual" => $anioActual, 'indicadores' => $indicadores ));
        }else{  
            return Response::json(array('success' => false));
        }
    });
    
    Route::get('mes-de-trabajo/cuentas/obtener', function(){
        
        $empresa = \Session::get('empresa');
        $isCME = $empresa->cme ? true : false;
        $datosConexionCME = array();
        if($isCME){
            //$rutEmpresa = $empresa->rut;
            $rutEmpresa = 111111111;
            $client = new Client(); //GuzzleHttp\Client
            $result = $client->post('http://demo.cme-es.com/rest/rrhh/plan-de-cuentas', [
            //$result = $client->post('http://demo.cme-es.com/empresas', [
                'auth' => ['restfull', '1234'],
                'json' => [
                    'rutEmpresa' => $rutEmpresa
                ],
                'debug' => false
            ]);
            
            $datosConexionCME = $result->json(); 
        }

        
        return Response::json($datosConexionCME);     
    });
    
    Route::get('trabajadores/cuentas/obtener', function(){
        $client = new Client(); //GuzzleHttp\Client
        $result = $client->get('http://demo.rrhh-es.com/centralizacion1/cuentas/obtener');
        
        $datosConexionCME = $result->json(); 
        
        return Response::json($datosConexionCME);     
    });
    
    Route::get('centralizacion/cuentas/obtener', function(){
        
        $lista = array();
        /*$data = Input::all();
        $idMes = $data['fechaRemuneracion'];*/
        $lista = Trabajador::centralizar('2017/11/30', 2);
        
        return Response::json($lista);     
    });
    
    
    Route::get('cuentas/cuentas/obtener', function(){
        
        $lista = array();
        /*$data = Input::all();
        $idMes = $data['fechaRemuneracion'];*/
        $mes = MesDeTrabajo::where('fecha_remuneracion', '2017/06/31')->first();
        $liquidaciones = Liquidacion::where('mes', $mes['mes'])->orderBy('trabajador_apellidos')->get();
        
        foreach($liquidaciones as $liquidacion){
            $detalles = $liquidacion->detallesLiquidacion();
            $lista[] = array(
                'idTrabajador' => $liquidacion->trabajador_id,
                'rut' => $liquidacion->trabajador_rut,
                'nombreCompleto' => $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos,
                'sueldoBase' => $liquidacion->sueldo_base,
                'sueldo' => $liquidacion->sueldo,
                'haberes' => $detalles['haberes'],
                'descuentos' => $detalles['descuentos'],
                'aportes' => $detalles['aportes'],
                'rentaImponible' => $liquidacion->renta_imponible,
                'sueldoLiquido' => $liquidacion->sueldo_liquido
            );
        }
        
        return Response::json($lista);     
    });
    
    Route::get('empresas/sistemas/obtener', function(){
        
        $client = new Client(); //GuzzleHttp\Client
        //$result = $client->post('http://demo.cme-es.com/empresas/id', [
        $result = $client->get('http://demo.cme-es.com/empresas', [
            'auth' => ['restfull', '1234'],
            'debug' => false
        ]);

        $datosConexionCME = $result->json(); 
        return Response::json($datosConexionCME);     
    });
    
    Route::get('crear-empresa', function(){
        
        $whmusername = "root";

# The contents of /root/.accesshash
                    $hash = "3a608b57f517be53f717b12222efe1f6
1b82d5d111ebaf26274527036fa0d675
a2e617ecd2dff57e95cab34231d49e9d
2d6f70dea85f0c08e49dfe133156f24c
644aa27093159c8c1328484ba0a5990e
7df86fbb522b82a15d0cc6bea8d6c366
3c1f370cd08fc988bd3c672ae7320584
45140b665cbfaf190d43c8529d42c2a2
ebc03fd38180c2ec0eed6a9db51dc1e9
daba09c12a350aaf287533c06a80cc04
733196767005fb5c0dd9608364f9910c
06f7ed9aed25b349d23b977f9bd2d273
f0f6af85c4551d743b4b89cf98ff384b
070d88d3d89803c0515601ffb777681e
fe7d4fa37ade0cb94333bfd8638b67db
3c09863eb0bc1afa5d32e8e3c33111d9
96f36670193b91d3811a121493755c20
81ff34c02ba0f9a1e2db80b98449deb4
720b524c3c2200a270e35d0343c633c0
8f247d0e739eca4f9c60ea1a723847bd
935aaad733a86309f06a61e80bc84cbc
fc735006727e13fae7a74a737e6958ff
f8a386b8bff90190c9e66552d41b5793
ba79921a196f78b11dc2842b81a64df3
c85699bdf0e10c5c0039708d4a693969
72b90b49846c65de47c5656ac9b6891a
878abd455bcbef7e2c6fd8a2a86f0995
62ec65222dfb818004f8e49d0c8fd9ee
c139f46406b37dedd4062fea30a5ccec
3355afed2b2d8d597f1ac8fd178892ef
";

                    $query1 = "https://184.154.202.34:2087/json-api/cpanel?user=easysystems&cpanel_jsonapi_module=Mysql&cpanel_jsonapi_func=adddb&cpanel_jsonapi_apiversion=1&arg-0=easysyst_123456";
                    $query2 = "https://184.154.202.34:2087/json-api/cpanel?user=easysystems&cpanel_jsonapi_module=Mysql&cpanel_jsonapi_func=adduserdb&cpanel_jsonapi_apiversion=1&arg-0=easysyst_123456&arg-1=easysyst_rrhh&arg-2=all";
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

                    $header[0] = "Authorization: WHM $whmusername:" . preg_replace("'(\r|\n)'", "", $hash);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($curl, CURLOPT_URL, $query1);
                    $result = curl_exec($curl);
                    if ($result == false) {
                        error_log("curl_exec threw error " . curl_error($curl) . " for $query1");
                        die("curl_exec threw error " . curl_error($curl) . " for $query1");
                    }

                    curl_setopt($curl, CURLOPT_URL, $query2);
                    $result = curl_exec($curl);

                    if ($result == false) {
                        error_log("curl_exec threw error " . curl_error($curl) . " for $query2");
                        die("curl_exec threw error " . curl_error($curl) . " for $query2");
                    }
                    curl_close($curl);
        


    

 
    });
                
    Route::post('cambiar-mes-de-trabajo', function(){
        $mesActivo = Input::get('mes');
        $empresa_id = \Session::get('basedatos');
        $empresa = Empresa::where('base_datos', $empresa_id)->first();
        if($empresa){
            // si el anio seleccionado esta permitido
            $listaMesesDeTrabajo=array();
            Config::set('database.default', $empresa->base_datos);
            $varSistema = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
            if($varSistema){
                $listaMesesDeTrabajo=MesDeTrabajo::listaMesesDeTrabajo();
            }
            $empresa->ultimoMes = $empresa->ultimoMes();
            $empresa->primerMes = $empresa->primerMes();
            $mesActual = MesDeTrabajo::selectMes($mesActivo['id']);
            \Session::put('mesActivo', $mesActual);
            $fecha = \Session::get('mesActivo')->fechaRemuneracion;
            $indicadores = ValorIndicador::valorFecha($fecha);                

            if($indicadores){
                $uf = $indicadores->uf;
                $utm = $indicadores->utm;
                $uta = $indicadores->uta;
            }else{
                $uf = null;
                $utm = null;
                $uta = null;
            }
            
            $listaMesesDeTrabajo=MesDeTrabajo::listaMesesDeTrabajo();

            $respuesta = array(
                'success' => true,
                'recargar' => true,
                'mesActual' => $mesActual,
                'ultimoMes' => $empresa->ultimoMes,
                'primerMes' => $empresa->primerMes,
                'uf' => $uf,
                'utm' => $utm,
                'uta' => $uta,
                'listaMesesDeTrabajo' => $listaMesesDeTrabajo
            );

            return Response::json($respuesta);
        }else{
            return Response::json(array('success' => false, 'mensaje' => 'La referencia a la base de datos de la empresa se ha perdido'));
        }
    });

    Route::get('inicio', function(){
        // comprobar sesion activa
        return Response::json(array('success' => true));
    });

    /* MENU DEL SISTEMA */
    Route::resource('menu', 'MenuController');
    Route::get('menu/opciones-formulario/obtener', 'MenuController@objetosFormulario');
    Route::get('menu/perfiles-usuario/obtener', 'MenuController@menu_perfil_usuario');

    
    /*  VARIABLES DEL SISTEMA   */
    Route::resource('variables-sistema', 'VariablesSistemaController');
    Route::get('empresas/configuracion/obtener', 'VariablesSistemaController@configuracion');
    
    /* MODIFICACION DE CONTRASENA Y VISTA A DATOS DEL PERFIL DE USUARIO */
    Route::get('misdatos', 'PerfilController@index');
    Route::post('misdatos', 'PerfilController@actualizar_password');
    
    /* MODIFICACION DE CONTRASENA Y VISTA A DATOS DEL PERFIL DE USUARIO EMPLEADOS */
    Route::post('empleados/misdatos/cambiar', 'EmpleadosController@actualizar_password');

});


Route::group(array('before'=>'auth_ajax'), function() {
    /*  EMPRESAS    */
    Route::resource('empresas', 'EmpresasController');
    Route::get('empresas/exportar/excel/{version}', "EmpresasController@exportar_excel");
    Route::get('empresa/notificaciones', "EmpresasController@notificaciones");
    Route::post('empresas/habilitar/cambiar', "EmpresasController@habilitar");
    Route::post('empresas/configuracion/cambiar', "EmpresasController@cambiarConfiguracion");
    Route::post('empresas/valor-configuracion/cambiar', "EmpresasController@cambiarValorConfiguracion");

    /* PROVINCIAS Y COMUNAS */
    Route::get('provincias/listado/{region}', 'ProvinciaController@ajaxSelectListaProvincias');
    Route::get('comunas/listado/{provincia}', 'ComunaController@ajaxSelectListaComunas');
    Route::get('comunas/buscador/json', 'ComunaController@buscador_comunas');

    /*  PERFILES DE USUARIOS    */
    Route::resource('perfiles', 'PerfilesController');
    Route::get('perfiles/exportar/excel/{version}', "PerfilesController@exportar_excel");

    /*  FUNCIONARIOS    */
    Route::resource('usuarios', 'UsuariosController');
    Route::get('usuarios/opciones/formulario', 'UsuariosController@objetosFormulario');
    Route::get('usuarios/buscar-rut/{rut}', 'UsuariosController@buscar_rut');
    Route::get('usuarios/exportar/excel/{version}', "UsuariosController@exportar_excel");
    Route::get('usuarios/buscador/json', "UsuariosController@ajax_buscador_usuarios");
    Route::get('usuarios/buscar-todos/json', "UsuariosController@ajax_buscador_todos_usuarios");
    Route::get('usuarios/buscar-vendedor/json', "UsuariosController@ajax_buscador_vendedores");
    Route::get('usuarios/buscar-productManager/json', "UsuariosController@ajax_buscador_productManager");
    Route::get('usuarios/listado-vendedor/json', "UsuariosController@lista_vendedores");
    Route::get('usuarios/listado-productManager/json', "UsuariosController@lista_product_manager");

    /*  EMPLEADOS    */
    Route::resource('empleados', 'EmpleadosController');
    Route::post('empleados/permisos/cambiar', "EmpleadosController@cambiarPermisos");
    Route::post('empleados/permisos/cambiar-masivo', "EmpleadosController@cambiarPermisosMasivo");
    Route::post('empleados/portal/activar', "EmpleadosController@activarUsuario");
    Route::post('empleados/portal/activar-masivo', "EmpleadosController@activarMasivo");
    Route::post('empleados/portal/desactivar-masivo', "EmpleadosController@desactivarMasivo");
    Route::post('empleados/portal/reactivar', "EmpleadosController@reactivarUsuario");
    Route::post('empleados/portal/generar-clave', "EmpleadosController@generarClave");
    Route::post('empleados/portal/generar-clave-masivo', "EmpleadosController@generarClaveMasivo");

    /*  DEPARTAMENTOS   */
    Route::resource('departamentos', 'DepartamentosController');
    Route::get('departamentos/opciones/formulario', 'DepartamentosController@objetosFormulario');
    Route::get('departamentos/buscador/json', "DepartamentosController@ajax_buscador_departamentos");
    
    /*  JORNADAS    */
    Route::resource('jornadas', 'JornadasController');
    
    /*  TIPOS_CONTRATO    */
    Route::resource('tipos-contrato', 'TiposContratoController');
    
    /*  APORTES    */
    Route::resource('aportes', 'AportesController');
    Route::post('aportes/cuentas/actualizar', 'AportesController@updateCuenta');
    Route::post('aportes/cuentas-centros-costos/actualizar', 'AportesController@updateCuentaCentroCosto');
    Route::get('aportes/centro-costo/obtener/{sid}', 'AportesController@cuentaAporteCentroCosto');
    Route::post('aportes/cuentas-masivo/actualizar', 'AportesController@updateCuentaMasivo');
    
    /*  TRABAJADORES    */
    Route::resource('trabajadores', 'TrabajadoresController');
    Route::get('trabajadores/secciones/obtener', 'TrabajadoresController@secciones');
    Route::get('trabajadores/total-inasistencias/obtener', 'TrabajadoresController@trabajadoresInasistencias');
    Route::get('trabajadores/total-atrasos/obtener', 'TrabajadoresController@trabajadoresAtrasos');
    Route::get('trabajadores/total-licencias/obtener', 'TrabajadoresController@trabajadoresLicencias');
    Route::get('trabajadores/total-horas-extra/obtener', 'TrabajadoresController@trabajadoresHorasExtra');
    Route::get('trabajadores/total-prestamos/obtener', 'TrabajadoresController@trabajadoresPrestamos');
    Route::get('trabajadores/total-cargas-familiares/obtener', 'TrabajadoresController@trabajadoresCargas');
    Route::get('trabajadores/total-apvs/obtener', 'TrabajadoresController@trabajadoresApvs');
    Route::get('trabajadores/apvs/obtener/{sid}', 'TrabajadoresController@trabajadorApvs');
    Route::get('trabajadores/inasistencias/obtener/{sid}', 'TrabajadoresController@trabajadorInasistencias');
    Route::get('trabajadores/atrasos/obtener/{sid}', 'TrabajadoresController@trabajadorAtrasos');
    Route::get('trabajadores/licencias/obtener/{sid}', 'TrabajadoresController@trabajadorLicencias');
    Route::get('trabajadores/horas-extra/obtener/{sid}', 'TrabajadoresController@trabajadorHorasExtra');
    Route::get('trabajadores/prestamos/obtener/{sid}', 'TrabajadoresController@trabajadorPrestamos');
    Route::get('trabajadores/documentos/obtener/{sid}', 'TrabajadoresController@trabajadorDocumentos');
    Route::get('trabajadores/cargas-familiares/obtener/{sid}', 'TrabajadoresController@trabajadorCargas');
    Route::get('trabajadores/cargas-familiares-autorizar/obtener/{sid}', 'TrabajadoresController@trabajadorCargasAutorizar');
    Route::post('trabajadores/autorizar-cargas-familiares/generar', 'TrabajadoresController@trabajadorAutorizarCargas');
    Route::get('trabajadores/haberes/obtener/{sid}', 'TrabajadoresController@haberes');
    Route::get('trabajadores/descuentos/obtener/{sid}', 'TrabajadoresController@descuentos');
    Route::get('trabajadores/opciones/afps', 'TrabajadoresController@listaAfps');
    Route::get('trabajadores/input/obtener', 'TrabajadoresController@input');
    Route::get('trabajadores/input-activos/obtener', 'TrabajadoresController@inputActivos');
    Route::get('trabajadores/secciones/formulario', 'TrabajadoresController@seccionesFormulario');
    Route::get('trabajadores/seccion/obtener/{sid}', 'TrabajadoresController@seccion');
    Route::post('trabajadores/liquidacion/generar', 'TrabajadoresController@miLiquidacion');
    Route::post('trabajadores/f1887-trabajadores/generar', 'TrabajadoresController@generarF1887Trabajadores');
    Route::get('trabajadores/f1887/generar/{anio}', 'TrabajadoresController@generarF1887');
    Route::get('trabajadores/f1887/ver/{anio}', 'TrabajadoresController@verF1887');
    Route::get('trabajadores/ingresados/obtener', 'TrabajadoresController@ingresados');
    Route::get('trabajadores/archivo-previred/obtener', 'TrabajadoresController@archivoPrevired');
    Route::get('trabajadores/archivo-previred/descargar', 'TrabajadoresController@descargarPrevired');
    Route::get('trabajadores/trabajadores-finiquitos/obtener', 'TrabajadoresController@trabajadoresFiniquitos');
    Route::get('trabajadores/trabajadores-documentos/obtener', 'TrabajadoresController@trabajadoresDocumentos');
    Route::get('trabajadores/vigentes/obtener', 'TrabajadoresController@vigentes');
    Route::get('trabajadores/trabajadores-liquidaciones/obtener', 'TrabajadoresController@trabajadoresLiquidaciones');
    Route::get('trabajadores/trabajadores-f1887/obtener/{sid}', 'TrabajadoresController@trabajadoresF1887');
    Route::get('trabajadores/planilla-costo-empresa/obtener', 'TrabajadoresController@planillaCostoEmpresa');
    Route::get('trabajadores/trabajadores-cartas-notificacion/obtener', 'TrabajadoresController@trabajadoresCartasNotificacion');
    Route::get('trabajadores/trabajadores-cartas-notificacion/obtener', 'TrabajadoresController@trabajadoresCartasNotificacion');
    Route::get('trabajadores/trabajadores-certificados/obtener', 'TrabajadoresController@trabajadoresCertificados');
    Route::get('trabajadores/trabajadores-vacaciones/obtener', 'TrabajadoresController@trabajadoresVacaciones');
    Route::get('trabajadores/trabajadores-semana-corrida/obtener', 'TrabajadoresController@trabajadoresSemanaCorrida');
    Route::get('trabajadores/trabajadores-sueldo-hora/obtener', 'TrabajadoresController@trabajadoresSueldoHora');
    Route::get('trabajadores/vacaciones/obtener/{sid}', 'TrabajadoresController@trabajadorVacaciones');
    Route::get('trabajadores/cartas-notificacion/obtener/{sid}', 'TrabajadoresController@trabajadorCartasNotificacion');
    Route::get('trabajadores/finiquitos/obtener/{sid}', 'TrabajadoresController@trabajadorFiniquitos');
    Route::get('trabajadores/certificados/obtener/{sid}', 'TrabajadoresController@trabajadorCertificados');
    Route::get('trabajadores/contratos/obtener/{sid}', 'TrabajadoresController@trabajadorContratos');
    Route::get('trabajadores/fichas/obtener/{sid}', 'TrabajadoresController@trabajadorFichas');
    Route::get('trabajadores/documento/obtener/{sid}', 'DocumentosController@documentoPDF');
    Route::get('trabajadores/reajuste/obtener', 'TrabajadoresController@reajuste');    
    Route::post('trabajadores/reajuste/masivo', 'TrabajadoresController@reajustarRMI');
    Route::post('trabajadores/finiquitar/generar', 'TrabajadoresController@finiquitar');
    Route::post('trabajadores/carta-notificacion/generar', 'TrabajadoresController@cartaNotificacion');
    Route::post('trabajadores/contrato/generar', 'TrabajadoresController@contrato');
    Route::post('trabajadores/finiquito/generar', 'TrabajadoresController@finiquito');
    Route::post('trabajadores/certificado/generar', 'TrabajadoresController@certificado');
    Route::get('trabajadores/miLiquidacion/generar/{sid}', 'TrabajadoresController@miLiquidacion');
    Route::post('trabajadores/libro-remuneraciones/generar-excel', 'TrabajadoresController@generarLibroExcel');
    Route::get('trabajadores/libro-remuneraciones/descargar-excel/{nombre}', 'TrabajadoresController@descargarLibroExcel');
    Route::post('trabajadores/nomina-bancaria/generar-excel', 'TrabajadoresController@generarNominaExcel');
    Route::get('trabajadores/nomina-bancaria/descargar-excel/{nombre}', 'TrabajadoresController@descargarNominaExcel');
    Route::post('trabajadores/planilla-costo-empresa/generar-excel', 'TrabajadoresController@generarPlanillaExcel');
    Route::get('trabajadores/planilla-costo-empresa/descargar-excel/{nombre}', 'TrabajadoresController@descargarPlanillaExcel');
    Route::post('trabajadores/archivo-previred/generar', 'TrabajadoresController@generarArchivoPreviredExcel');
    Route::get('trabajadores/planilla/descargar-excel/{tipo}', 'TrabajadoresController@descargarPlantilla');
    Route::get('trabajadores/planilla-masivo/descargar-excel/{tipo}', 'TrabajadoresController@descargarPlantillaMasivo');
    Route::get('trabajadores/planilla-trabajadores/descargar', 'TrabajadoresController@descargarPlantillaTrabajadores');
    Route::get('trabajadores/documento/descargar-pdf/{nombre}', 'TrabajadoresController@documentoPDF');
    Route::post('trabajadores/semana-corrida/actualizar', 'TrabajadoresController@updateSemanaCorrida');
    Route::post('trabajadores/planilla/importar', 'TrabajadoresController@importarPlanilla');
    Route::post('trabajadores/generar-ingreso/masivo', 'TrabajadoresController@generarIngresoMasivo');
    Route::post('trabajadores/tramo/cambiar', 'TrabajadoresController@cambiarTramo');
    Route::post('trabajadores/liquidacion/registro-observaciones', 'TrabajadoresController@miLiquidacionObservaciones_store');
    Route::post('trabajadores/provision-vacaciones/obtener', 'TrabajadoresController@provisionVacaciones');
    Route::get('trabajadores/provision-vacaciones/descargar', 'TrabajadoresController@descargarProvision');
    Route::get('trabajadores/sueldo-hora/obtener/{sid}', 'TrabajadoresController@trabajadorSueldoHora');
        
    /*   NACIONALIDADES    */
    Route::resource('nacionalidades', 'NacionalidadesController');
    
    /*   ESTADOS_CIVILES    */
    Route::resource('estados-civiles', 'EstadosCivilesController');
    
    /*   CARGOS    */
    Route::resource('cargos', 'CargosController');
    
    /*   AREAS_A_CARGO    */
    Route::resource('areas-a-cargo', 'AreasACargoController');
    
    /*   TÍTULOS    */
    Route::resource('titulos', 'TitulosController');
    
    /*   CUENTAS    */
    Route::resource('cuentas', 'CuentasController');
    Route::get('cuentas/plan-cuentas/obtener', 'CuentasController@planCuentas');
    
    /*   BANCOS    */
    Route::resource('bancos', 'BancosController');
    
    /*   AFPS    */
    Route::resource('afps', 'AfpsController');
    
    /*   ISAPRES    */
    Route::resource('isapres', 'IsapresController');

    /*   TIPOS_CARGA    */
    Route::resource('tipos-carga', 'TiposCargaController');
    
    /*   FICHAS    */
    Route::resource('fichas', 'FichasTrabajadoresController');
    Route::resource('fichas/unificar/obtener', 'FichasTrabajadoresController@unificar');
    
    /*   TIPOS_HABER    */
    Route::resource('tipos-haber', 'TiposHaberController');
    Route::get('tipos-haber/ingreso-haberes/obtener', 'TiposHaberController@ingresoHaberes');    
    Route::get('tipos-haber/cuentas/obtener/{sid}', 'TiposHaberController@cuentaHaber');
    Route::get('tipos-haber/centro-costo/obtener/{sid}', 'TiposHaberController@cuentaHaberCentroCosto');
    Route::post('tipos-haber/cuentas/actualizar', 'TiposHaberController@updateCuenta');
    Route::post('tipos-haber/cuentas-centros-costos/actualizar', 'TiposHaberController@updateCuentaCentroCosto');
    Route::post('tipos-haber/cuentas-masivo/actualizar', 'TiposHaberController@updateCuentaMasivo');

    /*   TIPOS_DESCUENTO    */
    Route::resource('tipos-descuento', 'TiposDescuentoController');
    Route::get('tipos-descuento/ingreso-descuentos/obtener', 'TiposDescuentoController@ingresoDescuentos');
    Route::get('tipos-descuento/cuentas/obtener/{sid}', 'TiposDescuentoController@cuentaDescuento');
    Route::get('tipos-descuento/centro-costo/obtener/{sid}', 'TiposDescuentoController@cuentaDescuentoCentroCosto');
    Route::post('tipos-descuento/cuentas/actualizar', 'TiposDescuentoController@updateCuenta');
    Route::post('tipos-descuento/cuentas-centros-costos/actualizar', 'TiposDescuentoController@updateCuentaCentroCosto');
    Route::post('tipos-descuento/cuentas-masivo/actualizar', 'TiposDescuentoController@updateCuentaMasivo');
    
    /*   TIPOS_DOCUMENTO    */
    Route::resource('tipos-documento', 'TiposDocumentoController');
    
    /*   APVS    */
    Route::resource('apvs', 'ApvsController');
    
    /*   HABERES    */
    Route::resource('haberes', 'HaberesController');
    Route::post('haberes/ingreso/masivo', 'HaberesController@ingresoMasivo');
    Route::post('haberes/planilla/importar', 'HaberesController@importarPlanilla');
    Route::post('haberes/planilla/importar-masivo', 'HaberesController@importarPlanillaMasivo');
    Route::post('haberes/generar-ingreso/masivo', 'HaberesController@generarIngresoMasivo');
    Route::post('haberes/generar-ingreso-masivo/masivo', 'HaberesController@generarIngresoMasivoHaberes');
    Route::post('haberes/permanentes/eliminar', 'HaberesController@eliminarPermanente');
    Route::post('haberes/haberes-ficha/obtener', 'HaberesController@haberesFicha');
    Route::post('haberes/haberes-ficha/update', 'HaberesController@updateHaberFicha');
    
    /*   DESCUENTOS    */
    Route::resource('descuentos', 'DescuentosController');
    Route::post('descuentos/ingreso/masivo', 'DescuentosController@ingresoMasivo');
    Route::post('descuentos/planilla/importar', 'DescuentosController@importarPlanilla');
    Route::post('descuentos/planilla/importar-masivo', 'DescuentosController@importarPlanillaMasivo');
    Route::post('descuentos/generar-ingreso/masivo', 'DescuentosController@generarIngresoMasivo');
    Route::post('descuentos/generar-ingreso-masivo/masivo', 'DescuentosController@generarIngresoMasivoDescuentos');
    Route::post('descuentos/permanentes/eliminar', 'DescuentosController@eliminarPermanente');
    
    /*   INASISTENCIAS    */
    Route::resource('inasistencias', 'InasistenciasController');
    
    /*   ATRASOS    */
    Route::resource('atrasos', 'AtrasosController');
    
    /*   DESCUENTOS_HORA    */
    Route::resource('descuentos-horas', 'DescuentosHorasController');
        
    /*   LICENCIAS    */
    Route::resource('licencias', 'LicenciasController');
    
    /*   HORAS_EXTRA    */
    Route::resource('horas-extra', 'HorasExtraController');
    
    /*   TABLAS_ESTRUCTURANTES  */
    Route::get('tablas-estructurantes/obtener/tablas', 'TablasEstructurantesController@tablas');
    
    /*   TABLA_GLOBAL_MENSUAL  */
    Route::get('tabla-global-mensual/tablas/obtener', 'TablaGlobalMensualController@tablas');
    Route::post('tabla-global-mensual/modificar/masivo', 'TablaGlobalMensualController@modificar');
    
    /*   PRESTAMOS    */
    Route::resource('prestamos', 'PrestamosController');
    
    /*   CUOTAS    */
    Route::resource('cuotas', 'CuotasController');
    
    /*   CARGAS    */
    Route::resource('cargas', 'CargasController');
    
    /*   SECCIONES    */
    Route::resource('secciones', 'SeccionesController');
    
    /*   TABLAS    */
    Route::resource('tablas', 'TablasController');
    
    /*   TABLA_IMPUESTO_UNICO    */
    Route::resource('tabla-impuesto-unico', 'TablaImpuestoUnicoController');
    Route::post('tabla-impuesto-unico/modificar/masivo', 'TablaImpuestoUnicoController@modificar');
    
    /*   FACTORES_ACTUALIZACIÓN    */
    Route::resource('factores-actualizacion', 'FactorActualizacionController');
    Route::post('factores-actualizacion/modificar/masivo', 'FactorActualizacionController@modificar');
    
    /*   TASAS_CAJAS_EX_REGIMEN   */
    Route::resource('tasas-cajas-ex-regimen', 'TasasCajasExRegimenController');
    Route::post('tasas-cajas-ex-regimen/modificar/masivo', 'TasasCajasExRegimenController@modificar');
    
    /*   RECAUDADORES    */
    Route::resource('recaudadores', 'RecaudadoresController');
    
    /*   CODIGOS    */
    Route::resource('codigos', 'CodigosController');
    Route::post('codigos/ingreso/masivo', 'CodigosController@ingresoMasivo');
    Route::post('codigos/update/masivo', 'CodigosController@updateMasivo');
    
    /*   GLOSAS    */
    Route::resource('glosas', 'GlosasController');
    
    /*   MES_DE_TRABAJO    */
    Route::resource('mes-de-trabajo', 'MesDeTrabajoController');  
    Route::get('mes-de-trabajo/detalle-centralizacion/obtener/{mes}', 'MesDeTrabajoController@detalleCentralizacion');
    Route::post('mes-de-trabajo/pre-centralizar/obtener', 'MesDeTrabajoController@preCentralizar');
    Route::post('mes-de-trabajo/centralizar/obtener', 'MesDeTrabajoController@centralizar');
    Route::post('mes-de-trabajo/cargar-indicadores/obtener', 'MesDeTrabajoController@cargarIndicadores');
    
    /*   ANIOS    */
    Route::resource('anios', 'AniosRemuneracionesController');
    Route::get('anio-remuneracion/datos-cierre/obtener', 'AniosRemuneracionesController@datosCierre');
    Route::post('anio-remuneracion/cerrar-meses/generar', 'AniosRemuneracionesController@cerrarMeses');
    Route::post('anio-remuneracion/feriados/generar', 'AniosRemuneracionesController@feriados');
    Route::get('anio-remuneracion/calendario/obtener', 'AniosRemuneracionesController@calendario');
    Route::post('anio-remuneracion/feriados-vacaciones/generar', 'AniosRemuneracionesController@feriadosVacaciones');
    Route::post('anio-remuneracion/feriados-semana-corrida/modificar', 'AniosRemuneracionesController@modificarFestivosSemanaCorrida');
    Route::get('anio-remuneracion/calendario-vacaciones/obtener', 'AniosRemuneracionesController@calendarioVacaciones');
    Route::post('anio-remuneracion/gratificacion/generar', 'AniosRemuneracionesController@gratificacion');
    Route::get('anio-remuneracion/datos-centralizacion/obtener/{sid}', 'AniosRemuneracionesController@datosCentralizacion');
    
    /*   VALORES_INDICADORES    */
    Route::resource('valores-indicadores', 'ValoresIndicadoresController');
    Route::post('valor-indicador/ingreso/masivo', 'ValoresIndicadoresController@ingresoMasivo');
    Route::post('valor-indicador/modificar/masivo', 'ValoresIndicadoresController@modificar');
    Route::get('valores-indicadores/indicadores/obtener/{fecha}', 'ValoresIndicadoresController@indicadores');
    
    /*   LIQUIDACIONES    */
    Route::resource('liquidaciones', 'LiquidacionesController');
    Route::resource('liquidaciones/libro-remuneraciones/obtener', 'LiquidacionesController@libroRemuneraciones');
    Route::post('liquidaciones/eliminar/masivo', 'LiquidacionesController@eliminarMasivo');
    Route::post('liquidaciones/imprimir/masivo', 'LiquidacionesController@imprimirMasivo');
    
    /*   LIBRO_REMUNERACIONES    */
    Route::resource('libro-remuneraciones', 'LibrosRemuneracionesController');   
    
    /*   DECLARACIONES    */
    Route::resource('declaraciones-trabajadores', 'DeclaracionesTrabajadoresController');
    Route::post('declaraciones-trabajadores/eliminar/masivo', 'DeclaracionesTrabajadoresController@eliminarMasivo');
    
    /*  CAUSALES_FINIQUITO    */
    Route::resource('causales-finiquito', 'CausalesFiniquitoController');
    
    /*  CAUSALES_NOTIFICACION    */
    Route::resource('causales-notificacion', 'CausalesNotificacionController');
    
    /*  CLAUSULAS_CONTRATO    */
    Route::resource('clausulas-contrato', 'ClausulasContratoController');
    Route::get('clausulas-contrato/plantilla-contrato/obtener/{sid}', 'ClausulasContratoController@listaClausulasContrato');
    
    /*  CLAUSULAS_FINIQUITO    */
    Route::resource('clausulas-finiquito', 'ClausulasFiniquitoController');
    Route::get('clausulas-finiquito/plantilla-finiquito/obtener/{sid}', 'ClausulasFiniquitoController@listaClausulasFiniquito');
    
    /*  TRAMOS_HORAS_EXTRA    */
    Route::resource('tramos-horas-extra', 'TramosHorasExtraController');
    
    /*  PLANTILLAS_CARTAS_NOTIFICACION    */
    Route::resource('plantillas-cartas-notificacion', 'PlantillasCartasNotificacionController');
    
    /*  CARTAS_NOTIFICACION    */
    Route::resource('cartas-notificacion', 'CartasNotificacionController');
    
    /*  CONTRATOS    */
    Route::resource('contratos', 'ContratosController');
    
    /*  CERTIFICADOS    */
    Route::resource('certificados', 'CertificadosController');
    
    /*  PLANTILLAS_CONTRATOS    */
    Route::resource('plantillas-contratos', 'PlantillasContratosController');
    
    /*  PLANTILLAS_FINIQUITOS    */
    Route::resource('plantillas-finiquitos', 'PlantillasFiniquitosController');
    
    /*  PLANTILLAS_CERTIFICADOS    */
    Route::resource('plantillas-certificados', 'PlantillasCertificadosController');
    
    /*  FINIQUITOS    */
    Route::resource('finiquitos', 'FiniquitosController');
    Route::post('finiquitos/calculo/obtener', 'FiniquitosController@calcular');
    
    /*  DOCUMENTOS    */
    Route::resource('documentos', 'DocumentosController');
    Route::post('documentos/archivo/importar', 'DocumentosController@importarDocumento');
    Route::post('documentos/archivo/subir', 'DocumentosController@subirDocumento');
    Route::post('documentos/archivo/eliminar', 'DocumentosController@eliminarDocumento');
    
    /*  DOCUMENTOS_EMPRESA    */
    Route::resource('documentos-empresa', 'DocumentosEmpresaController');
    Route::post('documentos-empresa/archivo/importar', 'DocumentosEmpresaController@importarDocumento');
    Route::post('documentos-empresa/archivo/subir', 'DocumentosEmpresaController@subirDocumento');
    Route::get('documentos-empresa/documento/descargar-documento/{sid}', 'DocumentosEmpresaController@documentoPDF');
    Route::get('documentos-empresa/publicos/obtener', 'DocumentosEmpresaController@publicos');
    
    /*  VACACIONES    */
    Route::resource('vacaciones', 'VacacionesController');
    Route::post('vacaciones/recalculo/obtener', 'VacacionesController@recalcularVacaciones');  
    Route::post('vacaciones/toma-vacaciones/obtener', 'VacacionesController@tomaVacaciones');  
    Route::post('vacaciones/toma-vacaciones/eliminar', 'VacacionesController@eliminarTomaVacaciones');  
    
    /*   CENTROS_COSTO    */
    Route::resource('centros-costo', 'CentrosCostoController');
    
    /*   TIENDAS    */
    Route::resource('tiendas', 'TiendasController');
    
    /*   CUENTAS    */
    Route::resource('cuentas', 'CuentasController');
    
    /*   REPORTES    */
    Route::resource('reportes', 'LogsController');

    
    
    
    
    
    
    /*   PORTAL_EMPLEADOS    */
    
    
    /*   MIS_LIQUIDACIONES    */
    Route::resource('mis-liquidaciones', 'MisLiquidacionesController');
    
    /*   MIS_CARTAS_NOTIFICACIÓN    */
    Route::resource('mis-cartas-notificacion', 'MisCartasNotificacionController');
    
    /*   MIS_CERTIFICADOS    */
    Route::resource('mis-certificados', 'MisCertificadosController');

});


Route::post('login', function (){
    $_SESSION['basedatos']="";
    $indice=0;
    $apellidoNombre = false;
    $userdata = array(
        'username' => Input::get('username'),
        'password' => Input::get('password')
    );
    $empresaDestino = Input::get('empresa') ? Input::get('empresa') : array();
    if($empresaDestino){
        
        $empresa = Empresa::where('portal', $empresaDestino)->first();
        \Session::put('basedatos', $empresa->base_datos);
        Config::set('database.default', $empresa->base_datos);
        
        if(Auth::empleado()->attempt($userdata)){     
            if(Auth::empleado()->user()->activo){
                $listaMesesDeTrabajo = array();
                $empresas = array();
                \Session::put('basedatos', $empresa->base_datos);
                $listaEmpresas = array();
                $menuController = new MenuController();
                $MENU_USUARIO = $menuController->generarMenuEmpleado($empresa->id);

                $data = array(
                    "success" => true, 
                    "uf" => null, 
                    "utm" => null, 
                    "uta" => null, 
                    "listaMesesDeTrabajo" => $listaMesesDeTrabajo, 
                    "max" => 0, 
                    "empresas" => $listaEmpresas, 
                    "empresa" => $empresa, 
                    "menu" => $MENU_USUARIO['menu'], 
                    "inicio" => "/mis-liquidaciones", 
                    "accesos" => $MENU_USUARIO['secciones'], 
                    "nombre" => ucwords( mb_strtolower( Auth::empleado()->user()->nombreCompleto()) ) , 
                    "imagen" => "images/usuario.png", 
                    "usuario" => ucwords ( mb_strtolower( Auth::empleado()->user()->nombreCompleto() ) ), 
                    "cliente" => Config::get('cliente.CLIENTE.NOMBRE'), 
                    "url" => Empresa::suite(), 
                    "uID" => Auth::empleado()->user()->id,
                    'isEmpleado' => true
                );

                return Response::json($data);
            }else{
                return Response::json(array("success" => false, "mensaje" => "El Usuario se encuentra <b>Inactivo</b>. <br />Por favor comuníquse con el <b>Administrador</b>."));
            }
        }else{
            return Response::json(array("success" => false, "mensaje" => "El Nombre de Usuario y/o la Contraseña son incorrectos"));
        }
    }else{ 
        if ( Auth::usuario()->attempt($userdata) ){
                    
            $empresa_id=0;
            $listaEmpresasPermisos=array();
            $menuController = new MenuController();

            if(Auth::usuario()->user()->id > 1){

                $empresas=Auth::usuario()->user()->listaEmpresasPerfil();
                $listaEmpresas=array();
                if( !in_array(100000, $empresas) ) {
                    $empresas = Empresa::whereIn('id', $empresas)->where('habilitada', 1)->orderBy('razon_social', 'ASC')->get();
                }else{
                    $empresas = Empresa::orderBy('razon_social', 'ASC')->where('habilitada', 1)->get();
                }
                if($empresas->count()){
                    foreach( $empresas as $empresa ){
                        $listaEmpresas[]=array(
                            'id' => $empresa->id,
                            'empresa' => $empresa->razon_social,
                            'rutFormato' => $empresa->rut_formato(),
                            'rut' => $empresa->rut
                        );
                        $listaEmpresasPermisos[]=$empresa->id;
                    }
                }                

                if(count($listaEmpresas)){
                    $empresa_id=$empresas[0]['id'];
                    $empresa = Empresa::find($empresa_id);
                    Config::set('database.default', $empresa->base_datos);
                    $mesActual = MesDeTrabajo::selectMes();
                    \Session::put('basedatos', $empresa->base_datos);
                    \Session::put('mesActivo', $mesActual);     
                    $empresa->ultimoMes = $empresa->ultimoMes();
                    $empresa->primerMes = $empresa->primerMes();
                    \Session::put('empresa', $empresa);
                    Empresa::configuracion();
                    $MENU_USUARIO = $menuController->generarMenuSistema( $empresa_id, false );
                }else{
                    $opciones = MenuSistema::where('administrador', '!=', '2')->get();
                    $MENU_USUARIO = $menuController->generarMenuSistema( 0, false );
                }  

            }else{
                $listaEmpresas=array();
                $empresas = Empresa::orderBy('razon_social', 'ASC')->get();
                if($empresas->count()){
                    foreach( $empresas as $empresa ){
                        $listaEmpresas[]=array(
                            'id' => $empresa->id,
                            'empresa' => $empresa->razon_social,
                            'rutFormato' => $empresa->rut_formato(),
                            'rut' => $empresa->rut
                        );
                        $listaEmpresasPermisos[]=$empresa->id;
                    }
                }
                // el SUPERADMINISTRADOR carga el menu completamente
                if( !count($listaEmpresas) ){
                    //no existen empresas por lo tanto solo se carga las opciones de administracion
                    $opciones = MenuSistema::where('administrador', '!=', '2')->get();
                    $MENU_USUARIO = $menuController->generarMenuSistema( 0, true );
                }else{                
                    // se carga el menu completo
                    $empresa_id=$empresas[0]['id'];
                    $empresa = Empresa::find($empresa_id);
                    Config::set('database.default', $empresa->base_datos);
                    $mesActual = MesDeTrabajo::selectMes();
                    \Session::put('basedatos', $empresa->base_datos);
                    \Session::put('mesActivo', $mesActual); 
                    $empresa->ultimoMes = $empresa->ultimoMes();
                    $empresa->primerMes = $empresa->primerMes();
                    \Session::put('empresa', $empresa);
                    Empresa::configuracion();
                    \Session::put('mesActivo', $mesActual); 

                    $MENU_USUARIO = $menuController->generarMenuSistema( $empresa_id, false );
                }
            }

            $varGlobal = VariableGlobal::where('variable', 'EMPRESAS')->first();
            if(!$varGlobal){
                $varGlobal = new VariableGlobal();
                $varGlobal->variable = "EMPRESAS";
                $varGlobal->valor = 5;
                $varGlobal->save();
            }

            $empresaInicial="";
            
            if( $empresa_id ){
                
                if($mesActual){                    
                    $listaMesesDeTrabajo = MesDeTrabajo::listaMesesDeTrabajo();
                }

                $empresaInicial = array(
                    'id' => $empresa->id,
                    'logo' => $empresa->logo? "/stories/".$empresa->logo : "images/dashboard/EMPRESAS.png",
                    'empresa' => $empresa->razon_social,
                    'mesDeTrabajo' => $mesActual,
                    'ultimoMes' => $empresa->ultimoMes,
                    'primerMes' => $empresa->primerMes,
                    'rutFormato' => $empresa->rut_formato(),
                    'rut' => $empresa->rut,
                    'direccion' => $empresa->direccion,
                    'gratificacion' => $empresa->gratificacion,
                    'tipoGratificacion' => $empresa->tipo_gratificacion,
                    'cme' => $empresa->cme ? true : false,
                    'impuestoUnico' => $empresa->impuesto_unico,
                    'centroCosto' => array(
                        'isCentroCosto' => $empresa->centro_costo ? true : false,
                        'niveles' => $empresa->niveles_centro_costo
                    ),
                    'isMutual' => Empresa::isMutual(),
                    'isCaja' => Empresa::isCaja(),
                    'apellidoNombre' => $apellidoNombre,
                    'comuna' => array(
                        'id' => $empresa->comuna->id,
                        'nombre' => $empresa->comuna->comuna,
                        'provincia' => $empresa->comuna->provincia->provincia,
                        'localidad' => $empresa->comuna->localidad(),
                        'domicilio' => $empresa->domicilio()
                    )
                );
            }                        

            if(isset($mesActual)){
                $fecha = $mesActual->fechaRemuneracion;
                $indicadores = ValorIndicador::valorFecha($fecha);
            }

            if(isset($indicadores)){
                $uf = $indicadores->uf;
                $utm = $indicadores->utm;
                $uta = $indicadores->uta;
            }else{
                $uf = null;
                $utm = null;
                $uta = null;
            }
            if(!isset($listaMesesDeTrabajo)){
                $listaMesesDeTrabajo = array();
            }

            $data = array(
                "success" => true, 
                "uf" => $uf, 
                "utm" => $utm, 
                "uta" => $uta, 
                "listaMesesDeTrabajo" => $listaMesesDeTrabajo, 
                "max" => $varGlobal->valor, 
                "empresas" => $listaEmpresas, 
                "empresa" => $empresaInicial ? $empresaInicial : "" , 
                "menu" => $MENU_USUARIO['menu'], 
                "inicio" => str_replace("#", "/", $MENU_USUARIO['inicio']), 
                "accesos" => $MENU_USUARIO['secciones'], 
                "nombre" => ucwords( mb_strtolower( Auth::usuario()->user()->nombreCompleto()) ) , 
                "imagen" => "images/usuario.png", 
                "usuario" => ucwords ( mb_strtolower( Auth::usuario()->user()->nombreCompleto() ) ), 
                "cliente" => Config::get('cliente.CLIENTE.NOMBRE'), 
                "url" => Empresa::suite(), 
                "uID" => Auth::usuario()->user()->id,
                'isEmpleado' => false
            );

            return Response::json($data);
        }else{
            return Response::json(array("success" => false, "mensaje" => "El Nombre de Usuario y/o la Contraseña son incorrectos"));
        }
    }
});


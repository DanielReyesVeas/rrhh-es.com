<?php

class EmpresasController extends \BaseController {

    public function exportar_excel($version){
        $baseDatos = \Session::get('basedatos');
        $empresa = Empresa::where('base_datos', $baseDatos)->first();
        if($version== 2007) $extension = "xlsx";
        else $extension = "xls";
        if( $empresa ){
            $datos['empresa'] = $empresa->razon_social;
            $datos['empresas'] = array();

            $empresas = Empresa::orderBy('razon_social')->orderBy('nombre_fantasia')->get();
            if($empresas->count()){
                foreach( $empresas as $empresa ){
                    $datos['empresas'][]=array(
                        'rut' => $empresa->rut_formato(),
                        'razon_social' => $empresa->razon_social,
                        'nombre_fantasia' => $empresa->nombre_fantasia
                    );
                }
            }

            Excel::create('Empresas', function($excel) use($datos) {
                $excel->sheet('Empresas', function($sheet) use($datos) {
                    $sheet->loadView('excel.empresas')->with('datos', $datos);
                    $filaFinal = count($datos['empresas'])? count($datos['empresas'])+3 : 4;

                    $sheet->getStyle('B4:B'.$filaFinal)->getAlignment()->setWrapText(true);
                    $sheet->setWidth(array(
                        'A'     =>  20,
                        'B'     =>  90,
                        'C'     =>  90
                    ));
                });
            })->download( $extension );
        }
    }

    public function lista_empresas_select()
    {        
        Config::set('database.default', 'principal' );
        $lista=array();
        if(Auth::usuario()->user()->id > 1){
            $empresas = Empresa::orderBy('razon_social')->where('habilitada', 1)->orderBy('nombre_fantasia')->get();
        }else{
            $empresas = Empresa::orderBy('razon_social')->orderBy('nombre_fantasia')->get();            
        }
        if($empresas->count()){
            foreach( $empresas as $empresa ){
                $lista[]=array(
                    'id' => $empresa->id,
                    'logo' => $empresa->logo? "/stories/".$empresa->logo : "images/dashboard/EMPRESAS.png",
                    'empresa' => $empresa->razon_social
                );
            }
        }
        return Response::json($lista);
    }
    
    public function cambiarConfiguracion()
    {
        $datos = Input::all();
        
        $var = VariableGlobal::where('variable', 'configuracion')->first();
        
        if($var){
            $var->valor = $datos['valor'];
            $var->save();
        }
        
        Empresa::configuracion();
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'id' => $var
        );
        
        return Response::json($respuesta);
    }
    
    public function cambiarValorConfiguracion()
    {
        $datos = Input::all();
        $configuracion = \Session::get('configuracion');
        
        if($configuracion->configuracion=='g'){
            $var = VariableGlobal::where('variable', $datos['variable'])->first();
            if($var){
                $var->valor = $datos['valor'];
                $var->save();
            }
        }else{
            $var = VariableSistema::where('variable', $datos['variable'])->first();     
            if($var){
                $var->valor1 = $datos['valor'];
                $var->save();
            }
        }
        
        Empresa::configuracion();
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'id' => $datos
        );
        
        return Response::json($respuesta);
    }
    
    public function habilitar()
    {
        $datos = Input::all();
        $empresa = Empresa::find($datos['id']);
        if($empresa){
            $empresa->habilitada = $datos['habilitada'];
            $empresa->save();
        }
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'id' => $empresa->id
        );
        
        return Response::json($respuesta);
    }

    /**
	 * Display a listing of the resource.
	 * GET /empresas
	 *
	 * @return Response
	 */
	public function index()
	{
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#empresas');
        Config::set('database.default', 'principal');
        $lista=array();
        $empresas = Empresa::orderBy('razon_social')->orderBy('nombre_fantasia')->get();
        $mutuales = Glosa::listaMutuales();
        $cajas = Glosa::listaCajas();
        if($empresas->count()){
            foreach( $empresas as $empresa ){
                $lista[]=array(
                    'id' => $empresa->id,
                    'logo' => $empresa->logo ? URL::to("stories/min_".$empresa->logo) : "",
                    'razonSocial' => $empresa->razon_social,
                    'nombreFantasia' => $empresa->nombre_fantasia,
                    'rut' => $empresa->rut_formato(),
                    'nRut' => '_'.$empresa->rut,
                    'habilitada' => $empresa->habilitada ? true : false
                );
            }
        }
        
        $emp = Empresa::habilitadas($lista);

        $respuesta = array(
            'success' => true,
            'empresas' => $lista,
            'lista' => $emp,
            'mutuales' => $mutuales,
            'cajas' => $cajas,
            'accesos' => $permisos
        );
        
        return Response::json($respuesta);
	}


	/**
	 * Store a newly created resource in storage.
	 * POST /empresas
	 *
	 * @return Response
	 */
	public function store()
	{
        $varGlobal = VariableGlobal::where('variable', 'EMPRESAS')->first();
        if(!$varGlobal){
            $varGlobal = new VariableGlobal();
            $varGlobal->variable = "EMPRESAS";
            $varGlobal->valor = 5;
            $varGlobal->save();
            //requirde|rut:true|unique:contribuyente, rut' $datps]od'
        }

        $empresas = Empresa::all();
        if($empresas->count() < $varGlobal->valor ){



            // al guardar una empresa se genera una base de datos nueva
            Config::set('database.default', 'principal' );
            $datos = $this->get_datos_formulario();
            $datos['id']=0;
            $errores = Empresa::errores($datos);
            if( !$errores ){
                $empresa = new Empresa();
                $empresa->razon_social =  $datos['razon_social'];
                $empresa->nombre_fantasia =  $datos['nombre_fantasia'];
                $empresa->rut =  $datos['rut'];
                $empresa->direccion =  $datos['direccion'];
                $empresa->comuna_id =  $datos['comuna_id'];
                $empresa->telefono =  $datos['telefono'];
                $empresa->fax =  $datos['fax'];
                $empresa->mutual_id =  $datos['mutual_id'];
                $empresa->codigo_mutual =  $datos['codigo_mutual'];
                $empresa->tasa_fija_mutual =  $datos['tasa_fija_mutual'];
                $empresa->tasa_adicional_mutual =  $datos['tasa_adicional_mutual'];
                $empresa->caja_id =  $datos['caja_id'];
                $empresa->gratificacion =  $datos['gratificacion'];
                $empresa->tipo_gratificacion =  $datos['tipo_gratificacion'];
                $empresa->tope_gratificacion =  $datos['tope_gratificacion'];
                $empresa->gratificacion_proporcional_inasistencias =  $datos['gratificacion_proporcional_inasistencias'];
                $empresa->gratificacion_proporcional_licencias =  $datos['gratificacion_proporcional_licencias'];
                $empresa->salud_completa =  $datos['salud_completa'];
                $empresa->licencias_30 =  $datos['licencias_30'];
                $empresa->ingresos_30 =  $datos['ingresos_30'];
                $empresa->finiquitos_30 =  $datos['finiquitos_30'];
                $empresa->zona =  $datos['zona'];
                $empresa->codigo_caja =  $datos['codigo_caja'];
                $empresa->sis =  $datos['sis'];
                $empresa->representante_nombre =  $datos['representante_nombre'];
                $empresa->representante_rut =  $datos['representante_rut'];
                $empresa->representante_direccion =  $datos['representante_direccion'];
                $empresa->representante_comuna_id =  $datos['representante_comuna_id'];
                $empresa->actividad_economica =  $datos['actividad_economica'];
                $empresa->codigo_actividad =  $datos['codigo_actividad'];
                $empresa->gerente_general =  $datos['gerente_general'];
                $empresa->contador_nombre =  $datos['contador_nombre'];
                $empresa->contador_rut =  $datos['contador_rut'];
                $empresa->numero_registro =  $datos['numero_registro'];
                $empresa->cme =  $datos['cme'];
                $empresa->centro_costo =  $datos['centro_costo'];
                $empresa->niveles_centro_costo =  $datos['niveles_centro_costo'];
                $empresa->impuesto_unico =  $datos['impuesto_unico'];

                if($datos['logo']){
                    $extension = substr($datos['logo'], 0, 16);
                    $tipo="jpg";
                    if( strpos($extension, 'image/png') ){
                        $tipo="png";
                    }elseif( strpos($extension, 'image/jpg') ){
                        $tipo="jpg";
                    }elseif( strpos($extension, 'image/gif') ){
                        $tipo="gif";
                    }
                    $base64_str = substr($datos['logo'], strpos($datos['logo'], ",")+1);
                    $png_url = "foto_empresa_".$datos['rut']."_".date("YmdHis").".".$tipo;

                    //decode base64 string
                    $image = base64_decode($base64_str);
                    $im = imagecreatefromstring($image);
                    $origWidth = imagesx($im);
                    $origHeight = imagesy($im);

                    // creacion de la miniatura
                    if($origWidth > 70 ){
                        $destWidth = 70;
                        $destHeight = round( $origHeight * ( $destWidth / $origWidth ) );
                    }elseif( $origHeight > 70 ){
                        $destHeight = 70;
                        $destWidth = round( $origWidth * ( $destHeight / $origHeight ) );
                    }else{
                        $destWidth = $origWidth;
                        $destHeight = $origHeight;
                    }
                    $imNew = imagecreatetruecolor($destWidth, $destHeight);

                    if( $tipo == "png" or $tipo == "gif" ){
                        imagecolortransparent($imNew, imagecolorallocatealpha($imNew, 0, 0, 0, 127));
                        imagealphablending($imNew, false);
                        imagesavealpha($imNew, true);
                    }

                    imagecopyresampled($imNew, $im, 0, 0, 0, 0, $destWidth, $destHeight, $origWidth, $origHeight);
                    if( $tipo == "png") {
                        imagepng($imNew, public_path()."/stories/min_".$png_url, 9);
                    }elseif( $tipo == "jpg" ){
                        imagejpeg($imNew, public_path()."/stories/min_".$png_url, 100);
                    }elseif( $tipo == "gif" ){
                        imagegif($imNew, public_path()."/stories/min_".$png_url);
                    }

                    // creacion de imagen definitiva
                    if($origWidth > 150 ){
                        $destWidth = 150;
                        $destHeight = round( $origHeight * ( $destWidth / $origWidth ) );
                    }elseif( $origHeight > 150 ){
                        $destHeight = 150;
                        $destWidth = round( $origWidth * ( $destHeight / $origHeight ) );
                    }else{
                        $destWidth = $origWidth;
                        $destHeight = $origHeight;
                    }
                    $imNew = imagecreatetruecolor($destWidth, $destHeight);
                    if( $tipo == "png" or $tipo == "gif" ){
                        imagecolortransparent($imNew, imagecolorallocatealpha($imNew, 0, 0, 0, 127));
                        imagealphablending($imNew, false);
                        imagesavealpha($imNew, true);
                    }

                    imagecopyresampled($imNew, $im, 0, 0, 0, 0, $destWidth, $destHeight, $origWidth, $origHeight);
                    if( $tipo == "png") {
                        imagepng($imNew, public_path()."/stories/".$png_url, 9);
                    }elseif( $tipo == "jpg" ){
                        imagejpeg($imNew, public_path()."/stories/".$png_url, 100);
                    }elseif( $tipo == "gif" ){
                        imagegif($imNew, public_path()."/stories/".$png_url);
                    }

                    $empresa->logo = $png_url;
                }

                //tipo de entorno
                $local = Config::get('cliente.LOCAL');
                $cpanel_user = Config::get('cliente.CPANEL_USER');

                // se genera una base de datos_
                $nombreBaseDatos = Config::get('cliente.CLIENTE.EMPRESA')."_".$datos['rut'];
                $empresa->base_datos =  $nombreBaseDatos;
                $empresa->save();

                $empresaDestinoId = $empresa->id;

                $userBaseDatos = Config::get('cliente.CLIENTE.USUARIO');
                $passBaseDatos = Config::get('cliente.CLIENTE.PASS');


                if( !$local) {

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

                    $query1 = "https://184.154.202.34:2087/json-api/cpanel?user=" . $cpanel_user . "&cpanel_jsonapi_module=Mysql&cpanel_jsonapi_func=adddb&cpanel_jsonapi_apiversion=1&arg-0=" . $nombreBaseDatos;
                    $query2 = "https://184.154.202.34:2087/json-api/cpanel?user=" . $cpanel_user . "&cpanel_jsonapi_module=Mysql&cpanel_jsonapi_func=adduserdb&cpanel_jsonapi_apiversion=1&arg-0=" . $nombreBaseDatos . "&arg-1=" . $userBaseDatos . "&arg-2=all";
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
                }else{
                    DB::raw(DB::select("CREATE DATABASE ".$nombreBaseDatos));
                }

                // editamos el config de las base de datos para crear la conexion
                $configuracion = "
                '".$nombreBaseDatos."' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => '".$nombreBaseDatos."',
                    'username'  => '".$userBaseDatos."',
                    'password'  => '".$passBaseDatos."',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                /*INICIO_PLANTILLA*/";

                $path = app_path()."/config";
                $contenido = file_get_contents($path."/database.php");
                $contenido = str_replace("/*INICIO_PLANTILLA*/", $configuracion, $contenido);

                // sobre-escribir archivo de configuraciones
                file_put_contents( $path."/database.php", $contenido );
                $datos['base_datos'] = "100000";
                if( $datos['base_datos'] == "100000" ){
                    // estructura original del sistema
                    $archivo = public_path()."/estructura_empresa.sql";
                    
                    shell_exec("mysql -u ".$userBaseDatos." -p" . $passBaseDatos . " ".$nombreBaseDatos." < ".$archivo);
                }else{
                    $empresaOrigen = Empresa::find($datos['base_datos']);
                    if($empresaOrigen){
                        // copiar base de datos existente
                        shell_exec("mysqldump -u ".$userBaseDatos." -p" . $passBaseDatos . " ".$empresaOrigen->base_datos." | mysql -u ".$userBaseDatos." -p" . $passBaseDatos . " ".$nombreBaseDatos);
                        sleep(2);
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".cont_asientos"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".cont_asientos_detalles"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".cont_asientos_detalles_parametros"));

                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".clientes_proveedores"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".areas_negocios"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".boletas_honorarios"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".centros_costos"));


                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_compras"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_compras_cuentas"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_compras_cuentas_parametros"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_compras_detalles"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_compras_detalles_impuestos"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_compras_detalles_impuestos_parametros"));

                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_ventas"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_ventas_cuentas"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_ventas_cuentas_parametros"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_ventas_detalles"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_ventas_detalles_impuestos"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_ventas_detalles_impuestos_parametros"));



                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_boletas"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_boletas_cuentas"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_boletas_cuentas_parametros"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".libros_boletas_detalles"));

                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".sii_control_folios"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".sii_control_folios_usos"));

                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".medios_pagos"));
                        DB::raw(DB::select("TRUNCATE TABLE ".$nombreBaseDatos.".medios_pagos_cuentas"));
                    }
                }

                // actualizamos en la tabla variables el valor del anio inicial
                //DB::raw(DB::select("TRUNCATE TABLE " . $nombreBaseDatos . ".variables_sistema"));
                
                $SQL="INSERT INTO " . $nombreBaseDatos . ".variables_sistema (id, variable, valor1, valor2, valor3, valor4, valor5, created_at, updated_at) VALUES (1, 'anio_inicial', '" . $datos['anioInicial'] . "', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');";
                DB::raw(DB::select($SQL));
                $SQL="INSERT INTO " . $nombreBaseDatos . ".variables_sistema (id, variable, valor1, valor2, valor3, valor4, valor5, created_at, updated_at) VALUES (2, 'mes_inicial', '" . $datos['mesInicial']['fecha'] . "', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');";
                DB::raw(DB::select($SQL));
                
                /*DB::raw(DB::select("UPDATE " . $nombreBaseDatos . ".variables_sistema SET valor1=" . $datos['anioInicial'] . " WHERE variable='ANIO_INICIAL' "));
                DB::raw(DB::select("UPDATE " . $nombreBaseDatos . ".variables_sistema SET valor1=" . $datos['mesInicial']['fecha'] . " WHERE variable='MES_INICIAL' "));*/
                
                $meses = Funciones::crearMesesSQL($datos['mesInicial']['id']);
                
                DB::raw(DB::select("TRUNCATE TABLE " . $nombreBaseDatos . ".anios_remuneraciones"));
                $sid = Funciones::generarSID();
                $SQL="INSERT INTO " . $nombreBaseDatos . ".anios_remuneraciones (id, sid, anio, enero, febrero, marzo, abril, mayo, junio, julio, agosto, septiembre, octubre, noviembre, diciembre, gratificacion, pagar, created_at, updated_at, deleted_at) VALUES (1, '" . $sid . "', '" . $datos['anioInicial'] . "', " . $meses . ", NULL, 0 , '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);";
                DB::raw(DB::select($SQL));
                
                DB::raw(DB::select("TRUNCATE TABLE " . $nombreBaseDatos . ".meses_de_trabajo"));
                $sid = Funciones::generarSID();
                $fechaRemuneracion = Funciones::obtenerFechaRemuneracion($datos['mesInicial']['mes'], $datos['anioInicial']);
                $SQL="INSERT INTO " . $nombreBaseDatos . ".meses_de_trabajo (sid, mes, nombre, fecha_remuneracion, anio_id, created_at, updated_at, deleted_at) VALUES ('".$sid."', '".$datos['mesInicial']['fecha']."', '".$datos['mesInicial']['mes']."', '".$fechaRemuneracion."', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);";
                DB::raw(DB::select($SQL));                
                
                if($datos['centro_costo']){
                    $i = 1;
                    foreach($datos['centros_costo'] as $centro){
                        $SQL="INSERT INTO " . $nombreBaseDatos . ".variables_sistema (variable, valor1, valor2, valor3, valor4, valor5, created_at, updated_at) VALUES ('centro_costo', " . $i . ", '" . $centro['nombre'] . "', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');";
                        DB::raw(DB::select($SQL));
                        $i++;
                    }
                }
                
                if($datos['zonas']){
                    foreach($datos['zonas'] as $zona){
                        $sid = Funciones::generarSID();
                        $SQL="INSERT INTO " . $nombreBaseDatos . ".zonas_impuesto_unico (sid, nombre, porcentaje, created_at, updated_at) VALUES ('" . $sid . "', '" . $zona['nombre'] . "', '" . $zona['porcentaje'] . "', '0000-00-00 00:00:00', '0000-00-00 00:00:00');";
                        DB::raw(DB::select($SQL));
                    }
                }
                
                $SQL="INSERT INTO " . $nombreBaseDatos . ".cajas (caja_id, codigo, anio_id, created_at, updated_at) VALUES ('" . $datos['caja_id'] . "','" . $datos['codigo_caja'] . "', '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00');";
                DB::raw(DB::select($SQL));
                
                $SQL="INSERT INTO " . $nombreBaseDatos . ".mutuales (mutual_id, tasa_fija, tasa_adicional, extraordinaria, sanna, codigo, anio_id, created_at, updated_at) VALUES ('" . $datos['mutual_id'] . "','" . $datos['tasa_fija_mutual'] . "','" . $datos['extraordinaria'] . "','" . $datos['sanna'] . "','" . $datos['tasa_adicional_mutual'] . "', '" . $datos['codigo_mutual'] . "', '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00');";
                DB::raw(DB::select($SQL));     
                
                ValorIndicador::crearIndicadores($datos['mesInicial']['fecha'], $fechaRemuneracion);
                
                /*$sub = str_replace('rrhhes_', '', Config::get('cliente.CLIENTE.EMPRESA'));
                if(strpos($sub, 'prueba') !== false){
                    $SQL="UPDATE " . $nombreBaseDatos . ".aportes_cuentas SET cuenta_id=176 WHERE tipo_aporte<>7 AND tipo_aporte<>5;";
                    DB::statement($SQL);
                    $SQL="UPDATE " . $nombreBaseDatos . ".tipos_haber SET cuenta_id=172 WHERE id<>1 AND id<>3 AND id<>4 AND id<>7;";
                    DB::statement($SQL);
                    $SQL="UPDATE " . $nombreBaseDatos . ".tipos_haber SET cuenta_id=238 WHERE id=1;";
                    DB::statement($SQL);
                    $SQL="UPDATE " . $nombreBaseDatos . ".tipos_haber SET cuenta_id=239 WHERE id=4 OR id=3;";
                    DB::statement($SQL);
                    $SQL="UPDATE " . $nombreBaseDatos . ".tipos_haber SET cuenta_id=236 WHERE id=7;";
                    DB::statement($SQL);
                    $SQL="UPDATE " . $nombreBaseDatos . ".tipos_descuento SET cuenta_id=176 WHERE id<>1;";
                    DB::statement($SQL);
                    $SQL="UPDATE " . $nombreBaseDatos . ".tipos_descuento SET cuenta_id=174 WHERE id=1;";
                    DB::statement($SQL);
                }*/

                $modMenu=false;
                $menuArray=array();

                $empresas = Empresa::orderBy('id')->get();
                if( $empresas->count() == 1 ) {
                    // si es uno se tiene que habilitar el menu del usuario ya que no cuenta con las opciones

                    $menuController = new MenuController();
                    if (Auth::usuario()->user()->id > 1) {
                        $empresas = Auth::usuario()->user()->listaEmpresasPerfil();
                        $listaEmpresas = array();
                        if (!in_array(100000, $empresas)) {
                            $empresas = Empresa::whereIn('id', $empresas)->orderBy('razon_social', 'ASC')->get();
                        } else {
                            $empresas = Empresa::orderBy('razon_social', 'ASC')->get();
                        }
                        if ($empresas->count()) {
                            foreach ($empresas as $empresa) {
                                $listaEmpresas[] = array(
                                    'id' => $empresa->id,
                                    'logo' => $empresa->logo ? "/stories/" . $empresa->logo : "images/dashboard/EMPRESAS.png",
                                    'empresa' => $empresa->razon_social
                                );
                                $listaEmpresasPermisos[] = $empresa->id;
                            }
                        }

                        if ($empresaDestinoId > 0 and in_array($empresaDestinoId, $listaEmpresasPermisos)) {
                            $empresa_id = $empresaDestinoId;
                        } elseif (count($listaEmpresas)) {
                            $empresa_id = $listaEmpresas[0]['id'];
                        }


                        $MENU_USUARIO = $menuController->generarMenuSistema($empresa_id);

                    } else {
                        $listaEmpresas = array();
                        $empresas = Empresa::orderBy('razon_social', 'ASC')->get();
                        if ($empresas->count()) {
                            foreach ($empresas as $empresa) {
                                $listaEmpresas[] = array(
                                    'id' => $empresa->id,
                                    'logo' => $empresa->logo ? "/stories/" . $empresa->logo : "images/dashboard/EMPRESAS.png",
                                    'empresa' => $empresa->razon_social
                                );
                                $listaEmpresasPermisos[] = $empresa->id;
                            }
                        }
                        // el SUPERADMINISTRADOR carga el menu completamente
                        if (!count($listaEmpresas)) {
                            //no existen empresas por lo tanto solo se carga las opciones de administracion
                            $opciones = MenuSistema::where('administrador', '!=', '2')->get();
                            $MENU_USUARIO = $menuController->generarMenuSistema(0, true);
                        } else {
                            // se carga el menu completo
                            if ($empresaDestinoId > 0 and in_array($empresaDestinoId, $listaEmpresasPermisos)) {
                                $empresa_id = $empresaDestinoId;
                            } else {
                                $empresa_id = $listaEmpresas[0]['id'];
                            }
                            $MENU_USUARIO = $menuController->generarMenuSistema(0, false);
                        }
                    }

                    if (count($listaEmpresas)) {
                        $empresa = Empresa::find($empresa_id);
                        \Session::put('basedatos', $empresa->base_datos);
                    }

                    $varGlobal = VariableGlobal::where('variable', 'EMPRESAS')->first();
                    if (!$varGlobal) {
                        $varGlobal = new VariableGlobal();
                        $varGlobal->variable = "EMPRESAS";
                        $varGlobal->valor = 5;
                        $varGlobal->save();
                    }

                    $empresaInicial = "";
                    if ($empresa_id) {
                        $empresa = Empresa::find($empresa_id);
                        $empresaInicial = array(
                            'id' => $empresa->id,
                            'logo' => $empresa->logo ? "/stories/" . $empresa->logo : "images/dashboard/EMPRESAS.png",
                            'empresa' => $empresa->razon_social
                        );
                    }
                    $menuArray=array(
                        'menu' => $MENU_USUARIO['menu'],
                        "inicio" => str_replace("#", "/", $MENU_USUARIO['inicio']),
                        'accesos' => $MENU_USUARIO['secciones'],
                        "empresa" => $empresaInicial? $empresaInicial : ""
                    );
                    $modMenu=true;

                }

                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente",
                    'id' => $empresa->id,
                    'modMenu' => $modMenu,
                    'menu' => $menuArray,
                    'crear' => true,
                    'editar' => false
                );
            }else{
                $respuesta=array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'errores' => $errores
                );
            }
        }else{
            $respuesta=array(
                'success' => false,
                'mensaje' => "La Empresa no puede ser creada debido a que ya cuenta con la cantidad maxima asignada",
                'errores' => array()
            );
        }

        return Response::json($respuesta);
	}

	/**
	 * Display the specified resource.
	 * GET /empresas/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $datosEmpresa = array();
        $anios = array();
        
        if($id){
            $empresa = Empresa::find($id);     
            $anios = $empresa->aniosEmpresa();

            $datosEmpresa = array(
                'id' => $empresa->id,
                'razonSocial' => $empresa->razon_social,
                'nombreFantasia' => $empresa->nombre_fantasia,
                'rut' => $empresa->rut,
                'direccion' => $empresa->direccion,
                'comuna' => array(
                    'id' => $empresa->comuna->id,
                    'nombre' => $empresa->comuna->localidad()
                ),
                'mutuales' => $empresa->mutuales(),
                'cajas' => $empresa->cajas(),
                'gratificacion' => $empresa->gratificacion,
                'tipoGratificacion' => $empresa->tipo_gratificacion,
                'topeGratificacion' => $empresa->tope_gratificacion,
                'proporcionalInasistencias' => $empresa->gratificacion_proporcional_inasistencias ? true : false,
                'proporcionalLicencias' => $empresa->gratificacion_proporcional_licencias ? true : false,
                'saludCompleta' => $empresa->salud_completa ? true : false,
                'ingresos30' => $empresa->ingresos_30 ? true : false,
                'finiquitos30' => $empresa->finiquitos_30 ? true : false,
                'licencias30' => $empresa->licencias_30 ? true : false,
                'zona' => $empresa->zona,
                'sis' => $empresa->sis ? true : false,
                'telefono' => $empresa->telefono,
                'fax' => $empresa->fax,
                'representanteNombre' => $empresa->representante_nombre,
                'representanteRut' => $empresa->representante_rut,
                'representanteDireccion' => $empresa->representante_direccion,
                'representanteComuna' => array(
                    'id' => $empresa->comunaRepresentante->id,
                    'nombre' => $empresa->comunaRepresentante->localidad()
                ),
                'actividadEconomica' => $empresa->actividad_economica,
                'codigoActividad' => $empresa->codigo_actividad,
                'gerenteGeneral' => $empresa->gerente_general,
                'contadorNombre' => $empresa->contador_nombre,
                'contadorRut' => $empresa->contador_rut,
                'numeroRegistro' => $empresa->numero_registro,
                'logo' => $empresa->logo,
                'cme' => $empresa->cme ? true : false,
                'centroCosto' => $empresa->centro_costo ? true : false,
                'nivelesCentroCosto' => $empresa->niveles_centro_costo,
                'impuestoUnico' => $empresa->impuesto_unico,
                'zonasImpuestoUnico' => $empresa->misZonas(),
                'centrosCosto' => $empresa->misCentrosCosto()
            );
        }
        
        $datos = array(
            'datos' => $datosEmpresa,
            'anios' => $anios
        );
        
        return Response::json($datos);
	}
    
    public function notificaciones()
    {
        $notificaciones = array();
        if(Empresa::variableConfiguracion('notificaciones') && MesDeTrabajo::isUltimoMes()){
            Trabajador::trabajadoresRMI($notificaciones);
            MesDeTrabajo::isMesDisponible($notificaciones);
            
        }
        
        $datos = array(
            'notificaciones' => $notificaciones
        );
        
        return Response::json($datos);
    }


	/**
	 * Update the specified resource in storage.
	 * PUT /empresas/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        Config::set('database.default', 'principal' );
        // al guardar una empresa se genera una base de datos nueva
        $datos = $this->get_datos_formulario();
        $datos['id']=$id;
        $errores = Empresa::errores($datos);
        if( !$errores ){
            $empresa = Empresa::find($id);
            $empresa->razon_social =  $datos['razon_social'];
            $empresa->nombre_fantasia =  $datos['nombre_fantasia'];
            $empresa->rut =  $datos['rut'];
            $empresa->direccion =  $datos['direccion'];
            $empresa->comuna_id =  $datos['comuna_id'];
            $empresa->telefono =  $datos['telefono'];
            $empresa->fax =  $datos['fax'];
            $empresa->mutual_id =  $datos['mutual_id'];
            $empresa->codigo_mutual =  $datos['codigo_mutual'];
            $empresa->tasa_fija_mutual =  $datos['tasa_fija_mutual'];
            $empresa->tasa_adicional_mutual =  $datos['tasa_adicional_mutual'];
            $empresa->caja_id =  $datos['caja_id'];
            $empresa->codigo_caja =  $datos['codigo_caja'];
            $empresa->sis =  $datos['sis'];
            $empresa->gratificacion =  $datos['gratificacion'];
            $empresa->tipo_gratificacion =  $datos['tipo_gratificacion'];
            $empresa->tope_gratificacion =  $datos['tope_gratificacion'];
            $empresa->gratificacion_proporcional_inasistencias =  $datos['gratificacion_proporcional_inasistencias'];
            $empresa->gratificacion_proporcional_licencias =  $datos['gratificacion_proporcional_licencias'];
            $empresa->salud_completa =  $datos['salud_completa'];
            $empresa->licencias_30 =  $datos['licencias_30'];
            $empresa->ingresos_30 =  $datos['ingresos_30'];
            $empresa->finiquitos_30 =  $datos['finiquitos_30'];
            $empresa->zona =  $datos['zona'];
            $empresa->representante_nombre =  $datos['representante_nombre'];
            $empresa->representante_rut =  $datos['representante_rut'];
            $empresa->representante_direccion =  $datos['representante_direccion'];
            $empresa->representante_comuna_id =  $datos['representante_comuna_id'];
            $empresa->actividad_economica =  $datos['actividad_economica'];
            $empresa->codigo_actividad =  $datos['codigo_actividad'];
            $empresa->gerente_general =  $datos['gerente_general'];
            $empresa->contador_nombre =  $datos['contador_nombre'];
            $empresa->contador_rut =  $datos['contador_rut'];
            $empresa->numero_registro =  $datos['numero_registro'];
            $empresa->cme =  $datos['cme'];
            $empresa->centro_costo =  $datos['centro_costo'];
            $empresa->niveles_centro_costo =  $datos['niveles_centro_costo'];
            $empresa->impuesto_unico =  $datos['impuesto_unico'];
            
            if($datos['centro_costo']){
                $empresa->updateCentrosCosto($datos['centros_costo']);
            }

            $zonas = $empresa->comprobarZonas($datos['zonas']);
            
            if($zonas['create']){
                foreach($zonas['create'] as $zona){
                    $nuevaZona = new ZonaImpuestoUnico();
                    $nuevaZona->sid = Funciones::generarSID();
                    $nuevaZona->nombre = $zona['nombre'];
                    $nuevaZona->porcentaje = $zona['porcentaje'];
                    $nuevaZona->save();
                }
            }
            
            if($zonas['update']){
                foreach($zonas['update'] as $zona){
                    $nuevaZona = ZonaImpuestoUnico::find($zona['id']);
                    $nuevaZona->nombre = $zona['nombre'];
                    $nuevaZona->porcentaje = $zona['porcentaje'];
                    $nuevaZona->save();
                }
            }
            
            if($zonas['destroy']){
                foreach($zonas['destroy'] as $zona){
                    $nuevaZona = ZonaImpuestoUnico::find($zona['id']);
                    $nuevaZona->delete();
                }
            }
            
            $cajas = $empresa->actualizarCajas($datos['cajas']);
            $empresa->actualizarMutuales($datos['mutuales']);
            
            
            if($datos['logo']){
                $extension = substr($datos['logo'], 0, 16);
                $tipo="jpg";
                if( strpos($extension, 'image/png') ){
                    $tipo="png";
                }elseif( strpos($extension, 'image/jpg') ){
                    $tipo="jpg";
                }elseif( strpos($extension, 'image/gif') ){
                    $tipo="gif";
                }
                $base64_str = substr($datos['logo'], strpos($datos['logo'], ",")+1);
                $png_url = "foto_empresa_".$datos['rut']."_".date("YmdHis").".".$tipo;

                list($type, $data) = explode(';', $datos['logo']);
                list(, $data)      = explode(',', $data);
                $data = base64_decode($data);

                //decode base64 string
         //       $image = base64_decode($base64_str);
                $im = imagecreatefromstring($data);
                $origWidth = imagesx($im);
                $origHeight = imagesy($im);

                // creacion de la miniatura
                if($origWidth > 70 ){
                    $destWidth = 70;
                    $destHeight = round( $origHeight * ( $destWidth / $origWidth ) );
                }elseif( $origHeight > 70 ){
                    $destHeight = 70;
                    $destWidth = round( $origWidth * ( $destHeight / $origHeight ) );
                }else{
                    $destWidth = $origWidth;
                    $destHeight = $origHeight;
                }
                $imNew = imagecreatetruecolor($destWidth, $destHeight);
                if( $tipo == "png" or $tipo == "gif" ){
                    imagecolortransparent($imNew, imagecolorallocatealpha($imNew, 0, 0, 0, 127));
                    imagealphablending($imNew, false);
                    imagesavealpha($imNew, true);
                }
                imagecopyresampled($imNew, $im, 0, 0, 0, 0, $destWidth, $destHeight, $origWidth, $origHeight);
                if( $tipo == "png") {
                    imagepng($imNew, public_path()."/stories/min_".$png_url);
                }elseif( $tipo == "jpg" ){
                    imagejpeg($imNew, public_path()."/stories/min_".$png_url);
                }elseif( $tipo == "gif" ){
                    imagegif($imNew, public_path()."/stories/min_".$png_url);
                }

                // creacion de imagen definitiva
                if($origWidth > 150 ){
                    $destWidth = 150;
                    $destHeight = round( $origHeight * ( $destWidth / $origWidth ) );
                }elseif( $origHeight > 150 ){
                    $destHeight = 150;
                    $destWidth = round( $origWidth * ( $destHeight / $origHeight ) );
                }else{
                    $destWidth = $origWidth;
                    $destHeight = $origHeight;
                }
                $imNew = imagecreatetruecolor($destWidth, $destHeight);
                if( $tipo == "png" or $tipo == "gif" ){
                    imagecolortransparent($imNew, imagecolorallocatealpha($imNew, 0, 0, 0, 127));
                    imagealphablending($imNew, false);
                    imagesavealpha($imNew, true);
                }
                imagecopyresampled($imNew, $im, 0, 0, 0, 0, $destWidth, $destHeight, $origWidth, $origHeight);
                if( $tipo == "png") {

                    $colorTransparancia=imagecolortransparent($im);// devuelve el color usado como transparencia o -1 si no tiene transparencias
                    if($colorTransparancia!=-1){ //TIENE TRANSPARENCIA
                        $colorTransparente = imagecolorsforindex($im, $colorTransparancia); //devuelve un array con las componentes de lso colores RGB + alpha
                        $idColorTransparente = imagecolorallocatealpha($imNew, $colorTransparente['red'], $colorTransparente['green'], $colorTransparente['blue'], $colorTransparente['alpha']); // Asigna un color en una imagen retorna identificador de color o FALSO o -1 apartir de la version 5.1.3
                        imagefill($imNew, 0, 0, $idColorTransparente);
                        imagecolortransparent($imNew, $idColorTransparente);
                    }
                    imagepng($imNew, public_path()."/stories/".$png_url);
                }elseif( $tipo == "jpg" ){
                    imagejpeg($imNew, public_path()."/stories/".$png_url);
                }elseif( $tipo == "gif" ){
                    imagegif($imNew, public_path()."/stories/".$png_url);
                }


                if( $empresa->logo ){
                    if(file_exists('stories/'. $empresa->logo)){
                        unlink('stories/'. $empresa->logo );
                    }
                    if(file_exists('stories/min_'. $empresa->logo)){
                        unlink('stories/min_'. $empresa->logo );
                    }
                }

                $empresa->logo = $png_url;
            }
            $empresa->save();                    

            $respuesta=array(
                'success' => true,
                'mensaje' => "La Información fue actualizada correctamente",
                'crear' => false,
                'editar' => true,
                'id' => $empresa->id,
                'cajas' => $datos['cajas']
            );
        }else{
            $respuesta=array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        }

        return Response::json($respuesta);
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /empresas/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$mensaje="La Información fue eliminada correctamente";
        Empresa::find($id)->delete();
        $empresas = Empresa::orderBy('id')->get();
        return Response::json(array('success' => true, 'empresas' => $empresas, 'mensaje' => $mensaje));
	}

    public function get_datos_formulario(){
        $datos=array(
            'razon_social' => Input::get('razonSocial'),
            'nombre_fantasia' => Input::get('nombreFantasia'),
            'rut' => Input::get('rut'),
            'direccion' => Input::get('direccion'),
            'comuna_id' => Input::get('comuna')['id'],
            'telefono' => Input::get('telefono'),
            'fax' => Input::get('fax'),
            'sis' => Input::get('sis'),
            'mutual_id' => Input::get('mutual')['id'],
            'mutuales' => Input::get('mutuales'),
            'codigo_mutual' => Input::get('mutual')['codigo'],
            'tasa_fija_mutual' => Input::get('mutual')['tasaFija'],
            'extraordinaria' => Input::get('mutual')['extraordinaria'],
            'sanna' => Input::get('mutual')['sanna'],
            'tasa_adicional_mutual' => Input::get('mutual')['tasaAdicional'],
            'cajas' => Input::get('cajas'),
            'caja_id' => Input::get('caja')['id'],
            'codigo_caja' => Input::get('caja')['codigo'],
            'representante_nombre' => Input::get('representanteNombre'),
            'representante_rut' => Input::get('representanteRut'),
            'representante_direccion' => Input::get('representanteDireccion'),
            'representante_comuna_id' => Input::get('representanteComuna')['id'],
            'actividad_economica' => Input::get('actividadEconomica'),
            'codigo_actividad' => Input::get('codigoActividad'),
            'gerente_general' => Input::get('gerenteGeneral'),
            'contador_nombre' => Input::get('contadorNombre'),
            'contador_rut' => Input::get('contadorRut'),
            'numero_registro' => Input::get('numeroRegistro'),
            'logo' => Input::get('fotografiaBase64'),
            'base_datos' => Input::get('baseDatos')['id'],
            'anioInicial' => Input::get('anioInicial'),
            'mesInicial' => Input::get('mesInicial'),
            'tipo_gratificacion' => Input::get('tipoGratificacion'),
            'gratificacion' => Input::get('gratificacion'),
            'tope_gratificacion' => Input::get('topeGratificacion'),
            'gratificacion_proporcional_inasistencias' => Input::get('proporcionalInasistencias'),
            'gratificacion_proporcional_licencias' => Input::get('proporcionalLicencias'),
            'salud_completa' => Input::get('saludCompleta'),
            'licencias_30' => Input::get('licencias30'),
            'ingresos_30' => Input::get('ingresos30'),
            'finiquitos_30' => Input::get('finiquitos30'),
            'zona' => Input::get('zona'),
            'cme' => Input::get('cme'),
            'centro_costo' => Input::get('centroCosto'),
            'centros_costo' => Input::get('centrosCosto'),
            'niveles_centro_costo' => Input::get('nivelesCentroCosto'),
            'impuesto_unico' => Input::get('impuestoUnico'),
            'zonas' => Input::get('zonasImpuestoUnico')
        );
        return $datos;
    }

}
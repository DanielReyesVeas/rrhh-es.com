<?php

class MesDeTrabajoController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $listaMesesDeTrabajo = array();
    	$mesesDeTrabajo = MesDeTrabajo::orderBy('id', 'DESC')->get();
    	if( $mesesDeTrabajo->count() ){
            foreach( $mesesDeTrabajo as $mesDeTrabajo ){
                $listaMesesDeTrabajo[]=array(
                    'id' => $mesDeTrabajo->id,
                    'mes' => $mesDeTrabajo->mes,
                    'nombre' => $mesDeTrabajo->nombre,
                    'anio' => $mesDeTrabajo->anioRemuneracion->anio,
                    'idAnio' => $mesDeTrabajo->anio_id,
                    'fechaRemuneracion' => $mesDeTrabajo->fecha_remuneracion,
                    'isIngresado' => $mesDeTrabajo->estado()
                );
            }
    	}
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaMesesDeTrabajo
        );
    	return $datos;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $empresa =  \Session::get('empresa');
        $datos = $this->get_datos_formulario();
        $errores = MesDeTrabajo::errores($datos);      
        Config::set('database.default', 'admin' );                
        $mes = DB::table('meses')->where('mes', $datos['mes'])->first();
        Config::set('database.default', $empresa->base_datos );
        
        if($mes->mes=='2018-01-01'){
            $datos['nombre'] = 'Enero';
            $nuevoAnio = date('Y', strtotime($mes->mes));
            $anio = new AnioRemuneracion();
            $anio->sid = Funciones::generarSID();
            $anio->anio = $nuevoAnio;
            $anio->enero = 1;
            $anio->febrero = 0;
            $anio->marzo = 0;
            $anio->abril = 0;
            $anio->mayo = 0;
            $anio->junio = 0;
            $anio->julio = 0;
            $anio->agosto = 0;
            $anio->septiembre = 0;
            $anio->octubre = 0;
            $anio->noviembre = 0;
            $anio->diciembre = 0;
            $anio->gratificacion = 0;
            $anio->pagar = 0;
            $anio->utilidad = NULL;
            $anio->save();
            $idAnio = $anio->id;            
        }else{
            $idAnio = $datos['anio_id'];    
            $anioRem = AnioRemuneracion::find($idAnio);
            if($anioRem){
                $campo = strtolower($datos['nombre']);
                $anioRem->$campo=1;
                $anioRem->save();
            }
        }
        
        if($mes){
            $mesDeTrabajo = new MesDeTrabajo();
            $mesDeTrabajo->id = $mes->id;
            $mesDeTrabajo->sid = Funciones::generarSID();
            $mesDeTrabajo->mes = $datos['mes'];
            $mesDeTrabajo->nombre = $datos['nombre'];
            $mesDeTrabajo->fecha_remuneracion = $datos['fecha_remuneracion'];
            $mesDeTrabajo->anio_id = $idAnio;
            $mesDeTrabajo->save();
            Trabajador::crearSemanasCorridas($mesDeTrabajo);
            Trabajador::calcularVacaciones($mesDeTrabajo);
            ValorIndicador::crearIndicadores($mesDeTrabajo->mes, $datos['fecha_remuneracion']);
            Config::set('database.default', $empresa->base_datos );
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'mes' => array(
                    'id' => $mes->id,
                    'nombre' => $mesDeTrabajo->nombre,
                    'fechaRemuneracion' => $mesDeTrabajo->fecha_remuneracion,
                    'idAnio' => $mesDeTrabajo->anio_id,
                    'mes' => $mesDeTrabajo->mes,
                    'isIngresado' => true,
                    'anio' => $mesDeTrabajo->anioRemuneracion->anio,
                    'mesActivo' => $mesDeTrabajo->nombre . ' ' . $mesDeTrabajo->anioRemuneracion->anio
                )
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
        $mesDeTrabajo = MesDeTrabajo::whereSid($sid)->first();

        $datosMesDeTrabajo=array(
            'id' => $mesDeTrabajo->id,
            'mes' => $mesDeTrabajo->mes,
            'anio' => $mesDeTrabajo->anioRemuneracion->anio,
            'idAnio' => $mesDeTrabajo->anio_id,
            'nombre' => $mesDeTrabajo->nombre,
            'fechaRemuneracion' => $mesDeTrabajo->fecha_remuneracion
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosMesDeTrabajo
        );
        
        return Response::json($datos);
    }    
    
    public function detalleCentralizacion($mes)
    {
        $comprobante = ComprobanteCentralizacion::where('mes', $mes)->first();
        $datosComprobante = array();
        
        if($comprobante){
            $datosComprobante=array(
                'id' => $comprobante->id,
                'mes' => $comprobante->mes,
                'fecha' => $comprobante->fecha,
                'referencia' => $comprobante->referencia,
                'comentario' => $comprobante->comentario,
                'empresa' => $comprobante->empresa,
                'numero' => $comprobante->numero,
                'detalles' => $comprobante->comprobanteDetalles()
            );      
        }
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosComprobante
        );
        
        return Response::json($datos);
    }
    
    public function centralizar()
    {
        
        $empresa = \Session::get('empresa');
        $datos = Input::all();
        $comprobante = $datos['comprobante'];
        $mes = \Session::get('mesActivo');
        $lista = Trabajador::centralizar($mes->mes, $empresa->id);
        $comp = $this->simularCentralizacion($lista);
        
        if($empresa->rut=='965799206'){
            
            $comprobanteRRhh = new ComprobanteCentralizacion();
            $comprobanteRRhh->fecha = $comprobante['Fecha'];
            $comprobanteRRhh->mes = $mes->mes;
            $comprobanteRRhh->referencia=$comprobante['Referencia'];
            $comprobanteRRhh->empresa = $comprobante['Empresa'];
            $comprobanteRRhh->comentario = $comprobante['Comentario'];
            $comprobanteRRhh->numero = 0;
            $comprobanteRRhh->save();
                
            foreach($comprobante['Detalle'] as $comprobanteDetalle){
                $comprobanteRRhhDetalle = new DetalleComprobanteCentralizacion();
                $comprobanteRRhhDetalle->comprobante_id = $comprobanteRRhh->id;
                $comprobanteRRhhDetalle->cuenta = $comprobanteDetalle['Cuenta'];
                $comprobanteRRhhDetalle->comentario = $comprobanteDetalle['Comentario'];
                $comprobanteRRhhDetalle->referencia = $comprobanteDetalle['Referencia'];
                $comprobanteRRhhDetalle->debe = $comprobanteDetalle['Debe'];
                $comprobanteRRhhDetalle->haber = $comprobanteDetalle['Haber'];
                $comprobanteRRhhDetalle->pais = $comprobanteDetalle['Pais'];
                $comprobanteRRhhDetalle->canal = $comprobanteDetalle['Canal'];
                $comprobanteRRhhDetalle->tienda = $comprobanteDetalle['Tienda'];
                $comprobanteRRhhDetalle->save();                        
            }
            
            try {
                $objetoEnvio = new stdClass();
                $objetoEnvio->AsientoContable = $comp['comprobante'];
                $parameters=array('asiento'=>$objetoEnvio );
                $client = new SoapClient('http://ws.audiomusica.com/WsRemuneService/RemunService.svc?wsdl', array('login' => "audiomusica.com/easysystems", 'password' => "Lou9ieki"));
                $resultado = $client->GenerarAsientoRemuneraciones($parameters);

                $texto = "RESULTADO: ".$resultado->GenerarAsientoRemuneracionesResult->Exito."\r\n";
                $texto.=" KEY: ".$resultado->GenerarAsientoRemuneracionesResult->Key."\r\n";
                $texto.=" MENSAJE: ".$resultado->GenerarAsientoRemuneracionesResult->Mensaje."\r\n";
                
                
                
                if( $resultado->GenerarAsientoRemuneracionesResult->Exito ){
                    $comprobanteRRhh->numero = $resultado->GenerarAsientoRemuneracionesResult->Key;
                    $comprobanteRRhh->save();
                    $respuesta=array(
                        'success' => true,
                        'mensaje' => $texto,
                        'comprobante' => $comprobante,
                        'obj' => $parameters
                    );
                }else{
                    $respuesta=array(
                        'success' => false,
                        'mensaje' => $texto,
                        'comprobante' => $comprobante,
                        'obj' => $parameters
                    );
                }

            } catch (Exception $e) {
                $texto =  "Exception Error!";
                $texto .= $e->getMessage();
                $respuesta=array(
                    'success' => false,
                    'mensaje' => $texto,
                    'comprobante' => $comprobante
                );
            }
                        
            
        }else{
            $objeto = $comp;
            
            /*
            **
            **
            **
            **
            
                ******CENTRALIZACIÓN A CME********
            
            **
            **
            **
            **
            */
            
        }
        
        return Response::json($respuesta);
    }
    
    public function preCentralizar()
    {
        $empresa = \Session::get('empresa');
        $datos = Input::all();
        $mes = Session::get('mesActivo');
        $lista = Trabajador::centralizar($mes->mes, $empresa->id);
        $data = $this->simularCentralizacion($lista);
        $nombrePdf = "";
        $nombreExcel = "";
        
        if(!$data['errores'] && !$empresa->cme){
            $documento = MesDeTrabajo::generarDocumentos($empresa->razon_social, $data['comprobante'], $data['centrosCostos'], $data['sumaDebe'], $data['sumaHaber']);            
            $nombrePdf = $documento['pdf'];
            $nombreExcel = $documento['excel'];
        }
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $data,
            'nombreDocumentoPDF' => $nombrePdf,
            'nombreDocumentoExcel' => $nombreExcel
        );
        
        return Response::json($datos);
    }

    public function arregloCentroCostos(&$arreglo, $nuevaKey){
        if( !array_key_exists($nuevaKey, $arreglo) ){
            $arreglo[$nuevaKey]=array();
        }
    }

    public function construirArregloCtaFinal($detalles, &$cuentasFinal, &$codigosCentroCosto, $periodo, $cuentas){
        $cuentasCodigo = Funciones::array_column($cuentas, 'codigo', 'id');
        if(array_key_exists('detallesCC', $detalles)){
            $totalesCC=array(
                'debe' => 0,
                'haber' => 0
            );
            $detalleCentroCosto=array();
			$centroCostoID = 0;
            foreach( $detalles['detallesCC'] as $idcuenta => $arrayCta ){
                foreach( $arrayCta as $nombreItem => $detalle ){
					$centroCostoID = $detalle['centroCosto'];
                    $detalleCentroCosto[]=array(
                        'tipo' => $detalle['tipo'],
                        'Cuenta' => $detalle['cuenta'],
                        'Debe' => $detalle['debe'],
                        'Haber' => $detalle['haber'],
                        'Comentario' => $detalle['nombreItem'],
                        'Referencia' => date("m-Y", strtotime($periodo))
                    );
                    foreach($codigosCentroCosto as $nivel =>  $codigo){
                        $detalleCentroCosto[ count($detalleCentroCosto)-1 ]['centroCosto'.($nivel+1 )]=$codigo;
                    }

                    $totalesCC['debe']+=$detalle['debe'];
                    $totalesCC['haber']+=$detalle['haber'];

                    if( $detalle['tipo']=="aportes" ){
                        $cotizaciones = Aporte::where('tipo_aporte', 8)->first();
                        $detalleCentroCosto[]=array(
                            'tipo' => $detalle['tipo'],
                            'Cuenta' => $cotizaciones->cuenta($cuentasCodigo, $detalle['centroCosto']),
                            'Debe' => $detalle['haber'],
                            'Haber' => $detalle['debe'],
                            'Comentario' => $detalle['nombreItem'],
                            'Referencia' => date("m-Y", strtotime($periodo))
                        );
                        foreach($codigosCentroCosto as $nivel =>  $codigo){
                            $detalleCentroCosto[ count($detalleCentroCosto)-1 ]['centroCosto'.($nivel+1)]=$codigo;
                        }
                        $totalesCC['debe']+=$detalle['haber'];
                        $totalesCC['haber']+=$detalle['debe'];
                    }
                }
            }
            if( $totalesCC['debe'] > 0 or $totalesCC['haber'] > 0 ){
                $diferencia = $totalesCC['debe'] - $totalesCC['haber'];
                if( $diferencia >= 0 ){
                    $debe=0;
                    $haber=$diferencia;
                }else{
                    $debe = $diferencia*-1;
                    $haber=0;
                }
                $remuneraciones = Aporte::where('tipo_aporte', 7)->first();

                // se debe ordenar por debe y luego por haber
                $listaDebe=array();
                $listaHaber=array();
                if( count($detalleCentroCosto) ) {
                    foreach ($detalleCentroCosto as $index => $valor) {
                        $listaDebe[$index] = $valor['Debe'];
                        $listaHaber[$index] = $valor['Haber'];
                    }

                    array_multisort($listaHaber, SORT_ASC, $listaDebe, SORT_ASC, $detalleCentroCosto);
                }

                $detalleCentroCosto[]=array(
                    'Cuenta' => $remuneraciones->cuenta($cuentasCodigo, $centroCostoID),
                    'Debe' => $debe,
                    'Haber' => $haber,
                    'Comentario' => $remuneraciones->nombre,
                    'Referencia' => date("m-Y", strtotime($periodo))
                );
                foreach($codigosCentroCosto as $nivel =>  $codigo){
                    $detalleCentroCosto[ count($detalleCentroCosto)-1 ]['centroCosto'.($nivel+1 )]=$codigo;
                }

                $cuentasFinal=array_merge($cuentasFinal, $detalleCentroCosto);
                $codigosCentroCosto=array();
            }

        }else{

            foreach( $detalles as $key => $detalle ){
                $codigosCentroCosto[]=$key;
                $this->construirArregloCtaFinal($detalles[$key], $cuentasFinal, $codigosCentroCosto, $periodo, $cuentas);
            }
        }
    }

    public function simularCentralizacion($datos)
    {
        $general=null;$detalles=null;$listaCuentas=null;

        if (array_key_exists('general', $datos)) {
            $general = $datos['general'];
        }
        if (array_key_exists('detalle', $datos)) {
            $detalles = $datos['detalle'];
            if(is_array($general)) {
                if (array_key_exists('cuentas', $general)) {
                    if (count($general['cuentas'])) {
                        foreach ($general['cuentas'] as $cuenta) {
                            $listaCuentas[$cuenta['codigo']] = $cuenta;
                        }
                        // nivel centros de costos de la empresa
                        $empresa = Empresa::find( \Session::get('empresa')->id);
                        $datosResumen=array(
                            'general' => array(),
                            'detalles' => array()
                        );
                        
                        $errores=array();
                        $totalDebe=$totalHaber=0;
                            
                        if($empresa->centro_costo){
                            $nivelCentroCosto = $empresa->niveles_centro_costo;
                            $centroCosto=null;
                            $centrosCostosTitulos=array();
                            $nivelesCentrosCostos=array();
                            $listaCentros = CentroCosto::listaCentrosCosto();
                            $listaCentrosCostos = CentroCosto::listaCentrosCostoCuentas();
                            if(count($listaCentros)){
                                if(count($listaCentrosCostos)){
                                    foreach( $listaCentrosCostos as $centro ){
                                        $nivelesCodigo=array();
                                        for($nivel=1; $nivel <= $nivelCentroCosto; $nivel++ ){
                                            $centroCostoCodigo="";
                                            if( $nivel == 1 ){
                                                $centroCosto = CentroCosto::find( $centro['id'] );
                                                if( $centroCosto ){
                                                    $centroCostoCodigo = $centroCosto->codigo;
                                                }
                                            }else{
                                                if( $centroCosto ){
                                                    $centroCosto = CentroCosto::find( $centroCosto->dependencia_id );
                                                    if($centroCosto){
                                                        $centroCostoCodigo = $centroCosto->codigo;
                                                    }
                                                }
                                            }
                                            $centrosCostosTitulos[$nivel]=$centroCosto->columna();
                                            $nivelesCodigo[]=$centroCostoCodigo;
                                        }
                                        $nivelesCentrosCostos[$centro['id']]=array_reverse($nivelesCodigo);
                                    }
                                    $centrosCostosTitulos = array_reverse($centrosCostosTitulos);
                                    
                                    if (is_array($general) and is_array($detalles) and is_array($listaCuentas)) {
                                        // se construye el asiento contable
                                        if (count($detalles)) {
                                            foreach ($detalles as $detalle) {
                                                if($detalle['centroCosto']){
                                                    $cotizaciones = Aporte::where('tipo_aporte', 8)->first();
                                                    $remuneraciones = Aporte::where('tipo_aporte', 7)->first();
                                                    $cuentasCodigo = Funciones::array_column($listaCuentas, 'codigo', 'id');
                                                    if(!$remuneraciones->cuenta($cuentasCodigo, $detalle['centroCosto'])){
                                                        $errores[]= $detalle['nombreCentroCosto']." --> " . "Aporte : Remuneraciones por Pagar: Cuenta no asignada";
                                                    }
                                                    if(!$cotizaciones->cuenta($cuentasCodigo, $detalle['centroCosto'])){
                                                        $errores[]= $detalle['nombreCentroCosto']." --> " . "Aporte : Cotizaciones por Pagar: Cuenta no asignada";
                                                    }
                                                    // descuentos, haberes y aportes del trabajador
                                                    $arrayDetalles = array('haberes', 'descuentos', 'aportes' );
                                                    foreach( $arrayDetalles as $indexDetalle ) {
                                
                                                        if (count($detalle[$indexDetalle])) {
                                                            foreach ($detalle[$indexDetalle] as $item) {
                                                                if (array_key_exists('nombre', $item)
                                                                    and array_key_exists('monto', $item)
                                                                    and array_key_exists('idCuenta', $item)
                                                                ) {
                                                                    if ( $item['idCuenta'] ) {
                                                                        if (array_key_exists($item['idCuenta'], $listaCuentas)) {
                                                                            if( $item['monto'] != 0  ) {
                                
                                                                                $arregloDef = &$datosResumen['detalles'];
                                
                                                                                if( array_key_exists( $detalle['centroCosto'], $nivelesCentrosCostos ) ){
                                                                                    foreach( $nivelesCentrosCostos[$detalle['centroCosto']] as $codigoNivel ){
                                                                                        $this->arregloCentroCostos($arregloDef, $codigoNivel);
                                                                                        $arregloDef = &$arregloDef[$codigoNivel];
                                                                                    }
                                                                                }
                                
                                                                                if ( !array_key_exists('detallesCC', $arregloDef) ) {
                                                                                    $arregloDef['detallesCC'] = array();
                                                                                }
                                
                                                                                if ( !array_key_exists($item['idCuenta'], $arregloDef['detallesCC']) ) {
                                                                                    $arregloDef['detallesCC'][$item['idCuenta']] = array();
                                                                                }
                                
                                                                                if (!array_key_exists($item['nombre'], $arregloDef['detallesCC'][$item['idCuenta']])) {
                                                                                    $arregloDef['detallesCC'][$item['idCuenta']][$item['nombre']] = array(
                                                                                        'debe' => 0,
                                                                                        'haber' => 0,
                                                                                        'tipo' => $indexDetalle,
                                                                                        'cuenta' => $listaCuentas[$item['idCuenta']]['codigo'],
                                                                                        'nombreItem' => $item['nombre'],
                                                                                        'centroCosto' => $detalle['centroCosto']
                                                                                    );
                                                                                }
                                
                                                                                if ($listaCuentas[$item['idCuenta']]['comportamiento'] == "1") {
                                                                                    // debe
                                                                                    if ($item['monto'] > 0) {
                                                                                        $debe = $item['monto'];
                                                                                        $haber = 0;
                                                                                    } else {
                                                                                        $haber = $item['monto'] * -1;
                                                                                        $debe = 0;
                                                                                    }
                                                                                } elseif ($listaCuentas[$item['idCuenta']]['comportamiento'] == "2") {
                                                                                    // haber
                                                                                    if ($item['monto'] > 0) {
                                                                                        $haber = $item['monto'];
                                                                                        $debe = 0;
                                                                                    } else {
                                                                                        $debe = $item['monto'] * -1;
                                                                                        $haber = 0;
                                                                                    }
                                                                                }
                                                                                $totalDebe += $debe;
                                                                                $totalHaber += $haber;
                                                                                if ($debe > 0 or $haber > 0) {
                                                                                    $arregloDef['detallesCC'][$item['idCuenta']][$item['nombre']]['debe'] += $debe;
                                                                                    $arregloDef['detallesCC'][$item['idCuenta']][$item['nombre']]['haber'] += $haber;
                                                                                } else {
                                                                                    $errores[] = "Trabajador: " . $detalle['nombreCompleto'] . " --> " . $indexDetalle . ": " . $item['nombre'] .
                                                                                        " : No Existe monto asociado al Debe o Haber";
                                                                                }
                                                                            }
                                                                        } else {
                                                                            $errores[]=ucwords($indexDetalle) . " : " . $item['nombre']." : Cuenta no registrada en Plan de Cuentas";
                                                                        }
                                                                    }else{
                                                                        $errores[]= $detalle['nombreCentroCosto']." --> " . ucwords($indexDetalle) . " : " . $item['nombre'] . " : Cuenta no asignada";
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }else{
                                                    $errores[] = "Trabajador: " . $detalle['nombreCompleto'] . " -->  No tiene Centro de Costo asociado.";
                                                }
                                            }
                                        }
                                    }
                                }else{
                                    $errores[]= "No se puede realizar la Centralización: No existen Centros de Costo de " . $nivelCentroCosto . "° nivel como se especifica en la ficha de Empresa.";
                                }
                            }else{
                                $errores[]= "No se puede realizar la Centralización: No existen Centros de Costo.";
                            }
                
                    
                            // reconstruir arreglo
                            $comprobanteFinal=array();
                            $sumaDebe = $sumaHaber = 0;
                            if(count($datosResumen['detalles'])) {
                                $codigosCentroCosto=array();
                                $this->construirArregloCtaFinal($datosResumen['detalles'], $comprobanteFinal, $codigosCentroCosto, $general['periodo'], $listaCuentas);
                            }
                    
                            if ($datosResumen ) {
                                $comprobante =new stdclass();
                                $comprobante->Comentario = $general['comentario'];
                                $comprobante->Fecha=date("Y-m-d");
                                $comprobante->Referencia=date("m-Y", strtotime($general['periodo']));
                                $comprobante->Empresa="Amsa";
                                $comprobante->Detalle=array();
                    
                                if(count($comprobanteFinal)) {
                                    foreach ($comprobanteFinal as $detalle) {
                                        $comprobanteDetalle = new stdClass();
                                        $comprobanteDetalle->Cuenta = $detalle['Cuenta'];
                                        $comprobanteDetalle->Debe = $detalle['Debe'];
                                        $comprobanteDetalle->Haber = $detalle['Haber'];
                                        $comprobanteDetalle->Comentario = $detalle['Comentario'];
                                        $comprobanteDetalle->Referencia = $detalle['Referencia'];
                    
                                        for ($nivel = 1; $nivel <= $nivelCentroCosto; $nivel++) {
                                            $campo = "CentroCosto" . $nivel;
                                            if (array_key_exists('centroCosto' . $nivel, $detalle)) {
                                                $comprobanteDetalle->$campo = $detalle['centroCosto' . $nivel];
                                            } else {
                                                $comprobanteDetalle->$campo = 0;
                                            }
                                        }
                                        $comprobante->Detalle[] = $comprobanteDetalle;
                    
                                        $sumaDebe+=$comprobanteDetalle->Debe;
                                        $sumaHaber+=$comprobanteDetalle->Haber;
                                    }
                                }
                                $respuesta = array(
                                    'datosResumen' => $datosResumen,
                                    'comprobanteFinal' => $comprobanteFinal,
                                    'comprobante' => $comprobante,
                                    'sumaDebe' => $sumaDebe,
                                    'sumaHaber' => $sumaHaber,
                                    'errores' => array_values( array_unique($errores) ),
                                    'cuentas' => $listaCuentas,
                                    'nivelCentroCosto' => $nivelCentroCosto,
                                    'centrosCostos' => $centrosCostosTitulos,
                                    'detalles' => $detalles,
                                    'datos' => $datos,
                                    'datosresumenDetalles' => $datosResumen['detalles']
                                );
                            }else{
                                $errores[]= "No existe información";
                                $respuesta = array(
                                    'errores' => $errores,
                                    'general' => $general,
                                    'detalles' => $detalles
                                );
                            }
                        }else{
                            $nivelCentroCosto = $empresa->niveles_centro_costo;
                            $centroCosto=null;
                            $centrosCostosTitulos=array();
                            $nivelesCentrosCostos=array();
                            $listaCentrosCostos = CentroCosto::listaCentrosCostoCuentas();
                            if(count($listaCentrosCostos)){
                                foreach( $listaCentrosCostos as $centro ){
                                    $nivelesCodigo=array();
                                    for($nivel=1; $nivel <= $nivelCentroCosto; $nivel++ ){
                                        $centroCostoCodigo="";
                                        if( $nivel == 1 ){
                                            $centroCosto = CentroCosto::find( $centro['id'] );
                                            if( $centroCosto ){
                                                $centroCostoCodigo = $centroCosto->codigo;
                                            }
                                        }else{
                                            if( $centroCosto ){
                                                $centroCosto = CentroCosto::find( $centroCosto->dependencia_id );
                                                if($centroCosto){
                                                    $centroCostoCodigo = $centroCosto->codigo;
                                                }
                                            }
                                        }
                                        $centrosCostosTitulos[$nivel]=$centroCosto->columna();
                                        $nivelesCodigo[]=$centroCostoCodigo;
                                    }
                                    $nivelesCentrosCostos[$centro['id']]=array_reverse($nivelesCodigo);
                                }
                                $centrosCostosTitulos = array_reverse($centrosCostosTitulos);
                            }
                
                            if (is_array($general) and is_array($detalles) and is_array($listaCuentas)) {
                                // se construye el asiento contable
                                if (count($detalles)) {
                                    foreach ($detalles as $detalle) {
                                        $cotizaciones = Aporte::where('tipo_aporte', 8)->first();
                                        $remuneraciones = Aporte::where('tipo_aporte', 7)->first();
                                        $cuentasCodigo = Funciones::array_column($listaCuentas, 'codigo', 'id');
                                        if(!$remuneraciones->cuenta($cuentasCodigo, $detalle['centroCosto'])){
                                            $errores[]= $detalle['nombreCentroCosto']." --> " . "Aporte : Remuneraciones por Pagar: Cuenta no asignada";
                                        }
                                        if(!$cotizaciones->cuenta($cuentasCodigo, $detalle['centroCosto'])){
                                            $errores[]= $detalle['nombreCentroCosto']." --> " . "Aporte : Cotizaciones por Pagar: Cuenta no asignada";
                                        }
                                        // descuentos, haberes y aportes del trabajador
                                        $arrayDetalles = array('haberes', 'descuentos', 'aportes' );
                                        foreach( $arrayDetalles as $indexDetalle ) {
                    
                                            if (count($detalle[$indexDetalle])) {
                                                foreach ($detalle[$indexDetalle] as $item) {
                                                    if (array_key_exists('nombre', $item)
                                                        and array_key_exists('monto', $item)
                                                        and array_key_exists('idCuenta', $item)
                                                    ) {
                                                        if ( $item['idCuenta'] ) {
                                                            if (array_key_exists($item['idCuenta']['codigo'], $listaCuentas)) {
                                                                if( $item['monto'] != 0  ) {
                    
                                                                    $arregloDef = &$datosResumen['detalles'];
                    
                                                                    if( array_key_exists( $detalle['centroCosto'], $nivelesCentrosCostos ) ){
                                                                        foreach( $nivelesCentrosCostos[$detalle['centroCosto']] as $codigoNivel ){
                                                                            $this->arregloCentroCostos($arregloDef, $codigoNivel);
                                                                            $arregloDef = &$arregloDef[$codigoNivel];
                                                                        }
                                                                    }
                    
                                                                    if ( !array_key_exists('detallesCC', $arregloDef) ) {
                                                                        $arregloDef['detallesCC'] = array();
                                                                    }
                    
                                                                    if ( !array_key_exists($item['idCuenta']['codigo'], $arregloDef['detallesCC']) ) {
                                                                        $arregloDef['detallesCC'][$item['idCuenta']['codigo']] = array();
                                                                    }
                    
                                                                    if (!array_key_exists($item['nombre'], $arregloDef['detallesCC'][$item['idCuenta']['codigo']])) {
                                                                        $arregloDef['detallesCC'][$item['idCuenta']['codigo']][$item['nombre']] = array(
                                                                            'debe' => 0,
                                                                            'haber' => 0,
                                                                            'tipo' => $indexDetalle,
                                                                            'cuenta' => $listaCuentas[$item['idCuenta']['codigo']],
                                                                            'nombreItem' => $item['nombre'],
                                                                            'centroCosto' => $detalle['centroCosto']
                                                                        );
                                                                    }
                    
                                                                    if ($listaCuentas[$item['idCuenta']['codigo']]['comportamiento'] == "1") {
                                                                        // debe
                                                                        if ($item['monto'] > 0) {
                                                                            $debe = $item['monto'];
                                                                            $haber = 0;
                                                                        } else {
                                                                            $haber = $item['monto'] * -1;
                                                                            $debe = 0;
                                                                        }
                                                                    } elseif ($listaCuentas[$item['idCuenta']['codigo']]['comportamiento'] == "2") {
                                                                        // haber
                                                                        if ($item['monto'] > 0) {
                                                                            $haber = $item['monto'];
                                                                            $debe = 0;
                                                                        } else {
                                                                            $debe = $item['monto'] * -1;
                                                                            $haber = 0;
                                                                        }
                                                                    }
                                                                    $totalDebe += $debe;
                                                                    $totalHaber += $haber;
                                                                    if ($debe > 0 or $haber > 0) {
                                                                        $arregloDef['detallesCC'][$item['idCuenta']['codigo']][$item['nombre']]['debe'] += $debe;
                                                                        $arregloDef['detallesCC'][$item['idCuenta']['codigo']][$item['nombre']]['haber'] += $haber;
                                                                    } else {
                                                                        $errores[] = "Trabajador: " . $detalle['nombreCompleto'] . " --> " . $indexDetalle . ": " . $item['nombre'] .
                                                                            " : No Existe monto asociado al Debe o Haber";
                                                                    }
                                                                }
                                                            } else {
                                                                $errores[]=ucwords($indexDetalle) . " : " . $item['nombre']." : Cuenta no registrada en Plan de Cuentas";
                                                            }
                                                        }else{
                                                            $errores[]= $detalle['nombreCentroCosto']." --> " . ucwords($indexDetalle) . " : " . $item['nombre'] . " : Cuenta no asignada";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                    
                            // reconstruir arreglo
                            $comprobanteFinal=array();
                            $sumaDebe = $sumaHaber = 0;
                            if(count($datosResumen['detalles'])) {
                                $codigosCentroCosto=array();
                                $this->construirArregloCtaFinal($datosResumen['detalles'], $comprobanteFinal, $codigosCentroCosto, $general['periodo'], $listaCuentas);
                            }
                    
                            if ($datosResumen ) {
                                $comprobante =new stdclass();
                                $comprobante->Comentario = $general['comentario'];
                                $comprobante->Fecha=date("Y-m-d");
                                $comprobante->Referencia=date("m-Y", strtotime($general['periodo']));
                                $comprobante->Empresa="Amsa";
                                $comprobante->Detalle=array();
                    
                                if(count($comprobanteFinal)) {
                                    foreach ($comprobanteFinal as $detalle) {
                                        $comprobanteDetalle = new stdClass();
                                        $comprobanteDetalle->Cuenta = $detalle['Cuenta'];
                                        $comprobanteDetalle->Debe = $detalle['Debe'];
                                        $comprobanteDetalle->Haber = $detalle['Haber'];
                                        $comprobanteDetalle->Comentario = $detalle['Comentario'];
                                        $comprobanteDetalle->Referencia = $detalle['Referencia'];
                    
                                        for ($nivel = 1; $nivel <= $nivelCentroCosto; $nivel++) {
                                            $campo = "CentroCosto" . $nivel;
                                            if (array_key_exists('centroCosto' . $nivel, $detalle)) {
                                                $comprobanteDetalle->$campo = $detalle['centroCosto' . $nivel];
                                            } else {
                                                $comprobanteDetalle->$campo = 0;
                                            }
                                        }
                                        $comprobante->Detalle[] = $comprobanteDetalle;
                    
                                        $sumaDebe+=$comprobanteDetalle->Debe;
                                        $sumaHaber+=$comprobanteDetalle->Haber;
                                    }
                                }
                                
                                $respuesta = array(
                                    'datosResumen' => $datosResumen,
                                    'comprobanteFinal' => $comprobanteFinal,
                                    'comprobante' => $comprobante,
                                    'sumaDebe' => $sumaDebe,
                                    'sumaHaber' => $sumaHaber,
                                    'errores' => array_values( array_unique($errores) ),
                                    'cuentas' => $listaCuentas,
                                    'nivelCentroCosto' => $nivelCentroCosto,
                                    'centrosCostos' => $centrosCostosTitulos,
                                    'detalles' => $detalles,
                                    'datos' => $datos,
                                    'datosresumenDetalles' => $datosResumen['detalles']
                                );
                            }else{
                                $errores[]= "No existe información";
                                $respuesta = array(
                                    'errores' => $errores,
                                    'general' => $general,
                                    'detalles' => $detalles
                                );
                            }
                        }
                    }else{
                        $errores[]= "No se puede realizar la Centralización: No existen Cuentas de Destino.";
                        $respuesta = array(
                            'errores' => $errores,
                                'general' => $general,
                                'detalles' => $detalles
                        );
                    }
                }
            }
        }else{
            $errores[]= "No se puede realizar la Centralización: No existen Trabajadores.";
            $respuesta = array(
                'errores' => array_values( array_unique($errores) )
            );       
        }

        
        return $respuesta;
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($sid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($sid)
    {
        $mesDeTrabajo = MesDeTrabajo::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = MesDeTrabajo::errores($datos);       
        
        if(!$errores and $mesDeTrabajo){
            $mesDeTrabajo->mes = $datos['mes'];
            $mesDeTrabajo->nombre = $datos['nombre'];
            $mesDeTrabajo->fecha_remuneracion = $datos['fecha_remuneracion'];
            $mesDeTrabajo->anio = $datos['anio'];
            $mesDeTrabajo->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $mesDeTrabajo->id
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        } 
        return Response::json($respuesta);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($sid)
    {
        $mensaje="La Información fue eliminada correctamente";
        MesDeTrabajo::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'mes' => Input::get('mes'),
            'nombre' => Input::get('nombre'),
            'fecha_remuneracion' => Input::get('fechaRemuneracion'),
            'anio_id' => Input::get('idAnio')
        );
        
        return $datos;
    }

}
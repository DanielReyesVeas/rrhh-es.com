<?php

class LiquidacionesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array()));
        }
        $mes = \Session::get('mesActivo')->mes;
        $liquidaciones = Liquidacion::where('mes', $mes)->get();
        $listaLiquidaciones=array();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#nomina-bancaria');
        
        if( $liquidaciones->count() ){
            foreach( $liquidaciones as $liquidacion ){
                $id = $liquidacion->trabajador_id;
                $trabajador = Trabajador::find($id);
                
                $listaLiquidaciones[]=array(
                    'id' => $liquidacion->id,
                    'idTrabajador' => $liquidacion->trabajador_id,
                    'sid' => $liquidacion->sid,
                    'rutFormato' => $liquidacion->trabajador->rut_formato(),
                    'apellidos' => $liquidacion->trabajador_apellidos,
                    'nombreCompleto' => $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos,
                    'cargo' => array(
                        'nombre' => $liquidacion->trabajador_cargo
                    ),
                    'seccion' => array(
                        'nombre' => $liquidacion->trabajador_seccion
                    ),
                    'fechaIngreso' => $liquidacion->trabajador_fecha_ingreso,
                    'tipoContrato' => array(
                        'nombre' => $liquidacion->tipo_contrato
                    ),
                    'numeroCuenta' => $liquidacion->trabajador->ficha()->numero_cuenta,
                    'tipoCuenta' => array(
                        'id' => $liquidacion->trabajador->ficha()->tipoCuenta ? $liquidacion->trabajador->ficha()->tipoCuenta->id : "",
                        'nombre' => $liquidacion->trabajador->ficha()->tipoCuenta ? $liquidacion->trabajador->ficha()->tipoCuenta->nombre : ""
                    ),
                    'banco' => array(
                        'id' => $liquidacion->trabajador->ficha()->banco ? $liquidacion->trabajador->ficha()->banco->id : "",
                        'codigo' => $liquidacion->trabajador->ficha()->banco ? $liquidacion->trabajador->ficha()->banco->codigo : "",
                        'nombre' => $liquidacion->trabajador->ficha()->banco ? $liquidacion->trabajador->ficha()->banco->nombre : ""
                    ),
                    'sueldoBasePesos' => $liquidacion->sueldo_base,
                    'sueldo' => $liquidacion->sueldo,
                    'sueldoLiquido' => $liquidacion->sueldo_liquido
                    
                );
            }
        }
        
        $listaLiquidaciones = Funciones::ordenar($listaLiquidaciones, 'apellidos');
        
        $datos = array(
            'datos' => $listaLiquidaciones,
            'accesos' => $permisos
            
        );
        
        return Response::json($datos);
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
        $datos = Input::all();
        $mes = \Session::get('mesActivo')->mes;
        
        foreach($datos as $dato){
            
            $filename = date("d-m-Y-H-i-s")."_Liquidacion_".$dato['rut']. '.pdf';
            $destination = public_path() . '/stories/' . $filename;
                        
            File::put($destination, PDF::load(utf8_decode($dato['cuerpo']), 'A4', 'portrait')->output());
            $documento = new Documento();
            $documento->sid = Funciones::generarSID();
            $documento->trabajador_id = $dato['id'];
            $documento->tipo_documento_id = 4;
            $documento->nombre = $filename;
            $documento->alias = 'Liquidación ' . $dato['nombres'] . ' ' . $dato['apellidos'] . '.pdf';
            $documento->descripcion = 'Liquidación de Sueldo de ' . $dato['nombres'] . ' ' . $dato['apellidos'];
            $documento->save();
            $totalApv = 0;
            if($dato['apvs']){    
                
                foreach($dato['apvs'] as $apv)
                {
                    $totalApv = ($totalApv + $apv['montoPesos']);
                }
            }
            
            $liquidacion = new Liquidacion();
            $liquidacion->sid = Funciones::generarSID();
            $liquidacion->trabajador_id = $dato['id'];
            $liquidacion->empresa_id = $dato['empresa']['id'];
            $liquidacion->empresa_razon_social = $dato['empresa']['empresa'];
            $liquidacion->empresa_rut = $dato['empresa']['rut'];
            $liquidacion->empresa_direccion = $dato['empresa']['direccion'];
            $liquidacion->encargado_id = $dato['id'];
            $liquidacion->mes = $mes;
            $liquidacion->folio = 45646548;
            $liquidacion->estado = 1;
            $liquidacion->trabajador_rut = $dato['rut'];
            $liquidacion->trabajador_nombres = $dato['nombres'];
            $liquidacion->trabajador_apellidos = $dato['apellidos'];
            $liquidacion->trabajador_cargo = $dato['cargo']['nombre'];
            $liquidacion->trabajador_seccion = $dato['seccion']['nombre'];
            $liquidacion->trabajador_fecha_ingreso = $dato['fechaIngreso'];
            $liquidacion->uf = $dato['uf'];
            $liquidacion->utm = $dato['utm'];
            $liquidacion->dias_trabajados = $dato['diasTrabajados'];
            $liquidacion->horas_extra = $dato['horasExtra']['cantidad'];
            $liquidacion->total_horas_extra = $dato['horasExtra']['total'];
            $liquidacion->tipo_contrato = $dato['tipoContrato']['nombre'];
            $liquidacion->sueldo_base = $dato['sueldoBase'];
            $liquidacion->total_afp = $dato['totalAfp'];
            $liquidacion->total_apv = $totalApv;
            $liquidacion->tasa_afp = $dato['tasaAfp'];
            $liquidacion->nombre_afp = $dato['afp']['nombre'];
            $liquidacion->total_salud = $dato['totalSalud']['total'];
            $liquidacion->base_salud = $dato['totalSalud']['obligatorio'];
            $liquidacion->nombre_salud = $dato['isapre']['nombre'];
            $liquidacion->id_salud = $dato['isapre']['id'];
            $liquidacion->nombre_afp = $dato['afp']['nombre'];
            $liquidacion->seguro_cesantia = $dato['seguroDesempleo'];
            $liquidacion->total_seguro_cesantia = $dato['totalSeguroCesantia']['total'];
            $liquidacion->afc_seguro_cesantia = $dato['totalSeguroCesantia']['afc'];
            $liquidacion->base_impuesto_unico = $dato['baseImpuestoUnico'];
            $liquidacion->impuesto_determinado = $dato['impuestoDeterminado'];
            $liquidacion->tramo_impuesto = $dato['tramoImpuesto'];
            $liquidacion->imponibles = $dato['imponibles'];
            $liquidacion->no_imponibles = $dato['noImponibles'];
            $liquidacion->total_otros_descuentos = $dato['totalOtrosDescuentos'];
            $liquidacion->total_anticipos = $dato['totalAnticipos'];
            $liquidacion->total_descuentos_previsionales = $dato['totalDescuentosPrevisionales'];
            $liquidacion->total_descuentos = ($dato['totalDescuentosPrevisionales'] + $dato['totalOtrosDescuentos']);
            $liquidacion->renta_imponible = $dato['rentaImponible'];
            $liquidacion->total_haberes = $dato['totalHaberes'];
            $liquidacion->total_cargas = $dato['cargasFamiliares']['monto'];
            $liquidacion->cantidad_cargas = $dato['cargasFamiliares']['cantidad'];
            $liquidacion->sueldo = $dato['sueldo'];
            $liquidacion->sueldo_diario = $dato['sueldoDiario'];
            $liquidacion->sueldo_liquido = $dato['sueldoLiquido'];
            $liquidacion->gratificacion = $dato['gratificacion'];
            $liquidacion->colacion = $dato['colacion']['montoPesos'];
            $liquidacion->movilizacion = $dato['movilizacion']['montoPesos'];
            $liquidacion->viatico = $dato['viatico']['montoPesos'];
            $liquidacion->centro_costo_id = $dato['centroCosto'];
            
            $liquidacion->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $liquidacion->sid
            );
            
            if($dato['haberes']){    
                
                foreach($dato['haberes'] as $haber)
                {
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = $haber['tipo']['nombre'];
                    $detalleLiquidacion->tipo = $haber['tipo']['imponible'] ? 'imponible' : 'no imponible';
                    $detalleLiquidacion->tipo_id = 1;
                    $detalleLiquidacion->valor = $haber['montoPesos'];
                    $detalleLiquidacion->valor_2 = $haber['monto'];
                    $detalleLiquidacion->valor_3 = $haber['moneda'];
                    $detalleLiquidacion->valor_4 = null;
                    $detalleLiquidacion->valor_5 = null;
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->save(); 
                }
            }
            if($dato['descuentos']){    
                
                foreach($dato['descuentos'] as $descuento)
                {
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = $descuento['tipo']['nombre'];
                    $detalleLiquidacion->tipo = 'descuento';
                    $detalleLiquidacion->tipo_id = 2;
                    $detalleLiquidacion->valor = $descuento['montoPesos'];
                    $detalleLiquidacion->valor_2 = $descuento['monto'];
                    $detalleLiquidacion->valor_3 = $descuento['moneda'];
                    if($descuento['tipo']['nombre']=='Anticipos'){
                        $detalleLiquidacion->valor_4 = 1;   
                    }else{
                        $detalleLiquidacion->valor_4 = null;   
                    }
                    $detalleLiquidacion->valor_5 = null;
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->save(); 
                }
            }
            
            if($dato['apvs']){    
                
                foreach($dato['apvs'] as $apv)
                {
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = $apv['afp']['nombre'];
                    $detalleLiquidacion->tipo = 'apv';
                    $detalleLiquidacion->tipo_id = 3;
                    $detalleLiquidacion->valor = $apv['montoPesos'];
                    $detalleLiquidacion->valor_2 = $apv['monto'];
                    $detalleLiquidacion->valor_3 = $apv['moneda'];
                    $detalleLiquidacion->valor_4 = $apv['afp']['id'];
                    $detalleLiquidacion->valor_5 = null;
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->save(); 
                }
            }
            
            if($dato['prestamos']){    
                
                foreach($dato['prestamos'] as $prestamo)
                {
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = $prestamo['nombreLiquidacion'];
                    $detalleLiquidacion->tipo = 'prestamo';
                    $detalleLiquidacion->tipo_id = 4;
                    $detalleLiquidacion->valor = $prestamo['cuotaPagar']['monto'];
                    $detalleLiquidacion->valor_2 = $prestamo['monto'];
                    $detalleLiquidacion->valor_3 = $prestamo['moneda'];
                    $detalleLiquidacion->valor_4 = $prestamo['cuotaPagar']['numero'];
                    $detalleLiquidacion->valor_5 = $prestamo['cuotas'];
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->save(); 
                }
            }
        }
        
        return Response::json($respuesta);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
        
    
    public function libroRemuneraciones()
    {        
        $mes = \Session::get('mesActivo');
        $empresa = \Session::get('empresa');
        $liquidaciones = Liquidacion::where('mes', $mes->mes)->get();
        $listaLiquidaciones=array();
        $sumaSueldoBase = 0;
        $sumaInasistenciasAtrasos = 0;
        $sumaHorasExtra = 0;
        $sumaSueldo = 0;
        $sumaSalud = 0;
        $sumaAfp = 0;
        $sumaApv = 0;
        $sumaGratificacion = 0;
        $sumaMutual = 0;
        $sumaImpuestoRenta = 0;
        $sumaAnticipos = 0;
        $sumaAsignacionFamiliar = 0;
        $sumaNoImponibles = 0;
        $sumaHaberes = 0;
        $sumaImponibles = 0;
        $sumaTotalImponibles = 0;
        $sumaSeguroCesantia = 0;
        $sumaTotalDescuentos = 0;
        $sumaOtrosDescuentos = 0;
        $sumaSueldoLiquido = 0;
        
        if( $liquidaciones->count() ){
            foreach( $liquidaciones as $liquidacion ){
                $totalApvs = $liquidacion->totalApvs();
                $totalSalud = $liquidacion->totalSalud();
                $otrosDescuentos = ($liquidacion->total_otros_descuentos - $totalApvs - $liquidacion->total_anticipos);
                $sis = 0;
                $cotizacion = $liquidacion->detalleAfp ? $liquidacion->detalleAfp->cotizacion : 0;
                if($liquidacion->detalleAfp){
                    if($liquidacion->detalleAfp->paga_sis=='empleado'){
                        $sis = $liquidacion->detalleAfp ? $liquidacion->detalleAfp->sis : 0;
                    }
                }
                $totalAfp = ($cotizacion + $sis);
                $totalSeguroCesantia = $liquidacion->detalleSeguroCesantia ? $liquidacion->detalleSeguroCesantia->aporte_trabajador : 0;
                if($liquidacion->centroCosto){
                    $centro = $liquidacion->centroCosto->nombre;
                }else{
                    $empleado = $liquidacion->trabajador->ficha();
                    if($empleado->centroCosto){
                        $centro = $empleado->centroCosto->nombre;
                    }else{
                        $centro = '';
                    }
                }
                $listaLiquidaciones[]=array(
                    'id' => $liquidacion->id,
                    'sid' => $liquidacion->sid,
                    'apellidos' => $liquidacion->trabajador_apellidos,
                    'nombreTrabajador' => $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos,
                    'rutTrabajador' => $liquidacion->trabajador_rut,
                    'idTrabajador' => $liquidacion->trabajador_id,
                    'rutFormatoTrabajador' => $liquidacion->trabajador->rut_formato(),
                    'seccion' => $liquidacion->trabajador_seccion,
                    'centroCosto' => $centro,
                    'cargo' => $liquidacion->trabajador_cargo,   
                    'tipoContrato' => $liquidacion->tipo_contrato,   
                    'sueldoBase' => $liquidacion->sueldo_base,
                    'diasTrabajados' => $liquidacion->dias_trabajados,
                    'inasistenciasAtrasos' => $liquidacion->inasistencias,
                    'horasExtra' => $liquidacion->horas_extra,
                    'sueldo' => $liquidacion->sueldo,
                    'totalSalud' => $totalSalud,
                    'totalAfp' => $totalAfp,
                    'sis' => $sis,
                    'totalApv' => $totalApvs,
                    'gratificacion' => $liquidacion->gratificacion,
                    'mutual' => $liquidacion->total_mutual,
                    'impuestoRenta' => $liquidacion->impuesto_determinado,
                    'anticipos' => $liquidacion->total_anticipos,
                    'asignacionFamiliar' => $liquidacion->total_cargas,
                    'noImponibles' => $liquidacion->no_imponibles,
                    'totalHaberes' => $liquidacion->total_haberes,
                    'imponibles' => $liquidacion->imponibles,
                    'totalImponibles' => $liquidacion->imponibles,
                    'seguroCesantia' => $totalSeguroCesantia,
                    'totalDescuentos' => $liquidacion->total_descuentos,
                    'totalOtrosDescuentos' => $otrosDescuentos,
                    'sueldoLiquido' => $liquidacion->sueldo_liquido
                );     
                $sumaSueldoBase += $liquidacion->sueldo_base;
                $sumaInasistenciasAtrasos += $liquidacion->inasistencias;
                $sumaHorasExtra += $liquidacion->horas_extra;
                $sumaSueldo += $liquidacion->sueldo;
                $sumaSalud += $totalSalud;
                $sumaAfp += $totalAfp;
                $sumaApv += $totalApvs;
                $sumaGratificacion += $liquidacion->gratificacion;
                $sumaMutual += $liquidacion->total_mutual;
                $sumaImpuestoRenta += $liquidacion->impuesto_determinado;
                $sumaAnticipos += $liquidacion->total_anticipos;
                $sumaAsignacionFamiliar += $liquidacion->total_cargas;
                $sumaNoImponibles += $liquidacion->no_imponibles;
                $sumaHaberes += $liquidacion->total_haberes;
                $sumaImponibles += $liquidacion->imponibles;
                $sumaTotalImponibles += $liquidacion->renta_imponible;
                $sumaSeguroCesantia += $totalSeguroCesantia;
                $sumaTotalDescuentos += $liquidacion->total_descuentos;
                $sumaOtrosDescuentos += $otrosDescuentos;
                $sumaSueldoLiquido += $liquidacion->sueldo_liquido;
            }
        }       
        
        $listaLiquidaciones = Funciones::ordenar($listaLiquidaciones, 'apellidos');
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaLiquidaciones,
            'isIndicadores' => $mes->indicadores,
            'totales' => array(
                'totalSueldoBase' => $sumaSueldoBase,
                'totalInasistenciasAtrasos' => $sumaInasistenciasAtrasos,
                'totalHorasExtra' => $sumaHorasExtra,
                'totalSueldo' => $sumaSueldo,
                'totalSalud' => $sumaSalud,
                'totalAfp' => $sumaAfp,
                'totalApv' => $sumaApv,
                'totalGratificacion' => $sumaGratificacion,
                'totalMutual' => $sumaMutual,
                'totalImpuestoRenta' => $sumaImpuestoRenta,
                'totalAnticipos' => $sumaAnticipos,
                'totalAsignacionFamiliar' => $sumaAsignacionFamiliar,
                'totalNoImponibles' => $sumaNoImponibles,
                'totalHaberes' => $sumaHaberes,
                'totalImponibles' => $sumaImponibles,
                'totalTotalImponibles' => $sumaTotalImponibles,
                'totalSeguroCesantia' => $sumaSeguroCesantia,
                'totalTotalDescuentos' => $sumaTotalDescuentos,
                'totalOtrosDescuentos' => $sumaOtrosDescuentos,
                'totalSueldoLiquido' => $sumaSueldoLiquido
            )
        );
        
        return Response::json($datos);    
    }
    
    public function show($sid)
    {
        
        $liquidacion = Liquidacion::whereSid($sid)->first();        
        
        $excedente = 0;
        $adicional = 0;
        $resto = ($liquidacion->total_salud - $liquidacion->base_salud);
        if($resto > 0){
            $adicional = $resto;
        }else{
            $excedente = ($resto * -1);
        }
        
        $datosLiquidacion = array(
            'id' => $liquidacion->id,
            'sid' => $liquidacion->sid,
            'rutFormato' => $liquidacion->trabajador->rut_formato(),
            'nombreCompleto' => $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos,
            'cargo' => array(
                'nombre' => $liquidacion->trabajador_cargo
            ),
            'seccion' => array(
                'nombre' => $liquidacion->trabajador_seccion
            ),
            'fechaIngreso' => $liquidacion->trabajador_fecha_ingreso,
            'tipoContrato' => array(
                'nombre' => $liquidacion->tipo_contrato
            ),
            'afp' => array(
                'nombre' => $liquidacion->nombre_afp
            ),
            'tasaAfp' => $liquidacion->tasa_afp,
            'totalAfp' => $liquidacion->total_afp,
            'seguroDesempleo' => $liquidacion->seguro_cesantia,
            'totalSeguroCesantia' => array(
                'afc' => $liquidacion->afc_seguro_cesantia,
                'total' => $liquidacion->total_seguro_cesantia
            ),
            'isapre' => array(
                'id' => $liquidacion->id_salud,
                'nombre' => $liquidacion->nombre_salud
            ),
            'totalSalud' => array(
                'obligatorio' => $liquidacion->base_salud,
                'adicional' => $adicional,
                'excedente' => $excedente,
                'total' => $liquidacion->total_salud
            ),
            'horasExtra' => array(
                'cantidad' => $liquidacion->horas_extra,
                'total' => $liquidacion->total_horas_extra
            ),
            'sueldoBase' => $liquidacion->sueldo_base,
            'colacion' => array(
                'montoPesos' => $liquidacion->colacion
            ),
            'movilizacion' => array(
                'montoPesos' => $liquidacion->movilizacion
            ),
            'viatico' => array(
                'montoPesos' => $liquidacion->viatico
            ),
            'estado' => $liquidacion->estado,
            'diasTrabajados' => $liquidacion->dias_trabajados,
            'sueldoDiario' => $liquidacion->sueldo_diario,
            'sueldo' => $liquidacion->sueldo,
            'gratificacion' => $liquidacion->gratificacion,
            'imponibles' => $liquidacion->imponibles,
            'baseImpuestoUnico' => $liquidacion->base_impuesto_unico,
            'impuestoDeterminado' => $liquidacion->impuesto_determinado,
            'tramoImpuesto' => $liquidacion->tramo_impuesto,
            'cargasFamiliares' => $liquidacion->misCargas(),
            'noImponibles' => $liquidacion->no_imponibles,
            'rentaImponible' => $liquidacion->renta_imponible,
            'totalDescuentosPrevisionales' => $liquidacion->total_descuentos_previsionales,
            'totalOtrosDescuentos' => $liquidacion->total_otros_descuentos,
            'sueldoLiquido' => $liquidacion->sueldo_liquido,
            'haberes' => $liquidacion->misHaberes(),
            'apvs' => $liquidacion->misApvs(),
            'prestamos' => $liquidacion->misPrestamos(),
            'descuentos' => $liquidacion->misDescuentos()
        );

        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosLiquidacion
        );

        return Response::json($datos);
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
        $liquidacion = Liquidacion::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Liquidacion::errores($datos);       
        
        if(!$errores and $liquidacion){
            $liquidacion->trabajador_id = $datos['trabajador_id'];
            $liquidacion->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $liquidacion->sid
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
    public function eliminarMasivo()
    {
        $sids = (array) Input::get('trabajadores');
   
        foreach($sids as $sid){
            $sids[] = $sid['sid'];
        }
        $documentos = Documento::whereIn('sid', $sids)->get();
        $mensaje = "La Información fue eliminada correctamente";
        
        foreach($documentos as $documento){
            $documento->eliminarDocumento();            
        }

        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function imprimirMasivo()
    {
        $sids = (array) Input::all();
        $html = "";
        
        foreach($sids as $sid){
            $sids[] = $sid['sid'];
        }
        $liquidaciones = Liquidacion::whereIn('sid', $sids)->get()->toArray();
        $liquidaciones = Funciones::ordenar($liquidaciones, 'trabajador_apellidos');
        
        if(count($liquidaciones)){            
            foreach($liquidaciones as $liquidacion){
                if($liquidacion['cuerpo']){
                    $html = $html . $liquidacion['cuerpo'] . '<div style="page-break-after: always;"></div>';                
                }else{
                    $liquidacion = Liquidacion::find($liquidacion['id']);
                    $html = $html . $liquidacion->generarCuerpo() . '<div style="page-break-after: always;"></div>';
                }
            }
            $destination = public_path() . '/stories/liquidaciones.pdf';
            $pdf = new \Thujohn\Pdf\Pdf();
            $content = $pdf->load($html, 'A4', 'portrait')->output();
            File::put($destination, $content); 
        }
        
        $datos = array(
            'success' => true,
            'mensaje' => "La Información fue generada correctamente",
            'liquidaciones' => $liquidaciones
        );
        
        return Response::json($datos);
    }
    
    public function destroy($sid)
    {
        $mensaje = "La Información fue eliminada correctamente";
        $documento = Documento::whereSid($sid)->first();
        
        $trabajador = $documento->trabajador;
        $ficha = $trabajador->ficha();
        
        Logs::crearLog('#liquidaciones-de-sueldo', $documento->id, $documento->alias, 'Delete', $documento->trabajador_id, $ficha->nombreCompleto(), 'Liquidaciones Trabajadores');
        
        $documento->eliminarDocumento();

        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'encargado_id' => Input::get('idEncargado'),
            'trabajador_rut' => Input::get('rutTrabajador'),
            'trabajador_nombres' => Input::get('nombresTrabajador'),
            'trabajador_apellidos' => Input::get('apellidosTrabajador'),
            'trabajador_cargo' => Input::get('cargoTrabajador'),
            'trabajador_seccion' => Input::get('seccionTrabajador'),
            'uf' => Input::get('uf'),
            'utm' => Input::get('utm')
        );
        return $datos;
    }

}
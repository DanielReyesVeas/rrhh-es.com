<?php

class TipoDescuento extends Eloquent {
    
    protected $table = 'tipos_descuento';
    
    public function descuentos(){
        return $this->hasMany('Descuento','tipo_descuento_id');
    }
    
    public function afp(){
        return $this->belongsTo('Glosa', 'afp_id');
    }
    
    public function formaPago(){
        return $this->belongsTo('Glosa', 'forma_pago');
    }
    
    public function estructuraDescuento(){
        return $this->belongsTo('EstructuraDescuento','estructura_descuento_id');
    }
    
    public function miCuenta(){
        return $this->belongsTo('Cuenta', 'cuenta_id');
    }
    
    public function cuenta($cuentas = null, $centroCostoId=null)
    {        
        $empresa = Session::get('empresa');

        if($empresa->centro_costo){
            return $this->descuentoCuenta($cuentas, $centroCostoId);
        }else{
            if($this->cuenta_id){
                if(!$cuentas){
                    $cuentas = Cuenta::listaCuentas();
                }
                $idCuenta = $this->cuenta_id;
                if(array_key_exists($idCuenta, $cuentas)){
                    return $cuentas[$idCuenta];
                }
            }
        }
        
        return null;
	}
    
    public function descuentoCuenta($cuentasCodigo = null, $centroCostoId=null)
    {
        if($centroCostoId){
            $codigo=null;
            if(!$cuentasCodigo){
                $cuentas = Cuenta::listaCuentas();
            }
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'descuento')
                ->where('concepto_id', $this->id)
                ->where('centro_costo_id', $centroCostoId )
                ->first();

            if( $centroCostoCuenta ){
                if(array_key_exists($centroCostoCuenta->cuenta_id, $cuentasCodigo)){
                    return $cuentasCodigo[$centroCostoCuenta->cuenta_id];
                }
            }
        }else{
            $empresa = Session::get('empresa');
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'descuento')
                ->where('concepto_id', $this->id)
                ->get();
            if($centroCostoCuenta->count()){
                $asignables = $empresa->centrosAsignables();
                if($centroCostoCuenta->count()==$asignables){
                    return 2;
                }else{
                    return 1;							
                }
            }else{
                return 0;
            }
        }

        return null;
    }
    
    static function descuentosCuentaAhorro()
    {
        $idTipoDescuento = TipoDescuento::where('estructura_descuento_id', 7)->lists('id');
        $listaDescuentos = array();
        $idMes = \Session::get('mesActivo')->id;
        $mes = \Session::get('mesActivo')->mes;
        $misDescuentos = Descuento::whereIn('tipo_descuento_id', $idTipoDescuento)->get();
        
        if( $misDescuentos->count() ){
            foreach($misDescuentos as $descuento){
                $listaDescuentos[] = array(
                    'id' => $descuento->id,
                    'sid' => $descuento->sid,
                    'moneda' => $descuento->moneda,
                    'monto' => $descuento->monto,
                    'porMes' => $descuento->por_mes ? true : false,
                    'rangoMeses' => $descuento->rango_meses ? true : false,
                    'permanente' => $descuento->permanente ? true : false,
                    'mes' => $descuento->mes ? Funciones::obtenerMesAnioTextoAbr($descuento->mes) : '',
                    'desde' => $descuento->desde ? Funciones::obtenerMesAnioTextoAbr($descuento->desde) : '',
                    'hasta' => $descuento->hasta ? Funciones::obtenerMesAnioTextoAbr($descuento->hasta) : '',
                    'trabajador' => $descuento->trabajadorDescuento(),
                    'fechaIngreso' => date('Y-m-d H:i:s', strtotime($descuento->created_at))
                );
            }
        }
        return $listaDescuentos;
    }
    
    public function nombreAfp(){
        $afp = Glosa::find($this->nombre);
        
        return $afp->glosa;
	}
    
    public function nombreIsapre(){
        $isapre = Glosa::find($this->nombre);
        
        return $isapre->glosa;
	}
    
    static function isCuentas()
    {
        $tiposDescuento = TipoDescuento::all();
        $bool = true;
        foreach($tiposDescuento as $tipoDescuento){
            if(!$tipoDescuento->cuenta_id){
                $bool = false;
            }
        }
        
        return $bool;
    }
    
    static function listaTiposDescuento(){
    	$listaTiposDescuento = array();
    	$tiposDescuento = TipoDescuento::orderBy('id', 'ASC')->get();
    	if( $tiposDescuento->count() ){
            foreach( $tiposDescuento as $tipoDescuento ){
                if($tipoDescuento->id!=1 && $tipoDescuento->id!=3 && $tipoDescuento->estructura_descuento_id!=4 && $tipoDescuento->estructura_descuento_id!=5 && $tipoDescuento->estructura_descuento_id!=9){
                    if($tipoDescuento->estructuraDescuento->id==3){
                        $nombre = 'APVC AFP ' . $tipoDescuento->nombreAfp();
                    }else if($tipoDescuento->estructuraDescuento->id==7){
                        $nombre = 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp();
                    }else{
                        $nombre = $tipoDescuento->nombre;
                    } 
                    $listaTiposDescuento[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'nombre' => $nombre
                    );
                }
            }
    	}
    	return $listaTiposDescuento;
    }
    
    static function listaDescuentos()
    {
    	$listaTiposDescuento = array();
    	$tiposDescuento = TipoDescuento::orderBy('id', 'ASC')->get();
        $aportes = Aporte::whereIn('tipo_aporte', array(4, 5))->get();
        
    	if( $tiposDescuento->count() ){
            foreach( $tiposDescuento as $tipoDescuento ){
                if($tipoDescuento->id!=1 && $tipoDescuento->id!=3 && $tipoDescuento->estructura_descuento_id!=9){
                    if($tipoDescuento->estructuraDescuento->id==3){
                        $nombre = 'APVC AFP ' . $tipoDescuento->nombreAfp();
                    }else if($tipoDescuento->estructuraDescuento->id==7){
                        $nombre = 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp();
                    }else if($tipoDescuento->estructura_descuento_id==4){
                        $nombre = 'APV Régimen A AFP ' . $tipoDescuento->nombreAfp();
                    }else if($tipoDescuento->estructura_descuento_id==5){
                        $nombre = 'APV Régimen B AFP ' . $tipoDescuento->nombreAfp();
                    }else{
                        $nombre = $tipoDescuento->nombre;
                    } 
                    $filtrados[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'nombre' => $nombre
                    );
                    $todos[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'nombre' => $nombre
                    );
                }else{
                    if($tipoDescuento->estructura_descuento_id==9){
                        $nombre = $tipoDescuento->nombreIsapre();
                    }else{
                        $nombre = $tipoDescuento->nombre;
                    }
                    $todos[]=array(
                        'id' => $tipoDescuento->id,
                        'sid' => $tipoDescuento->sid,
                        'nombre' => $nombre
                    );
                }
            }
    	}
        if( $aportes->count() ){
            foreach( $aportes as $aporte ){
                if($aporte->tipo_aporte==4){
                    $todos[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'nombre' => 'AFP ' . $aporte->afp()
                    );
                }else if($aporte->tipo_aporte==5){
                    $todos[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'nombre' => 'AFC AFP ' . $aporte->afp()
                    );
                }
            }
            
        }
        
        $datos = array(
            'todos' => $todos,
            'filtrados' => $filtrados
        );
        
    	return $datos;
    }
    
    static function reporteDescuentos($ids, $trabajadores, $desde)
    {
        $mes = \Session::get('mesActivo');
        $detalle = array();
        
        if(count($ids)){
            if($desde=='Ingresos'){
                $conceptos = TipoHaber::whereIn('id', $ids)->get();
                
                $descuentos = Descuento::where('mes_id', $mes->id)->whereIn('tipo_descuento_id', $ids)->whereIn('trabajador_id', $trabajadores)
                        ->orWhere('permanente', 1)->whereIn('tipo_descuento_id', $ids)->whereIn('trabajador_id', $trabajadores)
                        ->orWhere('hasta', '>=', $mes->mes)->whereIn('tipo_descuento_id', $ids)->whereIn('trabajador_id', $trabajadores)->get();

                if($conceptos->count()){
                    foreach($descuentos as $descuento){
                        if($descuento->permanente && !$descuento->desde && !$descuento->hasta 
                        || $descuento->permanente && !$descuento->desde && $descuento->hasta && $descuento->hasta >= $mes->mes 
                        || $descuento->permanente && !$descuento->hasta && $descuento->desde && $descuento->desde <= $mes->mes 
                        || $descuento->permanente && $descuento->desde && $descuento->desde <= $mes->mes && $descuento->hasta && $descuento->hasta >= $mes->mes 
                        || !$descuento->permanente){
                            if(isset($detalle[$descuento->tipoDescuento->id])){
                                $detalle[$descuento->tipoDescuento->id]['total'] += Funciones::convertir($descuento->monto, $descuento->moneda);
                                if(isset($detalle[$descuento->tipoDescuento->id]['trabajadores'][$descuento->trabajador_id])){
                                    $detalle[$descuento->tipoDescuento->id]['trabajadores'][$descuento->trabajador_id] += Funciones::convertir($descuento->monto, $descuento->moneda);
                                }else{
                                    $detalle[$descuento->tipoDescuento->id]['trabajadores'][$descuento->trabajador_id] = Funciones::convertir($descuento->monto, $descuento->moneda);
                                }
                            }else{                    
                                if($descuento->tipoDescuento->estructuraDescuento->id==3){                        
                                    $nombre = 'APVC AFP ' . $descuento->tipoDescuento->nombreAfp();
                                }else if($descuento->tipoDescuento->estructuraDescuento->id==7){                        
                                    $nombre = 'Cuenta de Ahorro AFP ' . $descuento->tipoDescuento->nombreAfp();
                                }else{                    
                                    $nombre = $descuento->tipoDescuento->nombre;
                                }
                                $detalle[$descuento->tipoDescuento->id] = array(
                                    'id' => $descuento->tipoDescuento->id,
                                    'codigo' => $descuento->tipoDescuento->codigo,
                                    'nombre' => $nombre,
                                    'total' => Funciones::convertir($descuento->monto, $descuento->moneda),
                                    'trabajadores' => array()
                                );
                                $detalle[$descuento->tipoDescuento->id]['trabajadores'][$descuento->trabajador_id] = Funciones::convertir($descuento->monto, $descuento->moneda);
                            }
                        }
                    }
                }
                
            }else{
                $liquidaciones = Liquidacion::whereIn('trabajador_id', $trabajadores)->where('mes', $mes->mes)->get();
                $idsLiquidaciones = Funciones::array_column(json_decode(json_encode($liquidaciones), true), 'id');
                $descuentos = DetalleLiquidacion::whereIn('liquidacion_id', $idsLiquidaciones)->whereIn('tipo_id', array(2, 4))->whereIn('detalle_id', $ids)->get();
                $apvs = DetalleApvi::whereIn('liquidacion_id', $idsLiquidaciones)->get();
                $apvcs = DetalleApvc::whereIn('liquidacion_id', $idsLiquidaciones)->get();
                $salud = DetalleSalud::whereIn('liquidacion_id', $idsLiquidaciones)->get();
                $afps = DetalleAfp::whereIn('liquidacion_id', $idsLiquidaciones)->get();
                $sc = DetalleSeguroCesantia::whereIn('liquidacion_id', $idsLiquidaciones)->get();
                $cajas = DetalleCaja::whereIn('liquidacion_id', $idsLiquidaciones)->get();
                                
                if($descuentos->count()){                   
                   foreach($descuentos as $descuento){
                        if(isset($detalle[$descuento->detalle_id])){
                            $detalle[$descuento->detalle_id]['total'] += $descuento->valor;
                            if(isset($detalle[$descuento->detalle_id]['trabajadores'][$descuento->liquidacion->trabajador_id])){
                                $detalle[$descuento->detalle_id]['trabajadores'][$descuento->liquidacion->trabajador_id] += $descuento->valor;
                            }else{
                                $detalle[$descuento->detalle_id]['trabajadores'][$descuento->liquidacion->trabajador_id] = $descuento->valor;
                            }
                        }else{                    
                            $detalle[$descuento->detalle_id] = array(
                                'id' => $descuento->detalle_id,
                                'codigo' => $descuento->detalle_id,
                                'nombre' => $descuento->nombre,
                                'total' => $descuento->valor,
                                'trabajadores' => array()
                            );
                            $detalle[$descuento->detalle_id]['trabajadores'][$descuento->liquidacion->trabajador_id] = $descuento->valor;
                        }
                    }
                }
                if($apvs->count()){                   
                   foreach($apvs as $apv){
                        if(isset($detalle['apv-' . $apv->afp_id])){
                            $detalle['apv-' . $apv->afp_id]['total'] += $apv->monto;
                            if(isset($detalle['apv-' . $apv->afp_id]['trabajadores'][$apv->liquidacion->trabajador_id])){
                                $detalle['apv-' . $apv->afp_id]['trabajadores'][$apv->liquidacion->trabajador_id] += $apv->monto;
                            }else{
                                $detalle['apv-' . $apv->afp_id]['trabajadores'][$apv->liquidacion->trabajador_id] = $apv->monto;
                            }
                        }else{                    
                            $detalle['apv-' . $apv->afp_id] = array(
                                'id' => $apv->id,
                                'codigo' => $apv->id,
                                'nombre' => 'APV ' . $apv->nombreAfp(),
                                'total' => $apv->monto,
                                'trabajadores' => array()
                            );
                            $detalle['apv-' . $apv->afp_id]['trabajadores'][$apv->liquidacion->trabajador_id] = $apv->monto;
                        }
                    }
                }
                if($apvcs->count()){                   
                   foreach($apvcs as $apvc){
                        if(isset($detalle['apvc-' . $apvc->afp_id])){
                            $detalle['apvc-' . $apvc->afp_id]['total'] += $apvc->monto;
                            if(isset($detalle['apvc-' . $apvc->afp_id]['trabajadores'][$apvc->liquidacion->trabajador_id])){
                                $detalle['apvc-' . $apvc->afp_id]['trabajadores'][$apvc->liquidacion->trabajador_id] += $apvc->monto;
                            }else{
                                $detalle['apvc-' . $apvc->afp_id]['trabajadores'][$apvc->liquidacion->trabajador_id] = $apvc->monto;
                            }
                        }else{                    
                            $detalle['apvc-' . $apvc->afp_id] = array(
                                'id' => $apvc->id,
                                'codigo' => $apvc->id,
                                'nombre' => 'APVC ' . $apvc->nombreAfp(),
                                'total' => $apvc->monto,
                                'trabajadores' => array()
                            );
                            $detalle['apvc-' . $apvc->afp_id]['trabajadores'][$apvc->liquidacion->trabajador_id] = $apvc->monto;
                        }
                    }
                }
                if($salud->count()){                   
                    foreach($salud as $sal){
                        if(($sal->cotizacion_obligatoria + $sal->cotizacion_adicional)>0){
                            if(isset($detalle['salud-' . $sal->salud_id])){
                                $detalle['salud-' . $sal->salud_id]['total'] += $sal->monto;
                            if(isset($detalle['salud-' . $sal->salud_id]['trabajadores'][$sal->liquidacion->trabajador_id])){
                                $detalle['salud-' . $sal->salud_id]['trabajadores'][$sal->liquidacion->trabajador_id] += ($sal->cotizacion_obligatoria + $sal->cotizacion_adicional);
                            }else{
                                $detalle['salud-' . $sal->salud_id]['trabajadores'][$sal->liquidacion->trabajador_id] = ($sal->cotizacion_obligatoria + $sal->cotizacion_adicional);
                            }
                        }else{                    
                            $detalle['salud-' . $sal->salud_id] = array(
                                'id' => $sal->id,
                                'codigo' => $sal->id,
                                'nombre' => 'Salud ' . $sal->nombreSalud(),
                                'total' => ($sal->cotizacion_obligatoria + $sal->cotizacion_adicional),
                                'trabajadores' => array()
                            );
                            $detalle['salud-' . $sal->salud_id]['trabajadores'][$sal->liquidacion->trabajador_id] = ($sal->cotizacion_obligatoria + $sal->cotizacion_adicional);
                            }
                        }
                    }
                }
                if($sc->count()){                   
                    foreach($sc as $s){
                        if($s->aporte_trabajador>0){
                            if(isset($detalle['sc-' . $s->afp_id])){
                                $detalle['sc-' . $s->afp_id]['total'] += $s->aporte_trabajador;
                                if(isset($detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id])){
                                    $detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id] += $s->aporte_trabajador;
                                }else{
                                    $detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id] = $s->aporte_trabajador;
                                }
                            }else{         
                                $detalle['sc-' . $s->afp_id] = array(
                                    'id' => $s->id,
                                    'codigo' => $s->id,
                                    'nombre' => 'AFC AFP ' . $s->nombreAfp(),
                                    'total' => $s->aporte_trabajador,
                                    'trabajadores' => array()
                                );                            
                                $detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id] = $s->aporte_trabajador;
                            }  
                        }
                    }
                }
                if($afps->count()){                   
                    foreach($afps as $afp){ 
                        $monto = $afp->cotizacion;
                        if($afp->paga_sis=='empleado'){
                            $monto += $afp->sis;
                        }
                        if($monto>0){
                            if(isset($detalle['afp-' . $afp->afp_id])){
                                $detalle['afp-' . $afp->afp_id]['total'] += $monto;
                                if(isset($detalle['afp-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id])){
                                    $detalle['afp-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id] += $monto;
                                }else{
                                    $detalle['afp-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id] = $monto;
                                }
                            }else{         
                                $detalle['afp-' . $afp->afp_id] = array(
                                    'id' => $afp->id,
                                    'codigo' => $afp->id,
                                    'nombre' => 'AFP ' . $afp->nombreAfp(),
                                    'total' => $monto,
                                    'trabajadores' => array()
                                );                            
                                $detalle['afp-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id] = $monto;
                            }
                        }
                        if($afp->cuenta_ahorro_voluntario>0){
                            if(isset($detalle['ahorro-' . $afp->afp_id])){
                                $detalle['ahorro-' . $afp->afp_id]['total'] += $afp->cuenta_ahorro_voluntario;
                                if(isset($detalle['ahorro-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id])){
                                    $detalle['ahorro-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id] += $afp->cuenta_ahorro_voluntario;
                                }else{
                                    $detalle['ahorro-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id] = $afp->cuenta_ahorro_voluntario;
                                }
                            }else{         
                                $detalle['ahorro-' . $afp->afp_id] = array(
                                    'id' => $afp->id,
                                    'codigo' => $afp->id,
                                    'nombre' => 'Cuenta de Ahorro AFP ' . $afp->nombreAfp(),
                                    'total' => $afp->cuenta_ahorro_voluntario,
                                    'trabajadores' => array()
                                    );                            
                                $detalle['ahorro-' . $afp->afp_id]['trabajadores'][$afp->liquidacion->trabajador_id] = $afp->cuenta_ahorro_voluntario;
                            }
                        }
                    }
                }
                if($cajas->count()){                   
                    foreach($cajas as $caja){
                        if($caja->cotizacion>0){
                            if(isset($detalle['caja-' . $caja->caja_id])){
                                $detalle['caja-' . $caja->caja_id]['total'] += $caja->cotizacion;
                                if(isset($detalle['caja-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id])){
                                    $detalle['caja-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] += $caja->cotizacion;
                                }else{
                                    $detalle['caja-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->cotizacion;
                                }
                            }else{         
                                $detalle['caja-' . $caja->caja_id] = array(
                                    'id' => $caja->id,
                                    'codigo' => $caja->id,
                                    'nombre' => 'Cotización CCAF ' . $caja->nombreCaja(),
                                    'total' => $caja->cotizacion,
                                    'trabajadores' => array()
                                );                            
                                $detalle['caja-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->cotizacion;
                            }  
                        }
                        if($caja->creditos_personales>0){
                            if(isset($detalle['creditos-' . $caja->caja_id])){
                                $detalle['creditos-' . $caja->caja_id]['total'] += $caja->creditos_personales;
                                if(isset($detalle['creditos-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id])){
                                    $detalle['creditos-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] += $caja->creditos_personales;
                                }else{
                                    $detalle['creditos-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->creditos_personales;
                                }
                            }else{         
                                $detalle['creditos-' . $caja->caja_id] = array(
                                    'id' => $caja->id,
                                    'codigo' => $caja->id,
                                    'nombre' => 'Créditos Personales CCAF ' . $caja->nombreCaja(),
                                    'total' => $caja->creditos_personales,
                                    'trabajadores' => array()
                                );                            
                                $detalle['creditos-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->creditos_personales;
                            }  
                        }
                        if($caja->descuentos_leasing>0){
                            if(isset($detalle['leasing-' . $caja->caja_id])){
                                $detalle['leasing-' . $caja->caja_id]['total'] += $caja->descuentos_leasing;
                                if(isset($detalle['leasing-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id])){
                                    $detalle['leasing-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] += $caja->descuentos_leasing;
                                }else{
                                    $detalle['leasing-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuentos_leasing;
                                }
                            }else{         
                                $detalle['leasing-' . $caja->caja_id] = array(
                                    'id' => $caja->id,
                                    'codigo' => $caja->id,
                                    'nombre' => 'Descuento por Leasing CCAF ' . $caja->nombreCaja(),
                                    'total' => $caja->descuentos_leasing,
                                    'trabajadores' => array()
                                );                            
                                $detalle['leasing-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuentos_leasing;
                            }  
                        }
                        if($caja->descuento_dental>0){
                            if(isset($detalle['dental-' . $caja->caja_id])){
                                $detalle['dental-' . $caja->caja_id]['total'] += $caja->descuento_dental;
                                if(isset($detalle['dental-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id])){
                                    $detalle['dental-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] += $caja->descuento_dental;
                                }else{
                                    $detalle['dental-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuento_dental;
                                }
                            }else{         
                                $detalle['dental-' . $caja->caja_id] = array(
                                    'id' => $caja->id,
                                    'codigo' => $caja->id,
                                    'nombre' => 'Descuento Dental CCAF ' . $caja->nombreCaja(),
                                    'total' => $caja->descuento_dental,
                                    'trabajadores' => array()
                                );                            
                                $detalle['dental-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuento_dental;
                            }  
                        }
                        if($caja->descuentos_seguro>0){
                            if(isset($detalle['seguro-' . $caja->caja_id])){
                                $detalle['seguro-' . $caja->caja_id]['total'] += $caja->descuentos_seguro;
                                if(isset($detalle['seguro-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id])){
                                    $detalle['seguro-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] += $caja->descuentos_seguro;
                                }else{
                                    $detalle['seguro-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuentos_seguro;
                                }
                            }else{         
                                $detalle['seguro-' . $caja->caja_id] = array(
                                    'id' => $caja->id,
                                    'codigo' => $caja->id,
                                    'nombre' => 'Descuento Seguro de Vida CCAF ' . $caja->nombreCaja(),
                                    'total' => $caja->descuentos_seguro,
                                    'trabajadores' => array()
                                );                            
                                $detalle['seguro-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuentos_seguro;
                            }  
                        }
                        if($caja->descuentos_cargas>0){
                            if(isset($detalle['cargas-' . $caja->caja_id])){
                                $detalle['cargas-' . $caja->caja_id]['total'] += $caja->descuentos_cargas;
                                if(isset($detalle['cargas-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id])){
                                    $detalle['cargas-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] += $caja->descuentos_cargas;
                                }else{
                                    $detalle['cargas-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuentos_cargas;
                                }
                            }else{         
                                $detalle['cargas-' . $caja->caja_id] = array(
                                    'id' => $caja->id,
                                    'codigo' => $caja->id,
                                    'nombre' => 'Descuento Cargas Familiares CCAF ' . $caja->nombreCaja(),
                                    'total' => $caja->descuentos_cargas,
                                    'trabajadores' => array()
                                );                            
                                $detalle['cargas-' . $caja->caja_id]['trabajadores'][$caja->liquidacion->trabajador_id] = $caja->descuentos_cargas;
                            }  
                        }
                    }
                }                
            }            
        }
                
        return array_values($detalle);
    }
    
    public function misDescuentos()
    {        
        $idTipoDescuento = $this->id;
        $listaDescuentos = array();
        $idMes = \Session::get('mesActivo')->id;
        $mes = \Session::get('mesActivo')->mes;
        $activos = Trabajador::trabajadoresActivos();
        
        $misDescuentos = Descuento::where('tipo_descuento_id', $idTipoDescuento)->where('mes_id', $idMes)
            ->orWhere('permanente', 1)->where('tipo_descuento_id', $idTipoDescuento)
            ->orWhere('hasta', '>=', $mes)->where('tipo_descuento_id', $idTipoDescuento)->get();

        if( $misDescuentos->count() ){
            foreach($misDescuentos as $descuento){
                if($descuento->permanente && !$descuento->desde && !$descuento->hasta 
                    || $descuento->permanente && !$descuento->desde && $descuento->hasta && $descuento->hasta >= $mes 
                    || $descuento->permanente && !$descuento->hasta && $descuento->desde && $descuento->desde <= $mes 
                    || $descuento->permanente && $descuento->desde && $descuento->desde <= $mes && $descuento->hasta && $descuento->hasta >= $mes 
                    || !$descuento->permanente){
                    if(in_array($descuento->trabajador_id, $activos)){
                        $listaDescuentos[] = array(
                            'id' => $descuento->id,
                            'sid' => $descuento->sid,
                            'moneda' => $descuento->moneda,
                            'monto' => $descuento->monto,
                            'porMes' => $descuento->por_mes ? true : false,
                            'rangoMeses' => $descuento->rango_meses ? true : false,
                            'permanente' => $descuento->permanente ? true : false,
                            'mes' => $descuento->mes ? Funciones::obtenerMesAnioTextoAbr($descuento->mes) : '',
                            'desde' => $descuento->desde ? Funciones::obtenerMesAnioTextoAbr($descuento->desde) : '',
                            'hasta' => $descuento->hasta ? Funciones::obtenerMesAnioTextoAbr($descuento->hasta) : '',
                            'trabajador' => $descuento->trabajadorDescuento(),
                            'fechaIngreso' => date('Y-m-d H:i:s', strtotime($descuento->created_at))
                        );
                    }
                }
            }
        }
        
        return $listaDescuentos;
    }
    
    public function validar($datos)
    {
        $codigos = TipoDescuento::where('codigo', $datos['codigo'])->get();

        if($codigos->count()){
            foreach($codigos as $codigo){
                if($codigo['codigo']==$datos['codigo'] && $codigo['id']!=$this->id){
                    $errores = new stdClass();
                    $errores->codigo = array('El código ya se encuentra registrado');
                    return $errores;
                }
            }
        }
        return;
    }
    
    public function comprobarDependencias()
    {
        $descuentos = $this->descuentos;        
        
        if($descuentos->count()){
            $errores = new stdClass();
            $errores->error = array("El Tipo de Descuento <b>" . $this->nombre . "</b> se encuentra asignado.<br /> Debe <b>eliminar</b> estos descuentos primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
    
    static function errores($datos){
        if($datos['id']){
            $rules =    array(
                'codigo' => 'required|unique:tipos_descuento,codigo,'.$datos['id'],
                'nombre' => 'required|unique:tipos_descuento,nombre,'.$datos['id']
            );
        }else{
            $rules =    array(
                'codigo' => 'required|unique:tipos_descuento,codigo',
                'nombre' => 'required|unique:tipos_descuento,nombre'
            );
        }

        $message = array(
            'tipoDescuento.required' => 'Obligatorio!'
        );
        $message =  array(
            'nombre.required' => 'Obligatorio!',
            'codigo.required' => 'Obligatorio!',
            'nombre.unique' => 'El nombre ya se encuentra registrado!',
            'codigo.unique' => 'El código ya se encuentra registrado!'
        );
        $verifier = App::make('validation.presence');
        //$verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            // la validacion tubo errores
            return $validation->getMessageBag()->toArray();
        }else{
            // no hubo errores de validacion
            return false;
        }        
    }
}
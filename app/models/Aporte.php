<?php

class Aporte extends Eloquent {
    
    protected $table = 'aportes_cuentas';
    
    public function miCuenta(){
        return $this->belongsTo('Cuenta', 'cuenta_id');
    }
    
    function cuentaCentroCosto(){
    	return $this->hasOne('CuentaCentroCosto', 'concepto_id');
    }
    
    public function cuenta($cuentas=null, $centroCostoId=null)
    {
        $empresa = Session::get('empresa');
                
        if($empresa->centro_costo){
            return $this->aporteCuenta($cuentas, $centroCostoId);
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

            return null;
        }
    }
    
    public function aporteCuenta($cuentasCodigo = null, $centroCostoId=null)
    {
        if($centroCostoId){
            $codigo=null;
            if(!$cuentasCodigo){
                $cuentas = Cuenta::listaCuentas();
            }
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'aporte')
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
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'aporte')
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
    
    static function listaAportes()
    {
        $empresa = \Session::get('empresa');
        $aportes = Aporte::all();
        $listaAportes = array();
    
        if( $aportes->count() ){
            foreach( $aportes as $aporte ){
                if($aporte->tipo_aporte==1){
                    if($empresa->mutual_id==263){
                        if($aporte->id==1){
                            $listaAportes[]=array(
                                'id' => $aporte->id,
                                'sid' => $aporte->sid,
                                'nombre' => $aporte->nombre
                            );
                        }
                    }else{
                        if($aporte->id==2){
                            $listaAportes[]=array(
                                'id' => $aporte->id,
                                'sid' => $aporte->sid,
                                'nombre' => $aporte->nombre
                            );
                        }
                    }
                }else if($aporte->tipo_aporte==2){
                    $listaAportes[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'nombre' => 'SIS AFP ' . $aporte->afp()
                    );
                }else if($aporte->tipo_aporte==6){
                    $listaAportes[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'nombre' => 'Seguro Cesantía Empleador AFP ' . $aporte->afp()
                    );
                }
            }
        }
        
        return $listaAportes;
    }
    
    static function arraySeleccionables($ids)
    {
        $aportes = Aporte::whereIn('id', $ids)->get();  
        $lista = new stdClass();
        $lista->sc = new stdClass(); 
        $lista->afp = new stdClass();
        
        if($aportes->count()){
            foreach($aportes as $aporte){
                if($aporte->tipo_aporte==1){
                    $lista->mutual = $aporte->nombre;
                }else if($aporte->tipo_aporte==2){
                    $val = $aporte->nombre;
                    $lista->afp->$val = $aporte->nombre;
                }else if($aporte->tipo_aporte==6){
                    $val = $aporte->nombre;
                    $lista->sc->$val = $aporte->nombre;
                }
            }
        }
        
        return $lista;
    }
    
    static function reporteAportes($ids, $trabajadores)
    {
        $mes = \Session::get('mesActivo');
        $detalle = array();
        
        if(count($ids)){                           
            $liquidaciones = Liquidacion::whereIn('trabajador_id', $trabajadores)->where('mes', $mes->mes)->get();
            $idsLiquidaciones = Funciones::array_column(json_decode(json_encode($liquidaciones), true), 'id');
            $afps = DetalleAfp::whereIn('liquidacion_id', $idsLiquidaciones)->get();
            $sc = DetalleSeguroCesantia::whereIn('liquidacion_id', $idsLiquidaciones)->get();
            $mutuales = DetalleMutual::whereIn('liquidacion_id', $idsLiquidaciones)->get();
            $aportes = Aporte::arraySeleccionables($ids);
            
            if($sc->count()){                   
                foreach($sc as $s){
                    $val = $s->afp_id;
                    if($s->aporte_empleador && isset($aportes->sc->$val)){
                        if(isset($detalle['sc-' . $s->afp_id])){
                            $detalle['sc-' . $s->afp_id]['total'] += $s->aporte_empleador;
                            if(isset($detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id])){
                                $detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id] += $s->aporte_empleador;
                            }else{
                                $detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id] = $s->aporte_empleador;
                            }
                        }else{         
                            $detalle['sc-' . $s->afp_id] = array(
                                'id' => $s->id,
                                'codigo' => $s->id,
                                'nombre' => 'AFC AFP ' . $s->nombreAfp(),
                                'total' => $s->aporte_empleador,
                                'trabajadores' => array()
                            );                            
                            $detalle['sc-' . $s->afp_id]['trabajadores'][$s->liquidacion->trabajador_id] = $s->aporte_empleador;
                        }  
                    }
                }
            }
            if($afps->count()){                   
                foreach($afps as $afp){ 
                    $monto = 0;
                    $val = $afp->afp_id;
                    if($afp->paga_sis=='empresa'){
                        $monto += $afp->sis;
                    }
                    if($monto>0 && isset($aportes->afp->$val)){
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
                }
            }   
            if($mutuales->count()){                   
                foreach($mutuales as $mutual){ 
                    if($mutual->cotizacion_accidentes>0 && isset($aportes->mutual)){
                        if(isset($detalle['mutual'])){
                            $detalle['mutual']['total'] += $mutual->cotizacion_accidentes;
                            if(isset($detalle['mutual']['trabajadores'][$mutual->liquidacion->trabajador_id])){
                                $detalle['mutual']['trabajadores'][$mutual->liquidacion->trabajador_id] += $mutual->cotizacion_accidentes;
                            }else{
                                $detalle['mutual']['trabajadores'][$mutual->liquidacion->trabajador_id] = $mutual->cotizacion_accidentes;
                            }
                        }else{         
                            $detalle['mutual'] = array(
                                'id' => $mutual->id,
                                'codigo' => $mutual->id,
                                'nombre' => $mutual->nombreMutual(),
                                'total' => $mutual->cotizacion_accidentes,
                                'trabajadores' => array()
                            );                            
                            $detalle['mutual']['trabajadores'][$mutual->liquidacion->trabajador_id] = $mutual->cotizacion_accidentes;
                        }
                    }                    
                }
            }            
        }
                
        return array_values($detalle);
    }
   
    static function aportes()
    {
        $aportes = Aporte::all();
        
        return $aportes;
    }
    
    static function isCuentas()
    {
        $aportes = Aporte::all();
        $bool = true;
        foreach($aportes as $aporte){
            if(!$aporte->cuenta_id){
                $bool = false;
            }
        }
        
        return $bool;
    }
    
    public function afp(){
        $afp = Glosa::find($this->nombre);
        
        return $afp->glosa;
	}
    
    public function exCaja(){
        $exCaja = Glosa::find($this->nombre);
        
        return $exCaja->glosa;
	}
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'aporte.required' => 'Obligatorio!'
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            return $validation->messages();
        }else{
            return false;
        }
    }
}
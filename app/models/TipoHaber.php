<?php

class TipoHaber extends Eloquent {
    
    protected $table = 'tipos_haber';
    
    public function haberes(){
        return $this->hasMany('Haber','tipo_haber_id');
    }
    
    public function miCuenta(){
        return $this->belongsTo('Cuenta', 'cuenta_id');
    }
    
    public function cuenta($cuentas = null, $centroCostoId=null)
    {
        $empresa = Session::get('empresa');
        
        if($empresa->centro_costo){
            return $this->haberCuenta($cuentas, $centroCostoId);
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
    
    public function haberCuenta($cuentasCodigo = null, $centroCostoId=null)
    {
        if($centroCostoId){
            $codigo=null;
            if(!$cuentasCodigo){
                $cuentas = Cuenta::listaCuentas();
            }
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'haber')
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
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'haber')
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
    
    static function isCuentas()
    {
        $tiposHaber = TipoHaber::all();
        $bool = true;
        foreach($tiposHaber as $tipoHaber){
            if(!$tipoHaber->cuenta_id){
                $bool = false;
            }
        }
        
        return $bool;
    }
    
    static function listaTiposHaber(){
    	$listaTiposHaber = array();
    	$tiposHaber = TipoHaber::orderBy('nombre', 'ASC')->get();
    	if( $tiposHaber->count() ){
            foreach( $tiposHaber as $tipoHaber ){
                if($tipoHaber->id>15 || $tipoHaber->nombre=='Colación' || $tipoHaber->nombre=='Movilización' || $tipoHaber->nombre=='Viático'){
                    $listaTiposHaber[]=array(
                        'id' => $tipoHaber->id,
                        'imponible' => $tipoHaber->imponible ? true : false,
                        'nombre' => $tipoHaber->nombre
                    );
                }
            }
    	}
    	return $listaTiposHaber;
    }
    
    static function listaHaberes()
    {
        $tiposHaber = TipoHaber::all()->sortBy("codigo");
        $todos=array();
        $filtrados=array();
        
        if($tiposHaber->count()){
            foreach($tiposHaber as $tipoHaber){
                if($tipoHaber->id>15 || $tipoHaber->id==10 || $tipoHaber->id==11 || $tipoHaber->id==4 || $tipoHaber->id==3 || $tipoHaber->id==5){
                    $filtrados[]=array(
                        'id' => $tipoHaber->id,
                        'sid' => $tipoHaber->sid,
                        'codigo' => $tipoHaber->codigo,
                        'nombre' => $tipoHaber->nombre
                    );
                    $todos[]=array(
                        'id' => $tipoHaber->id,
                        'sid' => $tipoHaber->sid,
                        'codigo' => $tipoHaber->codigo,
                        'nombre' => $tipoHaber->nombre
                    );
                }else{
                    $todos[]=array(
                        'id' => $tipoHaber->id,
                        'sid' => $tipoHaber->sid,
                        'codigo' => $tipoHaber->codigo,
                        'nombre' => $tipoHaber->nombre
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
    
    static function reporteHaberes($ids, $trabajadores, $desde)
    {
        $mes = \Session::get('mesActivo');        
        $detalle = array();
        
        if(count($ids)){
            if($desde=='Ingresos'){
                $conceptos = TipoHaber::whereIn('id', $ids)->get();

                $haberes = Haber::where('mes_id', $mes->id)->whereIn('tipo_haber_id', $ids)->whereIn('trabajador_id', $trabajadores)
                        ->orWhere('permanente', 1)->whereIn('tipo_haber_id', $ids)->whereIn('trabajador_id', $trabajadores)
                        ->orWhere('hasta', '>=', $mes->mes)->whereIn('tipo_haber_id', $ids)->whereIn('trabajador_id', $trabajadores)->get();

                if($conceptos->count()){
                    foreach($haberes as $haber){
                        if($haber->permanente && !$haber->desde && !$haber->hasta 
                        || $haber->permanente && !$haber->desde && $haber->hasta && $haber->hasta >= $mes->mes 
                        || $haber->permanente && !$haber->hasta && $haber->desde && $haber->desde <= $mes->mes 
                        || $haber->permanente && $haber->desde && $haber->desde <= $mes->mes && $haber->hasta && $haber->hasta >= $mes->mes 
                        || !$haber->permanente){
                            if(isset($detalle[$haber->tipoHaber->id])){
                                $detalle[$haber->tipoHaber->id]['total'] += Funciones::convertir($haber->monto, $haber->moneda);
                                if(isset($detalle[$haber->tipoHaber->id]['trabajadores'][$haber->trabajador_id])){
                                    $detalle[$haber->tipoHaber->id]['trabajadores'][$haber->trabajador_id] += Funciones::convertir($haber->monto, $haber->moneda);
                                }else{
                                    $detalle[$haber->tipoHaber->id]['trabajadores'][$haber->trabajador_id] = Funciones::convertir($haber->monto, $haber->moneda);
                                }
                            }else{                    
                                $detalle[$haber->tipoHaber->id] = array(
                                    'id' => $haber->tipoHaber->id,
                                    'codigo' => $haber->tipoHaber->codigo,
                                    'nombre' => $haber->tipoHaber->nombre,
                                    'total' => Funciones::convertir($haber->monto, $haber->moneda),
                                    'trabajadores' => array()
                                );
                                $detalle[$haber->tipoHaber->id]['trabajadores'][$haber->trabajador_id] = Funciones::convertir($haber->monto, $haber->moneda);
                            }
                        }
                    }
                    array_values($detalle);
                }
            }else{
                $liquidaciones = Liquidacion::whereIn('trabajador_id', $trabajadores)->where('mes', $mes->mes)->get();
                $idsLiquidaciones = Funciones::array_column(json_decode(json_encode($liquidaciones), true), 'id');
                $haberes = DetalleLiquidacion::whereIn('liquidacion_id', $idsLiquidaciones)->whereIn('tipo_id', array(1, 5))->whereIn('detalle_id', $ids)->get();
                                
                if($haberes->count()){                   
                   foreach($haberes as $haber){
                        if(isset($detalle[$haber->detalle_id])){
                            $detalle[$haber->detalle_id]['total'] += $haber->valor;
                            if(isset($detalle[$haber->detalle_id]['trabajadores'][$haber->liquidacion->trabajador_id])){
                                $detalle[$haber->detalle_id]['trabajadores'][$haber->liquidacion->trabajador_id] += $haber->valor;
                            }else{
                                $detalle[$haber->detalle_id]['trabajadores'][$haber->liquidacion->trabajador_id] = $haber->valor;
                            }
                        }else{                    
                            $detalle[$haber->detalle_id] = array(
                                'id' => $haber->detalle_id,
                                'codigo' => $haber->detalle_id,
                                'nombre' => $haber->nombre,
                                'total' => $haber->valor,
                                'trabajadores' => array()
                            );
                            $detalle[$haber->detalle_id]['trabajadores'][$haber->liquidacion->trabajador_id] = $haber->valor;
                        }
                    }
                }
                
                $trabajadores = array();
                $trabajadoresGratificacion = array();
                $trabajadoresCargas = array();
                $totalSueldo = 0;
                $totalAsignacion = 0;
                $totalGratificacion = 0;
                array_values($detalle);
                
                foreach($liquidaciones as $liquidacion){
                    $trabajadores[$liquidacion->trabajador_id] = $liquidacion->sueldo;
                    $totalSueldo += $liquidacion->sueldo;
                    $trabajadoresCargas[$liquidacion->trabajador_id] = $liquidacion->total_cargas;
                    $totalAsignacion += $liquidacion->total_cargas;
                    $trabajadoresGratificacion[$liquidacion->trabajador_id] = $liquidacion->gratificacion;
                    $totalGratificacion += $liquidacion->gratificacion;
                }        
                if($totalAsignacion>0){
                    $detalle[] = array(
                        'id' => 101010101,
                        'codigo' => 101010101,
                        'nombre' => 'Asignación Familiar',
                        'total' => $totalAsignacion,
                        'trabajadores' => $trabajadoresCargas
                    );  
                }
                if($totalGratificacion>0){
                    array_unshift($detalle, array(
                        'id' => 10101,
                        'codigo' => 10101,
                        'nombre' => 'Gratificación',
                        'total' => $totalGratificacion,
                        'trabajadores' => $trabajadoresGratificacion
                    ));
                }
                array_unshift($detalle, array(
                    'id' => 1010101,
                    'codigo' => 1010101,
                    'nombre' => 'Sueldo',
                    'total' => $totalSueldo,
                    'trabajadores' => $trabajadores
                ));
            }
        }
                
        return $detalle;
    }
    
    public function misHaberes()
    {        
        $idTipoHaber = $this->id;
        $listaHaberes = array();
        $idMes = \Session::get('mesActivo')->id;
        $mes = \Session::get('mesActivo')->mes;
        $activos = Trabajador::trabajadoresActivos();
        
        $misHaberes = Haber::where('tipo_haber_id', $idTipoHaber)->where('mes_id', $idMes)
                ->orWhere('permanente', 1)->where('tipo_haber_id', $idTipoHaber)
                ->orWhere('hasta', '>=', $mes)->where('tipo_haber_id', $idTipoHaber)->get();
        
        if( $misHaberes->count() ){
            foreach($misHaberes as $haber){
                if($haber->permanente && !$haber->desde && !$haber->hasta 
                || $haber->permanente && !$haber->desde && $haber->hasta && $haber->hasta >= $mes 
                || $haber->permanente && !$haber->hasta && $haber->desde && $haber->desde <= $mes 
                || $haber->permanente && $haber->desde && $haber->desde <= $mes && $haber->hasta && $haber->hasta >= $mes 
                || !$haber->permanente){
                    if(in_array($haber->trabajador_id, $activos)){
                        $listaHaberes[] = array(
                            'id' => $haber->id,
                            'sid' => $haber->sid,
                            'moneda' => $haber->moneda,
                            'permanente' => $haber->permanente ? true : false,
                            'porMes' => $haber->por_mes ? true : false,
                            'rangoMeses' => $haber->rango_meses ? true : false,
                            'monto' => $haber->monto,
                            'trabajador' => $haber->trabajadorHaber(),
                            'mes' => $haber->mes ? Funciones::obtenerMesAnioTextoAbr($haber->mes) : '',
                            'desde' => $haber->desde ? Funciones::obtenerMesAnioTextoAbr($haber->desde) : '',
                            'hasta' => $haber->hasta ? Funciones::obtenerMesAnioTextoAbr($haber->hasta) : '',
                            'fechaIngreso' => date('Y-m-d H:i:s', strtotime($haber->created_at))
                        );
                    }
                }
            }
        }
        
        return $listaHaberes;
    }
    
    public function misHaberesFicha($tipo)
    {        
        $listaHaberes = array();
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        $trabajadores = Trabajador::all();
        if($tipo=='Movilización'){
            $monto = 'monto_movilizacion';
            $proporcional = 'proporcional_movilizacion';
            $moneda = 'moneda_movilizacion';
        }else if($tipo=='Colación'){
            $monto = 'monto_colacion';
            $proporcional = 'proporcional_colacion';
            $moneda = 'moneda_colacion';
        }else if($tipo=='Viático'){
            $monto = 'monto_viatico';
            $proporcional = 'proporcional_viatico';
            $moneda = 'moneda_viatico';
        }
        
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes && $empleado->$monto>0 || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito < $finMes && $empleado->fecha_finiquito >= $mesAnterior && $empleado->$monto>0){
                        $listaHaberes[] = array(
                            'id' => $this->id,
                            'sid' => $tipo,
                            'moneda' => $empleado->$moneda,
                            'permanente' => true,
                            'porMes' => false,
                            'rangoMeses' => false,
                            'monto' => $empleado->$monto,
                            'trabajador' => array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'rutFormato' => Funciones::formatear_rut($trabajador->rut)
                            ),
                            'mes' => '',
                            'desde' => '',
                            'hasta' => '',
                            'fechaIngreso' => date('Y-m-d H:i:s', strtotime($empleado->created_at))
                        );
                    }                    
                }                
            }            
        }
        
        return $listaHaberes;
    }
    
    public function validar($datos)
    {
        $codigos = TipoHaber::where('codigo', $datos['codigo'])->get();

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
        $haberes = $this->haberes;        
        
        if($haberes->count()){
            $errores = new stdClass();
            $errores->error = array("El Tipo de Haber <b>" . $this->nombre . "</b> se encuentra asignado.<br /> Debe <b>eliminar</b> estos haberes primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
    
    static function errores($datos){        
        if($datos['id']){
            $rules =    array(
                'codigo' => 'required|unique:tipos_haber,codigo,'.$datos['id'],
                'nombre' => 'required|unique:tipos_haber,nombre,'.$datos['id']
            );
        }else{
            $rules =    array(
                'codigo' => 'required|unique:tipos_haber,codigo',
                'nombre' => 'required|unique:tipos_haber,nombre'
            );
        }

        $message = array(
            'tipoHaber.required' => 'Obligatorio!'
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
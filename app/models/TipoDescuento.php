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
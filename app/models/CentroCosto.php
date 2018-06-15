<?php

class CentroCosto extends Eloquent {
    
    protected $table = 'centros_costo';
    
    function obtenerDependencia(){
        return $this->belongsTo('CentroCosto', 'dependencia_id');
    }

    function obtenerHijos(){
    	return $this->hasMany('CentroCosto', 'dependencia_id');
    }
    
    function cuentaCentroCosto(){
    	return $this->hasMany('CuentaCentroCosto', 'centro_costo_id');
    }
    
    public function fichas(){
        return $this->hasMany('FichaTrabajador', 'centro_costo_id');
    }
    
    public function miCuentaCentroCosto($id, $concepto)
    {
        return CuentaCentroCosto::where('centro_costo_id', $this->id)->where('concepto', $concepto)->where('concepto_id', $id)->first();
    }
    
    static function listaCentrosCostoDependencia(&$lista, $padreId, $nivel, $idCentroCosto)
    {
        //obtener hijos de padreId
        //Sin la misma sección como dependencia
        
        $centrosCosto = CentroCosto::where('dependencia_id', $padreId)->orderBy('nombre', 'ASC')->get();
        if( $centrosCosto->count() ){
            foreach( $centrosCosto as $centroCosto ){
                if($centroCosto->id != $idCentroCosto){
                    $lista[]=array(
                        'id' => $centroCosto->id,
                        'sid' => $centroCosto->sid,
                        'codigo' => $centroCosto->codigo,
                        'nombre' => $centroCosto->nombre,
                        'nivel' => $nivel
                    );                    
                }
                if( $centroCosto->obtenerHijos->count() ){
                    CentroCosto::listaCentrosCostoDependencia( $lista, $centroCosto->id, $nivel+1, $idCentroCosto );
                }
            }
        }
    }
    
    public function comprobarDependencias()
    {
        $fichas = $this->fichas;        
        
        if($fichas->count()){
            $errores = new stdClass();
            $errores->error = array("El Centro de Costo <b>" . $this->nombre . "</b> se encuentra asignado.<br /> Debe <b>reasignar</b> los trabajadores primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
    
    static function arbolCentrosCosto(&$lista, $padreId, $nivel)
    {
        //obtener hijos de padreId
        $centrosCosto = CentroCosto::where('dependencia_id', $padreId)->orderBy('nombre', 'ASC')->get();
        if( $centrosCosto->count() ){
            foreach( $centrosCosto as $centroCosto ){
                $lista[]=array(
                    'id' => $centroCosto->id,
                    'sid' => $centroCosto->sid,
                    'nombre' => $centroCosto->nombre,
                    'nivel' => $nivel,
                    'codigo' => $centroCosto->codigo,
                    'dependencia' => $centroCosto->dependencia_id,
                    'isPadre' => $centroCosto->isPadre()
                );
                if( $centroCosto->obtenerHijos->count() ){
                    CentroCosto::arbolCentrosCosto( $lista, $centroCosto->id, $nivel+1 );
                }
            }
        }
    }
    
    public function nivel()
    {
        $centro = $this;
        $nivel = 1;
        while($centro->obtenerDependencia){
            $nivel++;
            $centro = $centro->obtenerDependencia;
        }        
        return $nivel;
    }
    
    public function dependencia()
    {
        $dependencia = $this->obtenerDependencia;
        $datosDependencia = array();
        if($dependencia){
            $datosDependencia = array(
                'id' => $dependencia->id,
                'sid' => $dependencia->sid,
                'codigo' => $dependencia->codigo,
                'nivel' => $dependencia->nivel(),
                'nombre' => $dependencia->nombre
            );
        }
        
        return $datosDependencia;
    }
    
    public function isPadre()
    {
        $dependencias = CentroCosto::where('dependencia_id', $this->id)->get();
        if($dependencias->count()){
            return true;
        }
        
        return false;
    }
    
    static function listaCentrosCosto()
    {
    	$listaCentrosCosto = array();
    	$centrosCosto = CentroCosto::orderBy('nombre', 'ASC')->get();
    	if( $centrosCosto->count() ){
            foreach( $centrosCosto as $centroCosto ){
                $listaCentrosCosto[]=array(
                    'id' => $centroCosto->id,
                    'sid' => $centroCosto->sid,
                    'codigo' => $centroCosto->codigo,
                    'nombre' => $centroCosto->nombre,
                    'nivel' => $centroCosto->nivel(),
                    'dependenciaId' => $centroCosto->dependencia_id,
                    'columna' => $centroCosto->columna()                
                );
            }
    	}
    	return $listaCentrosCosto;
    }
    
    public function columna()
    {
        $nivel = $this->nivel();    
        $columna = DB::table('variables_sistema')->where('variable', 'centro_costo')->where('valor1', $nivel)->first();
        if($columna){
            return $columna->valor2;
        }
        
        return null;
    }
    
    public function cuenta($id, $tipo)
    {
        if($id && $tipo){
            $concepto = $this->miCuentaCentroCosto($id, $tipo);
            if($concepto){
                return $concepto;
            }
        }
        
        return;
    }    
    
    public function arbol()
    {
        $dependencia = $this->obtenerDependencia;
        $dependencias = "";
        while($dependencia){
            $dependencias = $dependencia->nombre . " / " . $dependencias;
            $dependencia = $dependencia->obtenerDependencia;
        }
        
        return $dependencias . $this->nombre;
    }
    
    static function listaCentrosCostoCuentas($id=null, $tipo=null, $arbol=false, $cuentas=null)
    {
        $empresa = \Session::get('empresa');
        $listaCentrosCosto = array();
        
        if($empresa->centro_costo){
            $centrosCosto = CentroCosto::orderBy('nombre', 'ASC')->get();
            $nivel = $empresa->niveles_centro_costo;
            
            if( $centrosCosto->count() ){
                foreach( $centrosCosto as $centroCosto ){
                    if($centroCosto->nivel()==$nivel){
                        $cuenta = null;
                        if($arbol){
                            $nombre = $centroCosto->arbol();
                        }else{
                            $nombre = $centroCosto->nombre;
                        }
                        if(!$cuentas){
                            $cuentas = Cuenta::listaCuentas();
                        }
                        $idCuenta = $centroCosto->cuenta($id, $tipo)['cuenta_id'];
                        if(array_key_exists($idCuenta, $cuentas)){
                            $cuenta = $cuentas[$idCuenta];
                        }
                        $listaCentrosCosto[]=array(
                            'id' => $centroCosto->id,
                            'sid' => $centroCosto->sid,
                            'codigo' => $centroCosto->codigo,
                            'nombre' => $nombre,
                            'nivel' => $centroCosto->nivel(),
                            'dependenciaId' => $centroCosto->dependencia_id,
                            'cuenta' => $cuenta
                        );
                    }
                }
            }
        }
        
    	return $listaCentrosCosto;
    }
    
    static function codigosCentrosCosto()
    {
    	$codigosCentrosCosto = array();
    	$centrosCosto = CentroCosto::orderBy('nombre', 'ASC')->get();
    	if( $centrosCosto->count() ){
            foreach( $centrosCosto as $centroCosto ){
                $codigosTitulos[]=array(
                    'codigo' => $centroCosto->id,
                    'glosa' => $centroCosto->nombre
                );
            }
    	}
    	return $codigosCentrosCosto;
    }
        
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'centroCosto.required' => 'Obligatorio!'
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
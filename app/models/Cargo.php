<?php

class Cargo extends Eloquent {
    
    protected $table = 'cargos';
    
    public function fichas(){
        return $this->hasMany('FichaTrabajador', 'cargo_id');
    }
    
    static function listaCargos(){
    	$listaCargos = array();
    	$cargos = Cargo::orderBy('nombre', 'ASC')->get();
    	if( $cargos->count() ){
            foreach( $cargos as $cargo ){
                $listaCargos[]=array(
                    'id' => $cargo->id,
                    'nombre' => $cargo->nombre
                );
            }
    	}
    	return $listaCargos;
    }
    
    static function codigosCargos(){
    	$codigosCargos = array();
    	$cargos = Cargo::orderBy('nombre', 'ASC')->get();
    	if( $cargos->count() ){
            foreach( $cargos as $cargo ){
                $codigosCargos[]=array(
                    'codigo' => $cargo->id,
                    'glosa' => $cargo->nombre
                );
            }
    	}
    	return $codigosCargos;
    }
    
    public function comprobarDependencias()
    {
        $fichas = $this->fichas;        
        
        if($fichas->count()){
            $errores = new stdClass();
            $errores->error = array("El Cargo <b>" . $this->nombre . "</b> se encuentra asignado.<br /> Debe <b>reasignar</b> los trabajadores primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'cargo.required' => 'Obligatorio!'
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
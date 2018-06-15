<?php

class Titulo extends Eloquent {
    
    protected $table = 'titulos';
    
    public function fichas(){
        return $this->hasMany('FichaTrabajador', 'titulo_id');
    }
    
    static function listaTitulos(){
    	$listaTitulos = array();
    	$titulos = Titulo::orderBy('nombre', 'ASC')->get();
    	if( $titulos->count() ){
            foreach( $titulos as $titulo ){
                $listaTitulos[]=array(
                    'id' => $titulo->id,
                    'nombre' => $titulo->nombre
                );
            }
    	}
    	return $listaTitulos;
    }
    
    static function codigosTitulos(){
    	$codigosTitulos = array();
    	$titulos = Titulo::orderBy('nombre', 'ASC')->get();
    	if( $titulos->count() ){
            foreach( $titulos as $titulo ){
                $codigosTitulos[]=array(
                    'codigo' => $titulo->id,
                    'glosa' => $titulo->nombre
                );
            }
    	}
    	return $codigosTitulos;
    }
        
    public function comprobarDependencias()
    {
        $fichas = $this->fichas;        
        
        if($fichas->count()){
            $errores = new stdClass();
            $errores->error = array("El Título <b>" . $this->nombre . "</b> se encuentra asignado.<br /> Debe <b>reasignar</b> los trabajadores primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'titulo.required' => 'Obligatorio!'
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
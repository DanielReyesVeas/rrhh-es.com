<?php

class EstadoCivil extends Eloquent {
    
    protected $table = 'estados_civiles';
    protected $connection = "principal";
    
    static function listaEstadosCiviles(){
    	$listaEstadosCiviles = array();
    	$estadosCiviles = EstadoCivil::orderBy('nombre', 'ASC')->get();
    	if( $estadosCiviles->count() ){
            foreach( $estadosCiviles as $estadoCivil ){
                $listaEstadosCiviles[]=array(
                    'id' => $estadoCivil->id,
                    'nombre' => $estadoCivil->nombre
                );
            }
    	}
    	return $listaEstadosCiviles;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'estadoCivil.required' => 'Obligatorio!'
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
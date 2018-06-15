<?php

class Indicador extends Eloquent {
    
    protected $table = 'indicadores';
    protected $connection = "principal";
    
    static function listaIndicadores(){
    	$listaIndicadores = array();
    	$indicadores = Indicador::orderBy('nombre', 'ASC')->get();
    	if( $indicadores->count() ){
            foreach( $indicadores as $indicador ){
                $listaIndicadores[]=array(
                    'id' => $indicador->id,
                    'nombre' => $indicador->nombre
                );
            }
    	}
    	return $listaIndicadores;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'indicador.required' => 'Obligatorio!'
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
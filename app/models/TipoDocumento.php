<?php

class TipoDocumento extends Eloquent {
    
    protected $table = 'tipos_documento';
    
    static function listaTiposDocumento(){
    	$listaTiposDocumento = array();
    	$tiposDocumento = TipoDocumento::orderBy('nombre', 'ASC')->get();
    	if( $tiposDocumento->count() ){
            foreach( $tiposDocumento as $tipoDocumento ){
                $listaTiposDocumento[]=array(
                    'id' => $tipoDocumento->id,
                    'sid' => $tipoDocumento->sid,
                    'nombre' => $tipoDocumento->nombre
                );
            }
    	}
    	return $listaTiposDocumento;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'jornada.required' => 'Obligatorio!'
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
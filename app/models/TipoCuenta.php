<?php

class TipoCuenta extends Eloquent {
    
    protected $table = 'cuentas';
    protected $connection = "principal";
    
    static function listaTiposCuenta(){
    	$listaTiposCuenta = array();
    	$tiposCuenta = TipoCuenta::orderBy('nombre', 'ASC')->get();
    	if( $tiposCuenta->count() ){
            foreach( $tiposCuenta as $tipoCuenta ){
                $listaTiposCuenta[]=array(
                    'id' => $tipoCuenta->id,
                    'nombre' => $tipoCuenta->nombre
                );
            }
    	}
    	return $listaTiposCuenta;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'tipoCuenta.required' => 'Obligatorio!'
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
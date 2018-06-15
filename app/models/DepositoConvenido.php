<?php

class DepositoConvenido extends Eloquent {
    
    protected $table = 'deposito_convenido';
    protected $connection = "principal";
    
    static function listaDepositoConvenido($mes=null){
        
        if(!$mes){
            $mes = \Session::get('mesActivo')->mes;
        }
        
    	$listaDepositoConvenido = array();
    	$depositosConvenidos = DepositoConvenido::where('mes', $mes)->orderBy('id', 'ASC')->get();
    	if( $depositosConvenidos->count() ){
            foreach( $depositosConvenidos as $depositoConvenido ){
                $listaDepositoConvenido[]=array(
                    'id' => $depositoConvenido->id,
                    'nombre' => $depositoConvenido->nombre,
                    'valor' => $depositoConvenido->valor
                );
            }
    	}
    	return $listaDepositoConvenido;
    }
    
    static function errores($datos){
         
        $rules = array(
            'valor' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'depositoConvenido.required' => 'Obligatorio!'
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
<?php

class Banco extends Eloquent {
    
    protected $table = 'bancos';
    protected $connection = "principal";
    
    static function listaBancos(){
    	$listaBancos = array();
    	$bancos = Banco::orderBy('codigo', 'ASC')->get();
    	if( $bancos->count() ){
            foreach( $bancos as $banco ){
                $listaBancos[]=array(
                    'id' => $banco->id,
                    'codigo' => $banco->codigo,
                    'nombre' => $banco->nombre
                );
            }
    	}
    	return $listaBancos;
    }
    
    static function codigosBancos(){
    	$listaBancos = array();
    	$bancos = Banco::orderBy('nombre', 'ASC')->get();
    	if( $bancos->count() ){
            foreach( $bancos as $banco ){
                $listaBancos[]=array(
                    'codigo' => $banco->id,
                    'glosa' => $banco->nombre
                );
            }
    	}
    	return $listaBancos;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required',
            'codigo' => 'required'
        );

        $message = array(
            'banco.required' => 'Obligatorio!'
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
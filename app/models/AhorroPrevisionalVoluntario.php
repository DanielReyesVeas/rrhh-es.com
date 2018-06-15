<?php

class AhorroPrevisionalVoluntario extends Eloquent {
    
    protected $table = 'ahorro_previsional_voluntario';
    protected $connection = "principal";

    
    static function listaAhorroPrevisionalVoluntario($mes=null){
        
        if(!$mes){
            $mes = \Session::get('mesActivo')->mes;
        }
        
    	$listaAhorroPrevisionalVoluntario = array();
    	$ahorrosPrevisionalesVoluntarios = AhorroPrevisionalVoluntario::where('mes', $mes)->orderBy('id', 'ASC')->get();
    	if( $ahorrosPrevisionalesVoluntarios->count() ){
            foreach( $ahorrosPrevisionalesVoluntarios as $ahorroPrevisionalVoluntario ){
                $listaAhorroPrevisionalVoluntario[]=array(
                    'id' => $ahorroPrevisionalVoluntario->id,
                    'nombre' => $ahorroPrevisionalVoluntario->nombre,
                    'valor' => $ahorroPrevisionalVoluntario->valor
                );
            }
    	}
    	return $listaAhorroPrevisionalVoluntario;
    }
    
    static function errores($datos){
         
        $rules = array(
            'valor' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'ahorroPrevisionalVoluntario.required' => 'Obligatorio!'
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
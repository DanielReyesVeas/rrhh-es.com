<?php

class CausalFiniquito extends Eloquent {
    
    protected $table = 'causales_finiquito';
    
    public function finiquito(){
        return $this->hasMany('Finiquito','causal_finiquito_id');
    }
    
    static function listaCausalesFiniquito(){
    	$listaCausalesFiniquito = array();
    	$causalesFiniquito = CausalFiniquito::orderBy('nombre', 'ASC')->get();
    	if( $causalesFiniquito->count() ){
            foreach( $causalesFiniquito as $causalFiniquito ){
                $listaCausalesFiniquito[]=array(
                    'id' => $causalFiniquito->id,
                    'sid' => $causalFiniquito->sid,
                    'codigo' => $causalFiniquito->codigo,
                    'articulo' => $causalFiniquito->articulo,
                    'nombre' => $causalFiniquito->nombre
                );
            }
    	}
    	return $listaCausalesFiniquito;
    }
    
    static function errores($datos){
         
        $rules = array(
            'codigo' => 'required',
            'articulo' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'causalFiniquito.required' => 'Obligatorio!'
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
<?php

class TipoCarga extends Eloquent {
    
    protected $table = 'tipos_carga';
    
    public function cargas(){
        return $this->hasMany('Carga','tipo_carga_id');
    }
    
    static function listaTiposCarga(){
    	$listaTiposCarga = array();
    	$tiposCarga = TipoCarga::orderBy('id', 'ASC')->get();
    	if( $tiposCarga->count() ){
            foreach( $tiposCarga as $tipoCarga ){
                $listaTiposCarga[]=array(
                    'id' => $tipoCarga->id,
                    'nombre' => $tipoCarga->nombre
                );
            }
    	}
    	return $listaTiposCarga;
    }
    
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'tipoCarga.required' => 'Obligatorio!'
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
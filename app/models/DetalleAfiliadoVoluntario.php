<?php

class DetalleAfiliadoVoluntario extends Eloquent {
    
    protected $table = 'detalles_afiliado_voluntario';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'detalleAfiliadoVoluntario.required' => 'Obligatorio!'
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

<?php

class DetallePagadorSubsidio extends Eloquent {
    
    protected $table = 'detalles_pagador_subsidio';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    static function errores($datos){
         
        $rules = array(
        );

        $message = array(
            'detallePagadorSubsidio.required' => 'Obligatorio!'
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

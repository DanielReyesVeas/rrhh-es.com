<?php

class TomaVacaciones extends Eloquent {
    
    protected $table = 'toma_vacaciones';    
        
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    static function errores($datos){
         
        $rules = array(
        );

        $message = array(
            'tomaVacaciones.required' => 'Obligatorio!'
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
<?php

class CajaExRegimen extends Eloquent {
    
    protected $table = 'cajas_ex_regimen';
    protected $connection = "principal";
        
    public function tasas(){
        return $this->hasMany('TasasCajasExRegimen','caja_id');
    }

    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'cajaExRegimen.required' => 'Obligatorio!'
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
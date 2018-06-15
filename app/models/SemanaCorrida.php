<?php

class SemanaCorrida extends Eloquent {
    
    protected $table = 'semana_corrida';

    
    static function dias($inasistencias, $semanaCorrida)
    {
        return ;
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'semanaCorrida.required' => 'Obligatorio!'
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
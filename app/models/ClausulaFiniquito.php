<?php

class ClausulaFiniquito extends Eloquent {
    
    protected $table = 'clausulas_finiquito';
    
    public function plantillaFiniquito(){
        return $this->belongsTo('PlantillaFiniquito','plantilla_finiquito_id');
    }    
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required',
            'clausula' => 'required'
        );

        $message = array(
            'clausulaFiniquito.required' => 'Obligatorio!'
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
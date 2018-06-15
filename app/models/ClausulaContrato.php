<?php

class ClausulaContrato extends Eloquent {
    
    protected $table = 'clausulas_contrato';
    
    public function plantillaContrato(){
        return $this->belongsTo('PlantillaContrato','plantilla_contrato_id');
    }    
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required',
            'clausula' => 'required'
        );

        $message = array(
            'clausulaContrato.required' => 'Obligatorio!'
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
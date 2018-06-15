<?php

class PlantillaContrato extends Eloquent {
    
    protected $table = 'plantillas_contratos';
    
    public function clausulaContrato(){
        return $this->hasMany('ClausulaContrato','plantilla_contrato_id');
    }    
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required',
            'cuerpo' => 'required'
        );

        $message = array(
            'plantillaContrato.required' => 'Obligatorio!'
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
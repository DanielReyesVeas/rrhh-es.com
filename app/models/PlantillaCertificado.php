<?php

class PlantillaCertificado extends Eloquent {
    
    protected $table = 'plantillas_certificados';
        
    public function certificado(){
        return $this->hasMany('Certificado','plantilla_certificado_id');
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required',
            'cuerpo' => 'required'
        );

        $message = array(
            'plantillaCertificado.required' => 'Obligatorio!'
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
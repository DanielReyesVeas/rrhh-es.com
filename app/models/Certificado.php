<?php

class Certificado extends Eloquent {
    
    protected $table = 'certificados';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function plantillaCertificado(){
        return $this->belongsTo('PlantillaCertificado','plantilla_certificado_id');
    }
    
    public function documento(){
        return $this->belongsTo('Documento', 'documento_id');
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'certificado.required' => 'Obligatorio!'
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
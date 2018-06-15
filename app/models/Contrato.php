<?php

class Contrato extends Eloquent {
    
    protected $table = 'contratos';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function documento(){
        return $this->hasOne('Documento', 'documento_id');
    }

    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'contrato.required' => 'Obligatorio!'
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
<?php

class Finiquito extends Eloquent {
    
    protected $table = 'finiquitos';
        
    public function causalFiniquito(){
        return $this->belongsTo('CausalFiniquito','causal_finiquito_id');
    }
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function documento(){
        return $this->belongsTo('Documento','documento_id');
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'finiquito.required' => 'Obligatorio!'
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
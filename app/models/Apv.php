<?php

class Apv extends Eloquent {
    
    protected $table = 'apvs';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function afp(){
        return $this->belongsTo('Glosa', 'afp_id');
    }
    
    public function formaPago(){
        return $this->belongsTo('Glosa', 'forma_pago');
    }
    
    static function errores($datos){
         
        $rules = array(
            /*'trabajador_id' => 'required',
            'afp_id' => 'required',
            'moneda' => 'required',
            'monto' => 'required'*/
        );

        $message = array(
            'apv.required' => 'Obligatorio!'
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
<?php

class TasaCajasExRegimen extends Eloquent {
    
    protected $table = 'tasas_cajas_ex_regimen';
    protected $connection = "principal";
    
    public function caja(){
		return $this->belongsTo('CajaExRegimen', 'caja_id');
	}

    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'tasaCajasExRegimen.required' => 'Obligatorio!'
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
<?php

class Codigo extends Eloquent {
    
    protected $table = 'tipos_estructura_glosa_recaudador';
    protected $connection = "principal";
    
    public function recaudador(){
        return $this->belongsTo('Recaudador','recaudador_id');
    }
    
    public function glosa(){
        return $this->belongsTo('Glosa','glosa_id');
    }
    
    static function errores($datos){
         
        $rules = array(
            /*'glosa_id' => 'required',
            'racaudador_id' => 'required'*/
        );

        $message = array(
            'codigo.required' => 'Obligatorio!'
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
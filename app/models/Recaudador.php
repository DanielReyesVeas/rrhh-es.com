<?php

class Recaudador extends Eloquent {
    
    protected $table = 'recaudadores';
    protected $connection = "principal";

    /*public function codigo()
    {
        return $this->belongsToMany('Glosa', 'tipos_estructura_glosa_recaudador');
    }*/
    
    static function listaRecaudadores(){
    	$listaRecaudadores = array();
    	$recaudadores = Recaudador::orderBy('id', 'ASC')->get();
    	if( $recaudadores->count() ){
            foreach( $recaudadores as $recaudador ){
                $listaRecaudadores[]=array(
                    'id' => $recaudador->id,
                    'nombre' => $recaudador->nombre
                );
            }
    	}
    	return $listaRecaudadores;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'recaudador.required' => 'Obligatorio!'
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

<?php

class PlantillaFiniquito extends Eloquent {
    
    protected $table = 'plantillas_finiquitos';
    
    public function clausulaFiniquito(){
        return $this->hasMany('ClausulaFiniquito','plantilla_finiquito_id');
    }    
    
    static function listaPlantillasFiniquito()
    {        
    	$listaPlantillasFiniquito = array();
    	$plantillasFiniquitos = PlantillaFiniquito::orderBy('nombre', 'ASC')->get();
    	if( $plantillasFiniquitos->count() ){
            foreach( $plantillasFiniquitos as $plantillaFiniquito ){
                $listaPlantillasFiniquito[]=array(
                    'id' => $plantillaFiniquito->id,
                    'sid' => $plantillaFiniquito->sid,
                    'nombre' => $plantillaFiniquito->nombre
                );
            }
    	}
    	return $listaPlantillasFiniquito;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required',
            'cuerpo' => 'required'
        );

        $message = array(
            'plantillaFiniquito.required' => 'Obligatorio!'
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
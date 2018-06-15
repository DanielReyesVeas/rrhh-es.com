<?php

class AsignacionFamiliar extends Eloquent {
    
    protected $table = 'asignacion_familiar';
    protected $connection = "principal";
    
    static function listaAsignacionFamiliar($mes=null){
        
        if(!$mes){
            $mes = \Session::get('mesActivo')->mes;
        }
        
    	$listaAsignacionFamiliar = array();
    	$asignacionesFamiliares = AsignacionFamiliar::where('mes', $mes)->orderBy('tramo', 'ASC')->get();
    	if( $asignacionesFamiliares->count() ){
            foreach( $asignacionesFamiliares as $asignacionFamiliar ){
                $listaAsignacionFamiliar[]=array(
                    'id' => $asignacionFamiliar->id,
                    'tramo' => $asignacionFamiliar->tramo,
                    'monto' => $asignacionFamiliar->monto,
                    'rentaMenor' => $asignacionFamiliar->renta_menor,
                    'rentaMayor' => $asignacionFamiliar->renta_mayor
                );
            }
    	}
        
    	return $listaAsignacionFamiliar;
    }
    
    static function listaTramos(){
        $mes = \Session::get('mesActivo');
        $fecha = $mes->mes;
        if(!$mes->indicadores){
            $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fecha)));
        }
    	$listaTramos = array();
    	$tramos = AsignacionFamiliar::where('mes', $mes)->orderBy('tramo', 'ASC')->get();
        
    	if( $tramos->count() ){
            foreach( $tramos as $tramo ){
                $listaTramos[] = $tramo->tramo;
            }
    	}
        
    	return $listaTramos;
    }
    
    static function errores($datos){
         
        $rules = array(
            'valor' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'asignacionFamiliar.required' => 'Obligatorio!'
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
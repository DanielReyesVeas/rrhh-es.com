<?php

class FactorActualizacion extends Eloquent {
    
    protected $table = 'factores_actualizacion';
    protected $connection = "principal";
    
    static function listaFactores($anio=null){
        
        if(!$anio){
            $anio = \Session::get('mesActivo')->anio;
        }
        
    	$listaFactoresActualizacion = array();
    	$factores = FactorActualizacion::where('anio', $anio)->orderBy('mes', 'ASC')->get();
    	if( $factores->count() ){
            foreach( $factores as $factor ){
                $listaFactoresActualizacion[]=array(
                    'id' => $factor->id,
                    'anio' => $factor->anio,
                    'mes' => $factor->mes,
                    'porcentaje' => $factor->porcentaje,
                    'factor' => $factor->factor
                );
            }
    	}
    	return $listaFactoresActualizacion;
    }
}
<?php

class CotizacionTrabajoPesado extends Eloquent {
    
    protected $table = 'cotizacion_trabajos_pesados';
    protected $connection = "principal";
    
    static function listaCotizacionTrabajosPesados($mes=null){
        
        if(!$mes){
            $mes = \Session::get('mesActivo')->mes;
        }
        
    	$listaCotizacionTrabajosPesados = array();
    	$cotizacionTrabajosPesados = CotizacionTrabajoPesado::where('mes', $mes)->orderBy('id', 'ASC')->get();
    	if( $cotizacionTrabajosPesados->count() ){
            foreach( $cotizacionTrabajosPesados as $cotizacionTrabajoPesado ){
                $listaCotizacionTrabajosPesados[]=array(
                    'id' => $cotizacionTrabajoPesado->id,
                    'trabajo' => $cotizacionTrabajoPesado->trabajo,
                    'valor' => $cotizacionTrabajoPesado->valor,
                    'financiamientoEmpleador' => $cotizacionTrabajoPesado->financiamiento_empleador,
                    'financiamientoTrabajador' => $cotizacionTrabajoPesado->financiamiento_trabajador
                );
            }
    	}
    	return $listaCotizacionTrabajosPesados;
    }
    
    static function errores($datos){
         
        $rules = array(
            'valor' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'cotizacionTrabajoPesado.required' => 'Obligatorio!'
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
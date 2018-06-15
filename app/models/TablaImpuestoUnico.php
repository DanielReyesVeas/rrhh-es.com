<?php

class TablaImpuestoUnico extends Eloquent {
    
    protected $table = 'tabla_impuesto_unico';
    protected $connection = "principal";
        
    static function tabla()
    {
        $mes = \Session::get('mesActivo')->mes;
        $tablaImpuestoUnico = TablaImpuestoUnico::where('mes', $mes)->orderBy('tramo')->get();
        
        $listaTablaImpuestoUnico=array();
        if( $tablaImpuestoUnico->count() ){
            foreach( $tablaImpuestoUnico as $tabla ){
                $listaTablaImpuestoUnico[]=array(
                    'id' => $tabla->id,
                    'tramo' => $tabla->tramo,
                    'imponibleMensualDesde' => $tabla->imponible_mensual_desde,
                    'imponibleMensualHasta' => $tabla->imponible_mensual_hasta,
                    'factor' => $tabla->factor,
                    'cantidadARebajar' => $tabla->cantidad_a_rebajar
                );
            }
        }
        
        return $listaTablaImpuestoUnico;
    }
    
    static function errores($datos){
         
        $rules = array(
            'tramo' => 'required',
            'imponible_mensual_desde' => 'required',
            'imponible_mensual_hasta' => 'required',
            'factor' => 'required',
            'calntidad_a_rebajar' => 'required'
        );

        $message = array(
            'tablaImpuestoUnico.required' => 'Obligatorio!'
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
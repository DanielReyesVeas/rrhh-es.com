<?php

class RentaTopeImponible extends Eloquent {
    
    protected $table = 'rentas_topes_imponibles';
    protected $connection = "principal";
    
    static function listaRentasTopeImponibles($mes=null){
        
        if(!$mes){
            $mes = \Session::get('mesActivo')->mes;
        }
        
    	$listaRentasTopeImponibles = array();
    	$rentasTopeImponibles = RentaTopeImponible::where('mes', $mes)->orderBy('id', 'ASC')->get();
    	if( $rentasTopeImponibles->count() ){
            foreach( $rentasTopeImponibles as $rentaTopeImponible ){
                $listaRentasTopeImponibles[]=array(
                    'id' => $rentaTopeImponible->id,
                    'nombre' => $rentaTopeImponible->nombre,
                    'valor' => $rentaTopeImponible->valor
                );
            }
    	}
    	return $listaRentasTopeImponibles;
    }
    
    static function valor($nombre)
    {
        $mes = Session::get('mesActivo');
        $fecha = $mes->mes;
        if(!$mes->indicadores){
            $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fecha)));
        }
        $topeSeguro = RentaTopeImponible::where('mes', $fecha)->where('nombre', $nombre)->first();
        
        if($topeSeguro){
            return $topeSeguro->valor;
        }
        
        return NULL;
    }
    
    static function rti()
    {        
        $mes = \Session::get('mesActivo')->mes;
        $rti = 0;
    	$rentaTopeImponible = RentaTopeImponible::where('mes', $mes)->where('nombre', 'Para afiliados a una AFP')->first();
        
    	if( $rentaTopeImponible ){
            $rti = $rentaTopeImponible;
    	}
    	return $rti;
    }
    
    static function errores($datos){
         
        $rules = array(
            'valor' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'rentaTopeImponible.required' => 'Obligatorio!'
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
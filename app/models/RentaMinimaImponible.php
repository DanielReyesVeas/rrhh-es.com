<?php

class RentaMinimaImponible extends Eloquent {
    
    protected $table = 'rentas_minimas_imponibles';
    protected $connection = "principal";
    
    static function listaRentasMinimasImponibles($mes=null)
    {        
        if(!$mes){
            $mes = \Session::get('mesActivo')->mes;
        }
        
    	$listaRentasMinimasImponibles = array();
    	$rentasMinimasImponibles = RentaMinimaImponible::where('mes', $mes)->orderBy('id', 'ASC')->get();
    	if( $rentasMinimasImponibles->count() ){
            foreach( $rentasMinimasImponibles as $rentaMinimaImponible ){
                $listaRentasMinimasImponibles[]=array(
                    'id' => $rentaMinimaImponible->id,
                    'nombre' => $rentaMinimaImponible->nombre,
                    'valor' => $rentaMinimaImponible->valor
                );
            }
    	}
    	return $listaRentasMinimasImponibles;
    }
    
    static function rmi()
    {        
        $mes = \Session::get('mesActivo');
        $fecha = $mes->mes;
        if(!$mes->indicadores){
            $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($fecha)));
        }
        $rmi = 0;        
    	$rentaMinimaImponible = RentaMinimaImponible::where('mes', $fecha)->where('nombre', 'Trab. Dependientes e Independientes')->first();
        
    	if( $rentaMinimaImponible ){
            $rmi = $rentaMinimaImponible;
    	}
    	return $rmi;
    }
    
    static function errores($datos){
         
        $rules = array(
            'valor' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'rentaMinimaImponible.required' => 'Obligatorio!'
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
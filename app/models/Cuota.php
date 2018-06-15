<?php

class Cuota extends Eloquent {
    
    protected $table = 'cuotas';
    
    public function prestamo(){
        return $this->belongsTo('Prestamo','prestamo_id');
    }
    
    public function prestamoCuota(){        
        if( $this->prestamo ){
            $prestamo = $this->prestamo;
            $datosPrestamo = array(
                'id' => $prestamo->id,
                'sid' => $prestamo->sid,
                'mes' => $prestamo->mes,
                'prestamo_id' => $prestamo->prestamo_id,
                'moneda' => $prestamo->moneda,
                'monto' => $prestamo->monto
            );        
        }
        return $datosPrestamo;
    }
    
    static function errores($datos){
         
        $rules = array(
            'prestamo_id' => 'required',
            'monto' => 'required',
            'moneda' => 'required'
        );

        $message = array(
            'prestamo.required' => 'Obligatorio!'
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
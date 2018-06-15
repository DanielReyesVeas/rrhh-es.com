<?php

class DetalleLiquidacion extends Eloquent {
    
    protected $table = 'detalle_liquidacion';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    public function detalle(){
        if($this->tipo_id==1){
            return $this->belongsTo('TipoHaber','detalle_id');
        }else{
            return $this->belongsTo('TipoDescuento','detalle_id');
        }
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'detalleLiquidacion.required' => 'Obligatorio!'
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

<?php

class CartaNotificacion extends Eloquent {
    
    protected $table = 'cartas_notificacion';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function trabajadorCarta(){        
        if( $this->trabajador ){
            $trabajador = $this->trabajador;
            $empleado = $trabajador->ficha();
            $datosTrabajador = array(
                'id' => $trabajador->id,
                'sid' => $trabajador->sid,
                'nombreCompleto' => $empleado->nombreCompleto(),
                'rutFormato' => $trabajador->rut_formato()
            );        
        }
        return $datosTrabajador;
    }
    
    public function plantillaCartaNotificacion(){
        return $this->belongsTo('PlantillaCartaNotificacion','plantilla_carta_id');
    }
    
    public function documento(){
        return $this->belongsTo('Documento', 'documento_id');
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'cartaNotificacion.required' => 'Obligatorio!'
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
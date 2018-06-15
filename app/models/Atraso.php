<?php

class Atraso extends Eloquent {
    
    protected $table = 'atrasos';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function trabajadorAtraso(){        
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
    
    static function errores($datos){
         
        $rules = array(
            'trabajador_id' => 'required',
            'fecha' => 'required',
            'horas' => 'required',
            'minutos' => 'required'
        );

        $message = array(
            'atraso.required' => 'Obligatorio!'
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
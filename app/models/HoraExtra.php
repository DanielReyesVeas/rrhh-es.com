<?php

class HoraExtra extends Eloquent {
    
    protected $table = 'horas_extra';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function tipo(){
        return $this->belongsTo('TipoHoraExtra', 'tipo_id');
    }
    
    public function trabajadorHoraExtra(){        
        if( $this->trabajador ){
            $trabajador = $this->trabajador;
            $empleado = $trabajador->ficha();
            $datosTrabajador = array(
                'id' => $trabajador->id,
                'sid' => $trabajador->sid,
                'nombreCompleto' => $empleado->nombreCompleto(),
                'rutFormato' => $trabajador->rut_formato(),
                'tramos' => $trabajador->tramosHorasExtra()
            );        
        }
        return $datosTrabajador;
    }
    
    static function errores($datos){
         
        $rules = array(
            'trabajador_id' => 'required',
            'mes_id' => 'required',
            'fecha' => 'required',
            'cantidad' => 'required'
        );

        $message = array(
            'horaExtra.required' => 'Obligatorio!'
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
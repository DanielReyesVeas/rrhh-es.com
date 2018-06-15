<?php

class LibroRemuneraciones extends Eloquent {
        
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function empresa(){
        return $this->belongsTo('Empresa','empresa_id');
    }    
    
    static function errores($datos){
         
        $rules = array(
            'empresa_id' => 'required',
            'empresa_razon_social' => 'required',
            'empresa_rut' => 'required',
            'empresa_direccion' => 'required',
            'liquidacion_id' => 'required',
            'trabajador_id' => 'required',
            'trabajador_nombre' => 'required',
            'trabajador_rut' => 'required',
            'sueldo_base' => 'required',
            'total_haberes' => 'required',
            'dias_trabajados' => 'required',
            'sueldo' => 'required',
            'total_afp' => 'required',
            'inasistencias_atrasos' => 'required',
            'total_apv' => 'required',
            'gratificacion' => 'required',
            'total_salud' => 'required',
            'imponibles' => 'required',
            'total_imponibles' => 'required',
            'anticipos' => 'required'
        );

        $message = array(
            'libroRemuneraciones.required' => 'Obligatorio!'
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

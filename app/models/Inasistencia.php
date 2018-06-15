<?php

class Inasistencia extends Eloquent {
    
    protected $table = 'inasistencias';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function trabajadorInasistencia(){        
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
            'mes_id' => 'required',
            'desde' => 'required',
            'hasta' => 'required',
            'dias' => 'required',
            'motivo' => 'required'
        );

        $message = array(
            'inasistencia.required' => 'Obligatorio!'
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
<?php

class Licencia extends Eloquent {
    
    protected $table = 'licencias';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function trabajadorLicencia(){        
        if( $this->trabajador ){
            $trabajador = $this->trabajador;
            $datosTrabajador = array(
                'id' => $trabajador->id,
                'sid' => $trabajador->sid,
                'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
                'rutFormato' => Funciones::formatear_rut($trabajador->rut)
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
            'codigo' => 'required'
        );

        $message = array(
            'licencia.required' => 'Obligatorio!'
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
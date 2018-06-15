<?php

class Carga extends Eloquent {
    
    protected $table = 'cargas_familiares';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function tipoCarga(){
        return $this->belongsTo('TipoCarga','tipo_carga_id');
    }
    
    public function trabajadorCarga(){    
        $trabajador = $this->trabajador;
        $empleado = $trabajador->ficha();
        if($trabajador){
            $datosTrabajador = array(
                'id' => $trabajador->id,
                'idFicha' => $empleado->id,
                'sid' => $trabajador->sid,
                'nombreCompleto' => $empleado->nombreCompleto(),
                'rutFormato' => Funciones::formatear_rut($trabajador->rut)
            );        
        }
        return $datosTrabajador;
    }
    
    static function errores($datos){
         
        $rules = array(
            /*'trabajador_id' => 'required',
            'parentesco' => 'required',
            'rut' => 'required',
            'nombres' => 'required',
            'apellidos' => 'required',
            'fecha_nacimiento' => 'required',
            'sexo' => 'required'*/
        );

        $message = array(
            'carga.required' => 'Obligatorio!'
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
<?php

class Haber extends Eloquent {
    
    protected $table = 'haberes';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function mesDeTrabajo(){
        return $this->belongsTo('MesDeTrabajo','mes_id');
    }
    
    public function tipoHaber(){
        return $this->belongsTo('TipoHaber','tipo_haber_id');
    }
    
    public function trabajadorHaber()
    {  
        $datosTrabajador = array();
        if( $this->trabajador ){
            $trabajador = $this->trabajador;
            $empleado = $trabajador->ficha();
            $datosTrabajador = array(
                'id' => $trabajador->id,
                'sid' => $trabajador->sid,
                'nombreCompleto' => $empleado->nombreCompleto(),
                'rutFormato' => Funciones::formatear_rut($trabajador->rut),
                'cargo' => array(
                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                ),
                'seccion' => array(
                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                ), 
                'centroCosto' => array(
                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                )
            );        
        }
        
        return $datosTrabajador;
    }
    
    static function errores($datos){
         
        $rules = array(
            /*'trabajador_id' => 'required',
            'tipo_haber_id' => 'required',
            'moneda' => 'required',
            'monto' => 'required'*/
        );

        $message = array(
            'haber.required' => 'Obligatorio!'
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
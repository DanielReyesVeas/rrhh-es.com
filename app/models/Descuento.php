<?php

class Descuento extends Eloquent {
    
    protected $table = 'descuentos';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function tipoDescuento(){
        return $this->belongsTo('TipoDescuento','tipo_descuento_id');
    }
    
    public function mesDeTrabajo(){
        return $this->belongsTo('MesDeTrabajo','mes_id');
    }
    
    public function trabajadorDescuento()
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
            'tipo_descuento_id' => 'required',
            'moneda' => 'required',
            'monto' => 'required'*/
        );

        $message = array(
            'descuento.required' => 'Obligatorio!'
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
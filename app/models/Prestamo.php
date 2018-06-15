<?php

class Prestamo extends Eloquent {
    
    protected $table = 'prestamos';
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    public function cuotas(){
        return $this->hasMany('Cuota','prestamo_id');
    }
    
    public function trabajadorPrestamo()
    {        
        if( $this->trabajador ){
            $trabajador = $this->trabajador;
            $datosTrabajador = array(
                'id' => $trabajador->id,
                'sid' => $trabajador->sid,
                'nombreCompleto' => $trabajador->nombres . " " . $trabajador->apellidos,
                'rutFormato' => Funciones::formatear_rut($trabajador->rut)
            );        
        }
        return $datosTrabajador;
    }
    
    public function cuotasPrestamo()
    {   
        $idPrestamo = $this->id;
        $cuotas = Cuota::where('prestamo_id', $idPrestamo)->orderBy('mes')->get();
        $datosCuotas = array();
        if($cuotas){            
            foreach($cuotas as $cuota){
                $datosCuotas[] = array(
                    'id' => $cuota->id,
                    'sid' => $cuota->sid,
                    'numero' => $cuota->numero,
                    'monto' => $cuota->monto,
                    'mes' => $cuota->mes
                );     
            }
        }
        return $datosCuotas;
    }
    
    public function cuotaPagar()
    {
        $idPrestamo = $this->id;
        $cuota = new stdClass();
        $mes = \Session::get('mesActivo')->mes;
        $cuotas = Cuota::where('prestamo_id', $idPrestamo)->where('mes', $mes)->first();

        $cuota->id = $cuotas->id;
        $cuota->sid = $cuotas->sid;
        $cuota->numero = $cuotas->numero;
        $cuota->monto = $cuotas->monto;
        
        return $cuota;
    }
    
    public function eliminarPrestamo()
    {
        $idPrestamo = $this->id;
        $cuotas = Cuota::where('prestamo_id', $idPrestamo)->get();
        if($cuotas->count()){
            foreach($cuotas as $cuota){
                $cuota->delete();
            }
        }
        $this->delete();
    }
    
    static function errores($datos){
        
        if($datos['id']){
            $rules =    array(
                'codigo' => 'required|unique:prestamos,codigo,'.$datos['id']
            );
        }else{
            $rules =    array(
                'codigo' => 'required|unique:prestamos,codigo'
            );
        }

        $message =  array(
            'trabajador_id.required' => 'Obligatorio!',
            'codigo.required' => 'Obligatorio!',
            'glosa.required' => 'Obligatorio!',
            'nombre_liquidacion.required' => 'Obligatorio!',
            'moneda.required' => 'Obligatorio!',
            'monto.required' => 'Obligatorio!',
            'codigo.unique' => 'El Código ya se encuentra registrado!',
            'primera_cuota.required' => 'Obligatorio!',
            'ultima_cuota.required' => 'Obligatorio!'
        );

        $verifier = App::make('validation.presence');

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            // la validacion tubo errores
            return $validation->getMessageBag()->toArray();
        }else{
            // no hubo errores de validacion
            return false;
        }
    }
}
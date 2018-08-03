<?php

class TipoHoraExtra extends Eloquent {
    
    protected $table = 'tipos_hora_extra';
    
    public function horasExtra(){
        return $this->hasMany('HoraExtra', 'tipo_id');
    }
    
    public function miCuenta(){
        return $this->belongsTo('Cuenta', 'cuenta_id');
    }
    
    public function cuenta($cuentas = null, $centroCostoId=null)
    {
        $empresa = Session::get('empresa');
        
        if($empresa->centro_costo){
            return $this->horaExtraCuenta($cuentas, $centroCostoId);
        }else{
            if($this->cuenta_id){
                if(!$cuentas){
                    $cuentas = Cuenta::listaCuentas();
                }
                $idCuenta = $this->cuenta_id;
                if(array_key_exists($idCuenta, $cuentas)){
                    return $cuentas[$idCuenta];
                }
            }
        }            
        
        return null;
	}
    
    public function horaExtraCuenta($cuentasCodigo = null, $centroCostoId=null)
    {
        if($centroCostoId){
            $codigo=null;
            if(!$cuentasCodigo){
                $cuentas = Cuenta::listaCuentas();
            }
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'horaExtra')
                ->where('concepto_id', $this->id)
                ->where('centro_costo_id', $centroCostoId )
                ->first();

            if( $centroCostoCuenta ){
                if(array_key_exists($centroCostoCuenta->cuenta_id, $cuentasCodigo)){
                    return $cuentasCodigo[$centroCostoCuenta->cuenta_id];
                }
            }
        }else{
            $empresa = Session::get('empresa');
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'horaExtra')
                ->where('concepto_id', $this->id)
                ->get();
            if($centroCostoCuenta->count()){
                $asignables = $empresa->centrosAsignables();
                if($centroCostoCuenta->count()==$asignables){
                    return 2;
                }else{
                    return 1;							
                }
            }else{
                return 0;
            }
        }

        return null;
    }
    
    static function isCuentas()
    {
        $tiposHoraExtra = TipoHoraExtra::all();
        $bool = true;
        foreach($tiposHoraExtra as $tipoHoraExtra){
            if(!$tipoHoraExtra->cuenta_id){
                $bool = false;
            }
        }
        
        return $bool;
    }
    
    static function listaTiposHoraExtra()
    {
        $tiposHoraExtra = TipoHoraExtra::all();
        $listaTiposHoraExtra = array();
        
        if($tiposHoraExtra->count()){
            foreach($tiposHoraExtra as $tipoHoraExtra){
                $listaTiposHoraExtra[] = array(
                    'id' => $tipoHoraExtra->id,
                    'sid' => $tipoHoraExtra->sid,
                    'nombre' => $tipoHoraExtra->nombre
                );
            }
        }
        
        return $listaTiposHoraExtra;
    }
    
    public function comprobarDependencias()
    {
        $horasExtra = $this->horasExtra;        
        
        if($horasExtra->count()){
            $errores = new stdClass();
            $errores->error = array("El Tipo de Hora Extra <b>" . $this->nombre . "</b> se encuentra asignado.<br /> Debe <b>eliminar</b> estas horas extra primero para poder realizar esta acción.");
            
            return $errores;
        }
        
        return;
    }
    
    static function errores($datos){
        
        if($datos['id']){
            $rules = array(
                'codigo' => 'required|unique:tipos_hora_extra,codigo,'.$datos['id'],
                'nombre' => 'required|unique:tipos_hora_extra,nombre,'.$datos['id']
            );
        }else{
            $rules = array(
                'codigo' => 'required|unique:tipos_hora_extra,codigo',
                'nombre' => 'required|unique:tipos_hora_extra,nombre'
            );
        }

        $message = array(
            'tipoHoraExtra.required' => 'Obligatorio!'
        );
        $message =  array(
            'nombre.required' => 'Obligatorio!',
            'codigo.required' => 'Obligatorio!',
            'nombre.unique' => 'El nombre ya se encuentra registrado!',
            'codigo.unique' => 'El código ya se encuentra registrado!'
        );
        $verifier = App::make('validation.presence');
        //$verifier->setConnection("principal");

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
<?php

class Aporte extends Eloquent {
    
    protected $table = 'aportes_cuentas';
    
    public function miCuenta(){
        return $this->belongsTo('Cuenta', 'cuenta_id');
    }
    
    function cuentaCentroCosto(){
    	return $this->hasOne('CuentaCentroCosto', 'concepto_id');
    }
    
    public function cuenta($cuentas=null, $centroCostoId=null)
    {
        $empresa = Session::get('empresa');
                
        if($empresa->centro_costo){
            return $this->aporteCuenta($cuentas, $centroCostoId);
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

            return null;
        }
    }
    
    public function aporteCuenta($cuentasCodigo = null, $centroCostoId=null)
    {
        if($centroCostoId){
            $codigo=null;
            if(!$cuentasCodigo){
                $cuentas = Cuenta::listaCuentas();
            }
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'aporte')
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
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'aporte')
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
   
    static function aportes()
    {
        $aportes = Aporte::all();
        
        return $aportes;
    }
    
    static function isCuentas()
    {
        $aportes = Aporte::all();
        $bool = true;
        foreach($aportes as $aporte){
            if(!$aporte->cuenta_id){
                $bool = false;
            }
        }
        
        return $bool;
    }
    
    public function afp(){
        $afp = Glosa::find($this->nombre);
        
        return $afp->glosa;
	}
    
    public function exCaja(){
        $exCaja = Glosa::find($this->nombre);
        
        return $exCaja->glosa;
	}
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'aporte.required' => 'Obligatorio!'
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
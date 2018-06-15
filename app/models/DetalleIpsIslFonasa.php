<?php

class DetalleIpsIslFonasa extends Eloquent {
    
    protected $table = 'detalles_ips_isl_fonasa';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    /*public function cuenta($cuentasCodigo, $centroCostoId){
        $descuento = TipoDescuento::where('estructura_descuento_id', 9)->where('nombre', 246)->first();
        if($descuento){
            $codigo=null;
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'descuento')
                ->where('concepto_id', $descuento->id)
                ->where('centro_costo_id', $centroCostoId )
                ->first();

            if( $centroCostoCuenta ){
                if(array_key_exists($centroCostoCuenta->cuenta_id, $cuentasCodigo)){
                    $codigo = $cuentasCodigo[$centroCostoCuenta->cuenta_id];
                    return $codigo;
                }
            }
        }
        return null;
    }*/
    
    public function cuenta($cuentasCodigo, $centroCostoId)
    {
        $descuento = TipoDescuento::where('estructura_descuento_id', 9)->where('nombre', 246)->first();
        if($descuento){
            $codigo = $descuento->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
            }
        }
        return null;
    }
    
    public function cuentaIsl($cuentasCodigo, $centroCostoId){
        $aporte = Aporte::find(1);
        if($aporte){
            $codigo = $aporte->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
            }
        }
        return null;
    }
    
    /*public function cuentaIsl($cuentasCodigo, $centroCostoId){
        $aporte = Aporte::find(1);
        if($aporte){
            $codigo=null;
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'aporte')
                ->where('concepto_id', $aporte->id)
                ->where('centro_costo_id', $centroCostoId )
                ->first();

            if( $centroCostoCuenta ){
                if(array_key_exists($centroCostoCuenta->cuenta_id, $cuentasCodigo)){
                    $codigo = $cuentasCodigo[$centroCostoCuenta->cuenta_id];
                    return $codigo;
                }
            }
        }
        return null;
    }*/
    
    public function codigoExCaja()
    {
        $exCaja = $this->ex_caja_id;
        $codigo = Codigo::find($exCaja)->codigo;
        
        return $codigo;
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'detalleIpsIslFonasa.required' => 'Obligatorio!'
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

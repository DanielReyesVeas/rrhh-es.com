<?php

class DetalleAfp extends Eloquent {
    
    protected $table = 'detalles_afp';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    public function afp(){
        return $this->belongsTo('Glosa', 'afp_id');
    }
    
    /*public function cuenta($cuentasCodigo, $centroCostoId){
        $aporte = Aporte::where('tipo_aporte', 4)->where('nombre', $this->afp_id)->first();
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
    
    public function cuenta($cuentasCodigo=null, $centroCostoId=null)
    {
        $aporte = Aporte::where('tipo_aporte', 4)->where('nombre', $this->afp_id)->first();
        if($aporte){
            $codigo = $aporte->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
            }
        }
        return null;
    }
    
    public function cuentaSis($cuentasCodigo=null, $centroCostoId=null)
    {
        $aporte = Aporte::where('tipo_aporte', 2)->where('nombre', $this->afp_id)->first();
        if($aporte){
            $codigo = $aporte->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
            }
        }
        return null;
    }
    
    /*public function cuentaSis($cuentasCodigo, $centroCostoId){
        $aporte = Aporte::where('tipo_aporte', 2)->where('nombre', $this->afp_id)->first();
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
    
    public function cuentaAhorro($cuentasCodigo, $centroCostoId){
        $descuento = TipoDescuento::where('estructura_descuento_id', 7)->where('nombre', $this->afp_id)->first();
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
    }
        
    public function codigoAfp()
    {
        $afp = $this->afp_id;
        $codigo = Codigo::find($afp)->codigo;
        
        return $codigo;
    }
    
    public function nombreAfp()
    {
        $afp = $this->afp;
        $nombre = '';
        if($afp){
            $nombre = $afp->glosa;
        }
        
        return $nombre;
    }
    
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'detalleAfp.required' => 'Obligatorio!'
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

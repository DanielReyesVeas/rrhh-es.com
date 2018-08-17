<?php

class DetalleMutual extends Eloquent {
    
    protected $table = 'detalles_mutual';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    public function mutual(){
        return $this->belongsTo('Glosa', 'mutual_id');
    }
    
    public function codigoMutual()
    {        
        $mutual = $this->mutual_id;
        $codigo = Codigo::find($mutual)->codigo;
        
        return $codigo;
    }
    
    public function nombreMutual()
    {        
        $mutual = $this->mutual;
        $nombre = '';
        if($mutual){
            $nombre = $mutual->glosa;
        }
        
        return $nombre;
    }
    
    /*public function cuenta($cuentasCodigo, $centroCostoId){
        $aporte = Aporte::find(2);
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
    
    public function cuenta($cuentasCodigo, $centroCostoId)
    {
        $aporte = Aporte::find(2);
        if($aporte){
            $codigo = $aporte->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
            }
        }
        return null;
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'detalleMutual.required' => 'Obligatorio!'
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

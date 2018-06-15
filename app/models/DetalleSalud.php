<?php

class DetalleSalud extends Eloquent {
    
    protected $table = 'detalles_salud';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    public function isapre(){
        return $this->belongsTo('Glosa', 'salud_id');
    }
    
    /*public function cuenta($cuentasCodigo, $centroCostoId){
        $descuento = TipoDescuento::where('estructura_descuento_id', 9)->where('nombre', $this->salud_id)->first();
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
        $descuento = TipoDescuento::where('estructura_descuento_id', 9)->where('nombre', $this->salud_id)->first();
        if($descuento){
            $codigo = $descuento->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
            }
        }
        return null;
    }
    
    public function codigoSalud()
    {        
        $salud = $this->salud_id;
        $codigo = Codigo::find($salud)->codigo;
        
        return $codigo;
    }
    
    public function nombreSalud()
    {
        $salud = $this->isapre;
        $nombre = '';
        if($salud){
            $nombre = $salud->glosa;
        }
        
        return $nombre;
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'detalleSalud.required' => 'Obligatorio!'
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

<?php

class DetalleCaja extends Eloquent {
    
    protected $table = 'detalles_caja';

    /*public function cuenta($cuentasCodigo, $centroCostoId){
    //    $caja = TipoDescuento::where('estructura_descuento_id', 6)->where('codigo', $this->codigoCaja() )->first();
        $caja = TipoDescuento::where('estructura_descuento_id', 6)->where('codigo', 401 )->first();
        if($caja){
            $codigo=null;
            $centroCostoCuenta = CuentaCentroCosto::where('concepto', 'descuento')
                ->where('concepto_id', $caja->id)
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
    //    $caja = TipoDescuento::where('estructura_descuento_id', 6)->where('codigo', $this->codigoCaja() )->first();
        $caja = TipoDescuento::where('estructura_descuento_id', 6)->where('nombre', 'Caja de Compensación' )->first();
        if($caja){
            $codigo = $caja->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
            }
        }
        return null;
    }
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    public function codigoCaja()
    {        
        $caja = $this->caja_id;
        $codigo = Codigo::find($caja)->codigo;
        
        return $codigo;
    }
    
    public function rentaImponible()
    {
        $ri = $this->renta_imponible;
        $mes = \Session::get('mesActivo')->mes;
        $topeCaja = RentaTopeImponible::where('mes', $mes)->where('nombre', 'Para afiliados a una AFP')->first()->valor;
        $tope = Funciones::convertirUF($topeCaja);
        if($ri > $tope){
            return $tope;
        }
        
        return $ri;
    }
    
    static function errores($datos){
         
        $rules = array(

        );

        $message = array(
            'detalleCaja.required' => 'Obligatorio!'
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

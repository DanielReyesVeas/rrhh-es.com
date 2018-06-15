<?php

class DetalleApvc extends Eloquent {
    
    protected $table = 'detalles_apvc';
    
    public function liquidacion(){
        return $this->belongsTo('Liquidacion','liquidacion_id');
    }
    
    public function afp(){
        return $this->belongsTo('Glosa', 'afp_id');
    }

    public function cuenta($cuentasCodigo, $centroCostoId){
        $descuento = TipoDescuento::where('estructura_descuento_id', 3)->where('nombre', $this->afp_id)->first();
        if($descuento){
            $codigo = $descuento->cuenta($cuentasCodigo, $centroCostoId);
            if($codigo){
                return $codigo;
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
            'detalleApvc.required' => 'Obligatorio!'
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

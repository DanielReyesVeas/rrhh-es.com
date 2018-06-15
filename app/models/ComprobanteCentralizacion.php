<?php

class ComprobanteCentralizacion extends \Eloquent {
    protected $table = 'comprobantes_centralizacion';
    
    public function detalles(){
        return $this->hasMany('DetalleComprobanteCentralizacion','comprobante_id')->orderBy('id');
    }
    
    public function comprobanteDetalles()
    {
        $detalles = $this->detalles;
        $datos = array();
        if($detalles->count()){
            foreach($detalles as $detalle){
                $datos[] = array(
                    'id' => $detalle->id,
                    'cuenta' => $detalle->cuenta,
                    'comentario' => $detalle->comentario,
                    'referencia' => $detalle->referencia,
                    'debe' => $detalle->debe,
                    'haber' => $detalle->haber,
                    'pais' => $detalle->pais,
                    'canal' => $detalle->canal,
                    'tienda' => $detalle->tienda
                );
            }
        }
        return $datos;
    }
    
}
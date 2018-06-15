<?php

class DetalleComprobanteCentralizacion extends \Eloquent {
    protected $table = 'detalles_comprobante_centralizacion';
    
    public function comprobante(){
        return $this->belongsTo('ComprobanteCentralizacion','comprobante_id');
    }
    
}
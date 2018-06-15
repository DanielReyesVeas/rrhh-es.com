<?php

class DetalleF1887 extends Eloquent {
    
    protected $table = 'detalle_f1887';
    
    public function f1887(){
        return $this->belongsTo('f1887','f1887_id');
    }
    
}
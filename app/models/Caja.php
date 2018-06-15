<?php

class Caja extends Eloquent {
    
    protected $table = 'cajas';
    
    public function caja(){
        return $this->belongsTo('Glosa', 'caja_id');
    }
    
    public function anioRemuneracion(){
        return $this->belongsTo('AnioRemuneracion','anio_id');
    }

}
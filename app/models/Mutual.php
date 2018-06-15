<?php

class Mutual extends Eloquent {
    
    protected $table = 'mutuales';
    
    public function mutual(){
        return $this->belongsTo('Glosa', 'mutual_id');
    }
    
    public function anioRemuneracion(){
        return $this->belongsTo('AnioRemuneracion','anio_id');
    }

}
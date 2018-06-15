<?php

class JornadaTramo extends Eloquent {
    
    protected $table = 'jornadas_tramos';
    
    public function jornada(){
        return $this->belongsTo('Jornada','jornada_id');
    }
    
    public function tramo(){
        return $this->belongsTo('TramoHoraExtra','tramo_id');
    }
    
}
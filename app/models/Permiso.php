<?php

class Permiso extends Eloquent {
    
    protected $table = 'permisos';
    
    function usuario(){
    	return $this->belongsTo('Usuario', 'usuario_id');
    }
   
}
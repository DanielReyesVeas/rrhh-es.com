<?php

class EstructuraDescuento extends Eloquent {
    
    protected $table = 'estructuras_descuento';
    
    public function tiposDescuentos(){
        return $this->hasMany('TipoDescuento','estructura_descuento_id');
    }
    
    static function estructuras()
    {
        $estructuras = EstructuraDescuento::orderBy("id")->get();
        $lista = array();
        foreach($estructuras as $estructura){
            if($estructura->id<3){
                $lista[] = $estructura;
            }
        }
        
        return $lista;    
    }
    
}
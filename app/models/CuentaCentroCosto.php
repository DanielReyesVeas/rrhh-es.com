<?php

class CuentaCentroCosto extends Eloquent {
    
    protected $table = 'cuenta_centro_costo';
    
    public function centroCosto(){
        return $this->belongsTo('CentroCosto','centro_costo_id');
    }
    
    public function cuenta(){
        return $this->belongsTo('Cuenta','cuenta_id');
    }
    
    public function aporte(){
        return $this->belongsTo('Aporte','concepto_id');
    }
    
    public function tipoDescuento(){
        return $this->belongsTo('TipoDescuento','concepto_id');
    }
    
    public function tipoHaber(){
        return $this->belongsTo('TipoHaber','concepto_id');
    }
    
}
<?php

class ZonaImpuestoUnico extends Eloquent {
    
    protected $table = 'zonas_impuesto_unico';
    
    static function listaZonas()
    {
        $zonas = ZonaImpuestoUnico::all();
        $datos = array();
        
        if($zonas->count()){
            foreach($zonas as $zona){
                $datos[] = array(
                    'id' => $zona->id,
                    'nombre' => $zona->nombre,
                    'porcentaje' => $zona->porcentaje,
                    'select' => $zona->nombre . ' ( ' . $zona->porcentaje . ' % )' 
                );
            }
        }
        
        return $datos;
    }
    
    public function empresa(){
		return $this->belongsTo('Empresa', 'empresa_id');
	}    

}
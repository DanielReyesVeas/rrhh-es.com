<?php

class Comuna extends \Eloquent {
    protected $table = 'comunas';
    protected $connection = "principal";
    
	public function provincia(){
		return $this->belongsTo('Provincia', 'provincia_id');
	}
    
	public function localidad(){
		return $this->comuna.", ".$this->provincia->provincia." / ".$this->provincia->region->region;
	}
    
	static function listaComunas(){
        $lista=array();
        $comunas = Comuna::orderBy('comuna', 'ASC')->get();
        if( $comunas->count() ){
            foreach($comunas as $comuna){
                $lista[]=array(
                    'id' => $comuna->id,
                    'localidad' => $comuna->localidad()	
                );
            }
        }
        return $lista;
	}
    
    static function codigosComunas(){
        $lista=array();
        $comunas = Comuna::orderBy('comuna', 'ASC')->get();
        if( $comunas->count() ){
            foreach($comunas as $comuna){
                $lista[]=array(
                    'id' => $comuna->id,
                    'glosa' => $comuna->comuna	
                );
            }
        }
        return $lista;
	}
}
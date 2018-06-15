<?php

class Tienda extends Eloquent {
    
    protected $table = 'tiendas';
    
    public function fichas(){
        return $this->hasMany('FichaTrabajador', 'tienda_id');
    }
    
    static function listaTiendas(){
    	$listaTiendas = array();
    	$tiendas = Tienda::orderBy('nombre', 'ASC')->get();
    	if( $tiendas->count() ){
            foreach( $tiendas as $tienda ){
                $listaTiendas[]=array(
                    'id' => $tienda->id,
                    'nombre' => $tienda->codigo . ' - ' . $tienda->nombre
                );
            }
    	}
    	return $listaTiendas;
    }
    
    static function codigosTiendas(){
    	$codigosTiendas = array();
    	$tiendas = Tienda::orderBy('nombre', 'ASC')->get();
    	if( $tiendas->count() ){
            foreach( $tiendas as $tienda ){
                $codigosTiendas[]=array(
                    'codigo' => $tienda->id,
                    'glosa' => $tienda->nombre
                );
            }
    	}
    	return $codigosTiendas;
    }
    
    public function comprobarDependencias()
    {
        $fichas = $this->fichas;        
        
        if($fichas->count()){
            $errores = new stdClass();
            $errores->error = array("La Tienda <b>" . $this->nombre . "</b> se encuentra asignada.<br /> Debe <b>reasignar</b> los trabajadores primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
        
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'tienda.required' => 'Obligatorio!'
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            return $validation->messages();
        }else{
            return false;
        }
    }
}
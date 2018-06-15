<?php

class Seccion extends Eloquent {
    
    protected $table = 'secciones';
    
    function obtenerDependencia(){
        return $this->belongsTo('Seccion', 'dependencia_id');
    }
    
    function obtenerEncargado(){
        return $this->belongsTo('Trabajador', 'encargado_id');
    }
    
    function obtenerHijos(){
    	return $this->hasMany('Seccion', 'dependencia_id');
    }
    
    public function fichas(){
        return $this->hasMany('FichaTrabajador', 'seccion_id');
    }
    
    static function listaSecciones(&$lista, $padreId, $nivel)
    {
        //obtener hijos de padreId
        $secciones = Seccion::where('dependencia_id', $padreId)->orderBy('nombre', 'ASC')->get();
        if( $secciones->count() ){
            foreach( $secciones as $seccion ){
                $lista[]=array(
                    'id' => $seccion->id,
                    'sid' => $seccion->sid,
                    'nombre' => $seccion->nombre,
                    'nivel' => $nivel,
                    'codigo' => $seccion->codigo,
                    'dependencia' => $seccion->dependencia_id,
                    'isPadre' => $seccion->isPadre(),
                    //'encargado' => $seccion->encargado()
                );
                if( $seccion->obtenerHijos->count() ){
                    Seccion::listaSecciones( $lista, $seccion->id, $nivel+1 );
                }
            }
        }
    }
    
    public function isPadre()
    {
        $dependencias = Seccion::where('dependencia_id', $this->id)->get();
        if($dependencias->count()){
            return true;
        }
        
        return false;
    }
    
    static function codigosSecciones()
    {
        $lista = array();
        $secciones = Seccion::orderBy('nombre', 'ASC')->get();
        if( $secciones->count() ){
            foreach( $secciones as $seccion ){
                $lista[]=array(
                    'codigo' => $seccion->id,
                    'glosa' => $seccion->nombre
                );
            }
        }
        
        return $lista;
    }
    
    static function listaSeccionesDependencia(&$lista, $padreId, $nivel, $seccion_id){
        //obtener hijos de padreId
        //Sin la misma sección como dependencia
        
        $secciones = Seccion::where('dependencia_id', $padreId)->where('id', '<>', $seccion_id)->orderBy('nombre', 'ASC')->get();
        if( $secciones->count() ){
            foreach( $secciones as $seccion ){
                $lista[]=array(
                    'id' => $seccion->id,
                    'sid' => $seccion->sid,
                    'nombre' => $seccion->nombre,
                    'codigo' => $seccion->codigo,
                    'nivel' => $nivel,
                    //'encargado' => $seccion->encargado()
                );
                if( $seccion->obtenerHijos->count() ){
                    Seccion::listaSeccionesDependencia( $lista, $seccion->id, $nivel+1, $seccion_id );
                }
            }
        }
    }
        
    function nivel()
    {
        $seccion = $this;
        $nivel = 0;
        while($seccion->obtenerDependencia){
            $nivel++;
            $seccion = $seccion->obtenerDependencia;
        }        
        return $nivel;
    }
    
    function dependencia()
    {
        $dependencia = $this->obtenerDependencia;
        $datosDependencia = array();
        if($dependencia){
            $datosDependencia = array(
                'id' => $dependencia->id,
                'sid' => $dependencia->sid,
                'codigo' => $dependencia->codigo,
                'nombre' => $dependencia->nombre
            );
        }
        
        return $datosDependencia;
    }
    
    function encargado()
    {
        $encargado = $this->obtenerEncargado;
        $datosEncargado = array();
        if($encargado){
            $datosEncargado = array(
                'id' => $encargado->id,
                'sid' => $encargado->sid,
                'nombreCompleto' => $encargado->nombres . ' ' . $encargado->apellidos
            );
        }
        
        return $datosEncargado;
    }
    
    public function comprobarDependencias()
    {
        $fichas = $this->fichas;        
        
        if($fichas->count()){
            $errores = new stdClass();
            $errores->error = array("La Sección <b>" . $this->nombre . "</b> se encuentra asignada.<br /> Debe <b>reasignar</b> los trabajadores primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
    
    static function errores($datos)
    {
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'seccion.required' => 'Obligatorio!'
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
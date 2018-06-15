<?php

class Jornada extends Eloquent {
    
    protected $table = 'jornadas';
    
    public function tramoHoraExtra(){
        return $this->belongsTo('TramoHoraExtra', 'tramo_hora_extra_id');
    }
    
    public function fichas(){
        return $this->hasMany('FichaTrabajador', 'tipo_jornada_id');
    }
    
    public function jornadaTramo(){
        return $this->hasMany('JornadaTramo', 'jornada_id');
    }
    
    public function tramos()
    {
        $jornadasTramos = $this->jornadaTramo;
        $tramos = array();
        $mostrar = '';
        if($jornadasTramos->count()){
            foreach($jornadasTramos as $index => $jornadaTramo){
                $tramos[] = array(
                    'id' => $jornadaTramo->id,
                    'idTramo' => $jornadaTramo->tramo->id,
                    'factor' => $jornadaTramo->tramo->factor
                );                
                $mostrar .= $jornadaTramo->tramo->factor;
                if(($index + 1) != $jornadasTramos->count()){
                    $mostrar .= ', ';
                }
            }
        }
        
        $datos = array(
            'tramos' => $tramos,
            'mostrar' => $mostrar
        );
        
        return $datos;
    }
    
    public function comprobarTramos($tramos)
    {
        $misTramos = $this->jornadaTramo;
        $update = array();
        $create = array();
        $destroy = array();
        
        if($misTramos){
            foreach($tramos as $tramo)
            {
                $isUpdate = false;
                
                if(isset($tramo['id'])){    
                    foreach($misTramos as $miTramo)
                    {
                        if($tramo['id'] == $miTramo->id){
                            $update[] = array(
                                'id' => $tramo['id'],
                                'jornada_id' => $this->id,
                                'tramo_id' => $tramo['idTramo'],
                                'factor' => $tramo['factor']
                            );
                            $isUpdate = true;
                        }                        
                        if($isUpdate){
                            break;
                        }
                    }
                }else{
                    $create[] = array(
                        'jornada_id' => $this->id,
                        'tramo_id' => $tramo['idTramo'],
                        'factor' => $tramo['factor']
                    );
                }
            }

            foreach($misTramos as $miTramo)
            {
                $isTramo = false;
                foreach($tramos as $tramo)
                {
                    if(isset($tramo['id'])){
                        if($miTramo->id == $tramo['id']){
                            $isTramo = true;                        
                        }
                    }
                }
                if(!$isTramo){
                    $destroy[] = array(
                        'id' => $miTramo->id
                    );
                }
            }
        }else{
            $create = $tramos;
        }
        
        $datos = array(
            'create' => $create,
            'update' => $update,
            'destroy' => $destroy
        );
        
        return $datos;
    }
    
    public function eliminarTramos()
    {
        $jornadasTramos = $this->jornadaTramo;
        
        if($jornadasTramos->count()){
            foreach($jornadasTramos as $jornadaTramo){
                $jornadaTramo->delete();
            }
        }
        
        return;
    }
    
    static function listaJornadas(){
    	$listaJornadas = array();
    	$jornadas = Jornada::orderBy('nombre', 'ASC')->get();
    	if( $jornadas->count() ){
            foreach( $jornadas as $jornada ){
                $listaJornadas[]=array(
                    'id' => $jornada->id,
                    'nombre' => $jornada->nombre
                );
            }
    	}
    	return $listaJornadas;
    }
    
    static function codigosTiposJornada(){
    	$codigosTiposJornadas = array();
    	$jornadas = Jornada::orderBy('id', 'ASC')->get();
    	if( $jornadas->count() ){
            foreach( $jornadas as $jornada ){
                $codigosTiposJornadas[]=array(
                    'codigo' => $jornada->id,
                    'glosa' => $jornada->nombre
                );
            }
    	}
    	return $codigosTiposJornadas;
    }
    
    public function comprobarDependencias()
    {
        $fichas = $this->fichas;        
        
        if($fichas->count()){
            $errores = new stdClass();
            $errores->error = array("El Tipo de Jornada <b>" . $this->nombre . "</b> se encuentra asignada.<br /> Debe <b>reasignar</b> los trabajadores primero para poder realizar esta acción.");
            return $errores;
        }
        
        return;
    }
    
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required',
            'numero_horas' => 'required'
        );

        $message = array(
            'jornada.required' => 'Obligatorio!'
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
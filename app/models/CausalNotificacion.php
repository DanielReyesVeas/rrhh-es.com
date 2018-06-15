<?php

class CausalNotificacion extends Eloquent {
    
    protected $table = 'causales_notificacion';
    
    public function finiquito(){
        return $this->hasMany('CartaNotificacion','causal_notificacion_id');
    }
    
    static function listaCausalesNotificacion(){
    	$listaCausalesNotificacion = array();
    	$causalesNotificacion = CausalNotificacion::orderBy('nombre', 'ASC')->get();
    	if( $causalesNotificacion->count() ){
            foreach( $causalesNotificacion as $causalNotificacion ){
                $listaCausalesNotificacion[]=array(
                    'id' => $causalNotificacion->id,
                    'sid' => $causalNotificacion->sid,
                    'codigo' => $causalNotificacion->codigo,
                    'articulo' => $causalNotificacion->articulo,
                    'nombre' => $causalNotificacion->nombre
                );
            }
    	}
    	return $listaCausalesNotificacion;
    }
    
    static function errores($datos){
         
        $rules = array(
            'codigo' => 'required',
            'articulo' => 'required',
            'nombre' => 'required'
        );

        $message = array(
            'causalNotificacion.required' => 'Obligatorio!'
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
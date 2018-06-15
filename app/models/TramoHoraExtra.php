<?php

class TramoHoraExtra extends Eloquent {
    
    protected $table = 'tramos_horas_extra';
    
    public function jornadas(){
        return $this->hasMany('Jornada', 'tramo_hora_extra_id')->orderBy('id', 'ASC');
    }
    
    public function jornadaTramo(){
        return $this->hasMany('Tramo', 'tramo_id');
    }
    
    static function listaTramosHorasExtra(){
    	$listaTramosHorasExtra = array();
    	$tramosHorasExtra = TramoHoraExtra::orderBy('jornada', 'ASC')->get();
    	if( $tramosHorasExtra->count() ){
            foreach( $tramosHorasExtra as $tramoHoraExtra ){
                $listaTramosHorasExtra[]=array(
                    'id' => $tramoHoraExtra->id,
                    'sid' => $tramoHoraExtra->sid,
                    'jornada' => $tramoHoraExtra->jornada,
                    'factor' => $tramoHoraExtra->factor
                );
            }
    	}
    	return $listaTramosHorasExtra;
    }
    
    static function errores($datos){
         
        $rules = array(
            'jornada' => 'required',
            'factor' => 'required'
        );

        $message = array(
            'tramoHoraExtra.required' => 'Obligatorio!'
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
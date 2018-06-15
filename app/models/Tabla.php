<?php

class Tabla extends Eloquent {
    
    protected $table = 'tipos_estructura';
    protected $connection = "principal";
    
    public function glosas(){
        return $this->hasMany('Glosa', 'tipo_estructura_id');
    }
    
    public function misGlosas(){
        $misGlosas = $this->glosas;
        $listaGlosas = array();
        if( $misGlosas->count() ){
            foreach($misGlosas as $glosa){
                $listaGlosas[] = array(
                    'id' => $glosa->id,
                    'nombre' => $glosa->glosa,
                    'codigos' => $glosa->codigos    
                );
            }
        }
        return $listaGlosas;
    }    
        
    static function errores($datos){
         
        $rules = array(
            'nombre' => 'required'
        );

        $message = array(
            'tabla.required' => 'Obligatorio!'
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
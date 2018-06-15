<?php
class Perfil extends Eloquent {
 
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'perfiles';
    protected $connection = "principal";
    
    public function detalles()
    {
        return $this->hasMany('PerfilDetalle', 'perfil_id');
    }
    
    public function usuarios(){
        return $this->hasMany('User', 'perfil_id');
    }
    
    static function listaPerfiles(){
        /*
        if( Auth::usuario()->user()->id == "1"){
        	$perfiles = Perfil::orderBy('perfil')->get();
        }else{
            $acceso = array( Auth::usuario()->user()->funcionario->departamento_id);
            $perfiles = Perfil::whereIn('departamento_id', $acceso )->orderBy('departamento_id')->orderBy('perfil')->get();
        }
        */
        $perfiles = Perfil::orderBy('perfil')->get();
        $listaPerfiles=array();
        if($perfiles->count()){
            foreach($perfiles as $item)
            {
                $listaPerfiles[]=array('sid' => $item->sid, 'perfil' => $item->perfil);
            }
        }
        return $listaPerfiles;
    }
    
    /*public function departamento(){
        $departamentos = Config::get('constants.departamentos');
        if( $this->departamento_id == 0 ){
            return "GENERICO";
        }else{
            if( array_key_exists($this->departamento_id, $departamentos) ){
                return $departamentos[ $this->departamento_id ];
            }
        }
        return "NO DEFINIDO";
    }*/
 
    static function errores($datos){
         
        $rules =    array(
                        'perfil' => 'required',
                        'descripcion' => 'required',
                        'seleccion' => 'required'
                    );
                    
        $message =  array(
                        'perfil.required' => 'Obligatorio!',
                        'descripcion.required' => 'Obligatorio!',
                        'seleccion.required' => 'Debe asignar al menos un Acceso al Perfil!'
                    );
                    


        $verifier = App::make('validation.presence');
        $verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            // la validacion tubo errores
            return $validation->messages();
        }else{
            // no hubo errores de validacion
            return false;
        }
    }
    
}
<?php

class Funcionario extends Eloquent {
    protected $table = 'funcionarios';
    protected $connection = "principal";

    public function listaRoles(){
        $lista = array();
        if( $this->es_vendedor ) $lista[]="Vendedor";
        if( $this->es_product_manager ) $lista[]="Product Manager";
        return $lista;
    }
	public function usuario(){
        return $this->hasOne('User', 'funcionario_id')->where('tipo', '1');;
    }
    public function departamento(){
        return $this->belongsTo('Departamento', 'departamento_id');
    }
    public function nombre_completo(){
        return $this->nombres." ".$this->paterno." ".$this->materno;
    }
    public function rut_formato(){
        return Funciones::formatear_rut($this->rut);
    }

    static function errores($datos){

        if($datos['id']){
            $rules =    array(
                'rut' => 'required|unique:funcionarios,rut,'.$datos['id'],
                'usuario' => 'required|min:5|unique:usuarios,username,'.$datos['usuario_id']
            );
        }else{

            $rules =    array(
                'rut' => 'required|unique:funcionarios,rut',
                'usuario' => 'required|min:5|unique:usuarios,username',
                'password' => 'required|min:4',
           //     'perfiles' => 'required'
            );
        }


        $message =  array(
            'rut.required' => 'Obligatorio!',
            'rut.unique' => 'Ya se encuentra registrado!',
            'usuario.required' => 'Obligatorio!',
            'usuario.min' => 'Mínimo 5 caracteres!',
            'usuario.unique' => 'Ya se encuentra registrado!',
            'password.required' => 'Obligatorio!',
            'password.min' => 'Mínimo 4 caracteres!',
            'perfiles.required' => 'Debe seleccionar un Perfil para el usuario!'
        );
        $verifier = App::make('validation.presence');
        $verifier->setConnection("principal");

        $validation = Validator::make($datos, $rules, $message);
        $validation->setPresenceVerifier($verifier);

        if($validation->fails()){
            // la validacion tubo errores
            return $validation->getMessageBag()->toArray();
        }else{
            // no hubo errores de validacion
            return false;
        }
    }
}
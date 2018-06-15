<?php

class Logs extends Eloquent {
    
    protected $table = 'logs';
    
    public function menuSistema(){
        return $this->belongsTo('MenuSistema','menu_id');
    }
    
    static function crearLog($menu, $id, $nombre, $accion, $idDato2=null, $nombreDato2=null, $submenu=null, $idDato3=null, $nombreDato3=null)
    {
        $user = Auth::usuario()->user();
        $seccionMenu = MenuSistema::where('href', $menu)->first();
        
        $log = new Logs();
        $log->menu_id = $seccionMenu['id'];
        $log->menu = $seccionMenu['menu'];
        $log->submenu = $submenu;
        $log->accion = $accion;
        $log->dato_id = $id;
        $log->dato_nombre = $nombre;
        $log->dato2_id = $idDato2;
        $log->dato2_nombre = $nombreDato2;
        $log->dato3_id = $idDato3;
        $log->dato3_nombre = $nombreDato3;
        $log->encargado_id = $user->id;
        $log->encargado = $user->nombreCompleto();
        $log->save();
            
        return;
    }
        
    static function errores($datos){
         
        $rules = array(
        );

        $message = array(
            'logs.required' => 'Obligatorio!'
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
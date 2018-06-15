<?php
use Symfony\Component\Console\Tests\Descriptor\ObjectsProvider;
class MenuSistema extends Eloquent {
 
    protected $table = 'menu';
    protected $connection = "principal";
    
    public function log(){
        return $this->hasMany('Logs','menu_id');
    }

    static function obtenerPermisosAccesosURL($user, $url){
        $accesos=array();
        if($url=='#empresas' || $url=='#tabla-global-mensual'){
            $abierto = true;
        }else{
            $abierto = AnioRemuneracion::isMesAbierto();
        }
        
        if(Auth::usuario()->user()->id > 1){
            $menuOpc = MenuSistema::where('href', $url)->get();
            if ($menuOpc->count()) {
                foreach($menuOpc as $menu){
                    if($user->perfil_id){
                        $usuarioMenu = PerfilDetalle::where('perfil_id', $user->perfil_id)->where('menu_id', $menu->id)->first();
                    }else{
                        $usuarioMenu = UsuarioPerfilDetalle::where('usuario_id', $user->id)->where('menu_id', $menu->id)->first();
                    }
                    if($usuarioMenu){
                        $accesos = array(
                            'ver' => $usuarioMenu->ver ? true : false,
                            'editar' => $usuarioMenu->editar ? true : false,
                            'crear' => $usuarioMenu->crear ? true : false,
                            'eliminar' => $usuarioMenu->eliminar ? true : false,
                            'abierto' => $abierto
                        );
                    }
                }
            }
        }else{
            $accesos=array(
                'ver' => true,
                'editar' => true,
                'crear' => true,
                'eliminar' => true,
                'abierto' => true
            );
        }
        return $accesos;
    }

    public function permisos(){
        return $this->hasMany('MenuSistemaPermiso', 'menu_id');
    }

    public function listaPermisos(){
        $lista=array();
        if( $this->permisos->count() ){
            foreach( $this->permisos as $permiso ){
                $lista[ $permiso->opcion ] = true;
            }
        }
        return $lista;
    }    
 
    public function padre()
    {
        return $this->belongsTo('MenuSistema', 'padre_id');
    }
 
    public function hijos()
    {
        return $this->hasMany('MenuSistema', 'padre_id')->orderBy('menu');
    }
    
    public function submenu()
    {
        $submenus = $this->hijos;
        $detalles = array();
        
        if($submenus->count()){
            $detalles[] = array('id' => 0, 'nombre' => 'TODOS', 'secciones' => array());
            foreach($submenus as $submenu){
                $detalles[] = array(
                    'id' => $submenu->id,
                    'sid' => $submenu->sid,
                    'nombre' => $submenu->menu,
                    'secciones' => $submenu->secciones()
                );
            }
        }
        
        return $detalles;   
    }
    
    public function secciones()
    {
        $secciones = array();
        
        switch($this->id){
            case 101:
                $secciones = array( 'TODOS', 'Liquidaciones Trabajadores');
                break;
            case 112:
                $secciones = array( 'TODOS', 'Trabajadores', 'Contratos Trabajador', 'Gestión Fichas', 'Gestión Planillas Contrato');
                break;
            case 119:
                $secciones = array( 'TODOS', 'Haberes Trabajadores', 'Ingreso Masivo');
                break;
            case 120:
                $secciones = array( 'TODOS', 'Descuentos Trabajadores', 'Ingreso Masivo');
                break;            
            case 126:
                $secciones = array( 'TODOS', 'Cargas Trabajadores', 'Cambiar Tramo');
                break;
            case 127:
                $secciones = array( 'TODOS', 'Cartas de Notificación Trabajadores', 'Plantillas Cartas de Notificación');
                break;
            case 128:
                $secciones = array( 'TODOS', 'Documentos Trabajadores', 'Tipos de Documento');
                break;
            case 136:
                $secciones = array( 'TODOS', 'Certificados Trabajadores', 'Plantillas Certificados');
                break;
        }
        
        return $secciones;
    }
    
    public function logs()
    {
        $logs = $this->log;
        $detalles = array();
        
        if($logs->count()){
            foreach($logs as $log){
                $detalles[] = array(
                    'id' => $log->id,
                    'accion' => $log->accion,
                    'menu' => array(
                        'id' => $log->menu_id,
                        'nombre' => $log->menu
                    ),
                    'submenu' => array(
                        'nombre' => $log->submenu
                    ),
                    'dato' => array(
                        'id' => $log->dato_id,
                        'nombre' => $log->dato
                    ),
                    'dato2' => array(
                        'id' => $log->dato2_id,
                        'nombre' => $log->dato2
                    ),
                    'dato3' => array(
                        'id' => $log->dato3_id,
                        'nombre' => $log->dato3
                    ),            
                    'encargado' => array(
                        'id' => $log->encargado_id,
                        'nombre' => $log->encargado
                    )
                );
            }
        }
        
        return $detalles;
    }
    
    static public function estructura_menu($padre=0){

        if($padre==0){
            $menu = MenuSistema::where('padre_id','=', $padre)->orderBy('posicion', 'ASC')->get();
        }else{
            $menu = MenuSistema::where('padre_id','=', $padre)->orderBy('menu', 'ASC')->get();            
        }
        
        
        $lista=array();
        if($menu->count()){
            foreach($menu as $item)
            {
                $lista[] = array(
                    'id' => $item->id,
                    'datos' => $item,
                    'hijos' => MenuSistema::estructura_menu($item->id)
                );
            }
        }
        return $lista;
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
    
    static public function reordenar($padre, $nuevo, $origen, $id){
        
        if($origen <= $nuevo){
            // desplazamiento hacia abajo
            DB::connection('principal')->table('menu')
            ->where('padre_id', $padre)
            ->where('posicion', '>', $origen)
            ->where('posicion', '<', $nuevo)
            ->where('id', '!=', $id)
            ->decrement('posicion');
        }else{
            // desplazamiento hacia arriba
            DB::connection('principal')->table('menu')
            ->where('padre_id', $padre)
            ->where('posicion', '>=', $nuevo)
            ->where('posicion', '<', $origen)
            ->where('id', '!=', $id)
            ->increment('posicion');
        }
    }
    
    static function errores($datos){
        $rules = array(
			'nombre' => 'required',
			'titulo' => 'required'
     	);

        $message =  array(
			'nombre.required' => 'Obligatorio!',
			'titulo.required' => 'Obligatorio!'
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
    
    static public function get_all_padres(){
    	$storoge =array();
        $menu = MenuSistema::where('tipo', 1)->orderBy('posicion', 'ASC')->get();
        if($menu->count()){
            foreach($menu as $item){
            	$storoge[] = array(
            		'id' => $item->id,
                    'nivel' => 0,
            		'value' => $item->menu
            	);
                $subMenu = MenuSistema::where('padre_id', $item->id)->where('tipo', 3)->orderBy('posicion', 'ASC')->get();
                if( $subMenu->count() ){
                    foreach( $subMenu as $sub ){
                        $storoge[] = array(
                            'id' => $sub->id,
                            'value' => $sub->menu,
                            'nivel' => 1
                        );
                    }
                }
            }
        }
        return $storoge;
    }
}
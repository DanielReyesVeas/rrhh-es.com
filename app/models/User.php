<?php
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
class User extends Eloquent implements UserInterface, RemindableInterface {
	protected $table = 'usuarios';
    protected $connection = "principal";
	protected $hidden = array('password');
 
	public function getAuthIdentifier(){
		return $this->getKey();
	}

	public function getAuthPassword(){
		return $this->password;
	}

	public function getRememberToken(){
		return $this->remember_token;
	}

	public function setRememberToken($value){
		$this->remember_token = $value;
	}

	public function getRememberTokenName(){
		return 'remember_token';
	}

	public function getReminderEmail(){
		return $this->email;
	}

    public function funcionario(){
        return $this->belongsTo('Funcionario', 'funcionario_id');
    }
    
    public function trabajador(){
        return $this->belongsTo('Trabajador', 'funcionario_id');
    }

    public function vendedor(){
        return $this->belongsTo('Vendedor', 'funcionario_id');
    }

    public function productManager(){
        return $this->belongsTo('ProductManager', 'funcionario_id');
    }

    public function firmas(){
        return $this->hasMany('UsuarioDoctoFirma', 'usuario_id');
    }
    
    public function usuarioEmpresa(){
        return $this->hasMany('UsuarioEmpresa', 'usuario_id');
    }
    
    public function isEmpresa()
    {
        $empresa = \Session::get('empresa');
        $empresas = $this->usuarioEmpresa;
        if($empresas->count()){
            foreach($empresas as $emp){
                if($emp->empresa_id==$empresa->id){
                    return true;
                }
            }
        }
        return false;
    }
    
    public function listaEmpresas()
    {
        $empresas = $this->usuarioEmpresa;
        $ids = array();
        $listaEmpresas = array();
        
        foreach($empresas as $empresa){
            $ids[] = $empresa->empresa_id;
        }
        
        $misEmpresas = Empresa::whereIn('id', $ids)->get();
            
        foreach( $misEmpresas as $empresa ){
            $listaEmpresas[]=array(
                'id' => $empresa->id,
                'empresa' => $empresa->razon_social,
                'rutFormato' => $empresa->rut_formato(),
                'rut' => $empresa->rut
            );
        }
        return $listaEmpresas;
    }
    
    public function misPermisos()
    {
        $datos = array();
        $empresa = \Session::get('empresa');
        if(Auth::usuario()->user()->perfil_id>0){
            $permisos = PerfilDetalle::where('perfil_id', Auth::usuario()->user()->perfil_id)->where('empresa_id', 100000)->get();
            if($permisos->count()){
                foreach($permisos as $permiso){
                    if($permiso->ver){
                        $datos[] = $permiso->menu_id;
                    }
                }
            }else{
               $permisos = PerfilDetalle::where('perfil_id', Auth::usuario()->user()->perfil_id)->where('empresa_id', $empresa->id)->get();
                if($permisos->count()){
                    foreach($permisos as $permiso){
                        if($permiso->ver){
                            $datos[] = $permiso->menu_id;
                        }
                    }
                } 
            }
        }else{                                
            $permisos = UsuarioPerfilDetalle::where('usuario_id', Auth::usuario()->user()->id)->where('empresa_id', 100000)->get();
            if($permisos->count()){
                foreach($permisos as $permiso){
                    if($permiso->ver){
                        $datos[] = $permiso->menu_id;
                    }
                }
            }else{
               $permisos = UsuarioPerfilDetalle::where('usuario_id', Auth::usuario()->user()->id)->where('empresa_id', $empresa->id)->get();
                if($permisos->count()){
                    foreach($permisos as $permiso){
                        if($permiso->ver){
                            $datos[] = $permiso->menu_id;
                        }
                    }
                } 
            }
        }
        
        return $datos;
    }
    
    public function nombreCompleto()
    {
        if($this->perfil_id==2){
            if($this->trabajador->ficha()){
                return $this->trabajador->ficha()->nombreCompleto();
            }
            return '';
        }
        return $this->funcionario->nombre_completo();
    }
    
    public function accesos()
    {
        $empresa = \Session::get('empresa');
        $usuarioEmpresa = UsuarioEmpresa::where('usuario_id', $this->id)->where('empresa_id', $empresa->id)->first();
        $opciones = MenuSistema::where('administrador', 4)->where('id', '<>', 145)->get();
        $accesos = $this->comprobarAccesos($usuarioEmpresa, $opciones);
        
        return $accesos;
    }
    
    public function isActivo()
    {
        $empresa = \Session::get('empresa');
        $usuarioEmpresa = UsuarioEmpresa::where('usuario_id', $this->id)->where('empresa_id', $empresa->id)->first();
        $activo = false;
        if($usuarioEmpresa->count()){
            $activo = $usuarioEmpresa->activo ? true : false;
        }
        
        return $activo;
    }
    
    public function comprobarAccesos($usuarioEmpresa, $opciones)
    {
        $documentosEmpresa = false;    
        $liquidaciones = false;    
        $cartas = false;    
        $certificados = false;    
        $solicitudes = false; 
        
        if($usuarioEmpresa->count()){
            $documentosEmpresa = $usuarioEmpresa['documentos_empresa'] ? true : false;
            $liquidaciones = $usuarioEmpresa['liquidaciones'] ? true : false;
            $cartas = $usuarioEmpresa['cartas_notificacion'] ? true : false;
            $certificados = $usuarioEmpresa['certificados'] ? true : false;
            $solicitudes = $usuarioEmpresa['solicitudes'] ? true : false;
        }
        
        $opciones[0]->check = $documentosEmpresa;
        $opciones[1]->check = $liquidaciones;
        $opciones[2]->check = $cartas;
        $opciones[3]->check = $certificados;
        $opciones[4]->check = $solicitudes;
        
        return $opciones;
    }

    public function doctosFirmas(){
        $lista=array();
        if( $this->firmas->count() ){
            foreach( $this->firmas as $firma ){
                $lista[ $firma->documento ]=true;
            }
            return $lista;
        }else{
            return "";
        }

    }

    public function identificacion(){
        if( $this->tipo == '1' ){
            return $this->belongsTo('Funcionario', 'funcionario_id');
        }elseif( $this->tipo == '2'){
            return $this->belongsTo('Vendedor', 'funcionario_id');
        }elseif( $this->tipo == '3'){
            return $this->belongsTo('ProductManager', 'funcionario_id');
        }
    }

    public function perfil(){
        return $this->belongsTo('Perfil', 'perfil_id');
    }

    public function perfilDetalle(){
        return $this->hasMany('UsuarioPerfilDetalle', 'usuario_id');
    }

    public function perfilDetallePrimer(){
        return $this->hasOne('UsuarioPerfilDetalle', 'usuario_id');
    }
    public function listaEmpresasPerfil(){
        if( !$this->perfil_id ) {
            $lista = array();
            if ($this->perfilDetalle->count()) {
                foreach ($this->perfilDetalle as $detalle) {
                    if (!in_array($detalle->empresa_id, $lista)) {
                        $lista[] = $detalle->empresa_id;
                    }
                }
            }
        }else{
            $lista = array();
            if ($this->perfil->detalles->count()) {
                foreach ($this->perfil->detalles as $detalle) {
                    if (!in_array($detalle->empresa_id, $lista)) {
                        $lista[] = $detalle->empresa_id;
                    }
                }
            }
        }
        return $lista;
    }
    public function listaAccesosEmpresa($empresa_id){
        $opcionesMenu=array();
        
        if( !$this->perfil_id ) {
            
            if( $this->perfilDetalle()->where('empresa_id', 100000)->count() ){
                $accesos = $this->perfilDetalle()->where('empresa_id', 100000)->get();
            }else {
                $accesos = $this->perfilDetalle()->where('empresa_id', $empresa_id)->get();
            }
        }else{
            
            if( $this->perfil->detalles()->where('empresa_id', 100000)->count() ) {
                $accesos = $this->perfil->detalles()->where('empresa_id', 100000)->get();
            }else{
                $accesos = $this->perfil->detalles()->where('empresa_id', $empresa_id)->get();
            }
        }
        foreach( $accesos as $datosPerfilMenu ){
            
            if( $datosPerfilMenu->menu ){
                $opcionesMenu[]=$datosPerfilMenu->menu_id;
                if( !array_key_exists( $datosPerfilMenu->menu->padre_id, $opcionesMenu) ){
                    $opcionesMenu[]=$datosPerfilMenu->menu->padre_id;
                }
            }
        }
        return $opcionesMenu;
    }

    public function comprobarUrlMenuEmpresa($url, $empresa_id){
        $acceso = $this->perfilDetallePrimer()->where('empresa_id', $empresa_id)->whereHas('menu',
            function($q) use($url){
                $q->where('href', $url);
            }
        )->first();
        return $acceso? true : false;
    }
}

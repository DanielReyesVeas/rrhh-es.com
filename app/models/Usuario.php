<?php
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
class Usuario extends Eloquent implements UserInterface, RemindableInterface {
	protected $table = 'usuarios';
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

    public function trabajador(){
        return $this->belongsTo('Trabajador', 'funcionario_id');
    }
    
    public function permisos(){
        return $this->hasOne('Permiso', 'usuario_id');
    }
    
    static function logTrabajador()
    {
        return false;
    }
    
    public function nombreCompleto()
    {
        $ficha = $this->trabajador->ultimaFicha();
        if($ficha){
            return $ficha->nombreCompleto();
        }
        return '';
    }
    
    public function accesos()
    {
        $opciones = MenuSistema::where('administrador', 4)->where('id', '<>', 145)->get();
        $accesos = $this->comprobarAccesos($opciones);
        
        return $accesos;
    }
  
    public function arrayAccesos()
    {
        $opciones = MenuSistema::where('administrador', 4)->where('id', '<>', 145)->get();
        $accesos = $this->comprobarAccesos($opciones);
        $texto = "";
        if(count($accesos)){
            foreach($accesos as $acceso){
                if($acceso->check){
                    $texto = $texto . "<br />-" . str_replace("Mis ", "", $acceso->menu);
                }
            }
        }
        
        return $texto;
    }
    
    public function generarClave()
    {
        $clave = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        return $clave;
    }
    
    public function enviarClave($correo, $nuevo, $clave=null)
    {
        $empresa = \Session::get('empresa');
        $accesos = $this->arrayAccesos();
        $url = $empresa->url();
        if(!$clave){
            $clave = $this->generarClave();
        }
        
        $ficha = $this->trabajador->ficha();
        
        if($nuevo){
            $subject = "Acceso a Portal Trabajadores " . $empresa->razon_social;
            $user = array(
                'nombre' => $ficha->nombreCompleto(),
                'usuario' => $this->username,
                'clave' => $clave,
                'correo' => $correo,
                'empresa' => $empresa->razon_social,
                'rutEmpresa' => $empresa->rut,
                'accesos' => $accesos,
                'url' => $url,
                'img' => public_path() . "/images/esystems-rrhh.png",
                'img2' => public_path() . "/images/logo-correo.png",
                'titulo' => "Bienvenido al Portal de Trabajadores <div style='text-align:center;'>" . $empresa->razon_social . "</div>",
                'mensaje1' => "<p class='mensaje'><strong>" . $ficha->nombreCompleto() . "</strong>, se ha activado su usuario para ingresar al portal de Remuneraciones de " . $empresa->razon_social . ".</p>",
                'mensaje2' => "<p class='mensaje'>En el Portal podrás acceder a la siguiente información: <br />" . $accesos . "</p>",
                'mensaje3' => "<p class='mensaje'>Tu datos de acceso son los siguientes: <br /><br />Usuario: <strong>" . $this->username . "</strong><br / >Contraseña: <strong>" . $clave . "</strong></p>",
                'mensaje4' => "<p class='mensaje'>Para acceder al Portal de Trabajadores haz click en el siguiente enlace:<div style='text-align:center;'><a href='" . $url . "' style='font-weight:normal;text-decoration:underline' target='_blank' >" . $url . "</a></div></p>"						
            );
        }else{
            $subject = "Reestablecimiento Clave Acceso Portal Trabajadores " . $empresa->razon_social;
            $user = array(
                'nombre' => $ficha->nombreCompleto(),
                'usuario' => $this->username,
                'clave' => $clave,
                'correo' => $correo,
                'empresa' => $empresa->razon_social,
                'accesos' => $accesos,
                'url' => $url,
                'rutEmpresa' => $empresa->rut,
                'img' => public_path() . "/images/esystems-rrhh.png",
                'img2' => public_path() . "/images/logo-correo.png",
                'titulo' => "<p>Reestablecimiento Acceso al Portal de Trabajadores </p><p><div style='text-align:center;'>" . $empresa->razon_social . "</div></p>",								
                'mensaje1' => "<p class='mensaje'><strong>" . $ficha->nombreCompleto() . "</strong>, se ha reestablecido su contraseña para ingresar al portal de Remuneraciones de " . $empresa->razon_social . ".</p>",
                'mensaje2' => "",
                'mensaje3' => "<p class='mensaje'>Tu nuevos datos de acceso son los siguientes: <br /><br />Usuario: <strong>" . $this->username . "</strong><br / >Contraseña: <strong>" . $clave . "</strong></p>",
                'mensaje4' => "<p class='mensaje'>Recuerda que puedes acceder al Portal de Trabajadores a través del siguiente enlace:<div style='text-align:center;'><a href='" . $url . "' rel='nofollow' style='font-weight:normal;text-decoration:underline' target='_blank'>" . $url . "</a></div></p>"		
            );
        }
                
        Mail::send('correo_clave_usuario', $user, function($message) use($empresa, $correo, $subject)
        {
            $message->to($correo);
            $message->from('no-reply@easysystems.cl', $empresa->razon_social);
            $message->replyTo('no-reply@easysystems.cl', $empresa->razon_social);
            $message->subject($subject);
        });
        $now = DB::raw('NOW()');
        DB::table('emails')->insert(array(
            array( 'trabajador_id' => $ficha->trabajador_id, 'email' => $correo, 'clave' => $clave, 'created_at' => $now, 'updated_at' => $now)
        ));
        
        
        return $clave;
    }
    
    public function comprobarAccesos($opciones)
    {
        $documentosEmpresa = false;    
        $liquidaciones = false;    
        $cartas = false;    
        $certificados = false;    
        $solicitudes = false; 
        $permisos = $this->permisos;
        
        $documentosEmpresa = $permisos['documentos_empresa'] ? true : false;
        $liquidaciones = $permisos['liquidaciones'] ? true : false;
        $cartas = $permisos['cartas_notificacion'] ? true : false;
        $certificados = $permisos['certificados'] ? true : false;
        $solicitudes = $permisos['solicitudes'] ? true : false;
        
        $opciones[0]->check = $documentosEmpresa;
        $opciones[1]->check = $liquidaciones;
        $opciones[2]->check = $cartas;
        $opciones[3]->check = $certificados;
        $opciones[4]->check = $solicitudes;
        
        return $opciones;
    }
    
    static function todosAccesos()
    {
        $opciones = MenuSistema::where('administrador', 4)->where('id', '<>', 145)->get();
 
        $opciones[0]->check = true;
        $opciones[1]->check = true;
        $opciones[2]->check = true;
        $opciones[3]->check = true;
        $opciones[4]->check = true;
        
        return $opciones;
    }    
    
    public function misLiquidaciones($anioRemuneracion)
    {        
        $rango = Funciones::obtenerRangoFechas($anioRemuneracion->anio);
        $liquidaciones = Liquidacion::where('trabajador_id', $this->trabajador->id)->where('mes', '>=', $rango['desde'])->where('mes', '<=', $rango['hasta'])->get();
        $listaLiquidaciones = array();
        
        if($liquidaciones->count()){
            foreach($liquidaciones as $liquidacion){
                if($liquidacion->mes<='2018-07-01' && $liquidacion->mes>='2018-02-01'){
                    $listaLiquidaciones[]=array(
                        'id' => $liquidacion->trabajador_id,
                        'sid' => $liquidacion->sid,
                        'mes' => $liquidacion->mes,
                        'periodo' => Funciones::obtenerMesAnioTexto($liquidacion->mes),
                        'sidDocumento' => $liquidacion->documento->sid,
                        'sidTrabajador' => $liquidacion->trabajador->sid,
                        'rutFormato' => $liquidacion->trabajador->rut_formato(),
                        'apellidos' => $liquidacion->trabajador_apellidos,
                        'nombreCompleto' => $liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos,
                        'cargo' => $liquidacion->trabajador_cargo,              
                        'sueldoBasePesos' => $liquidacion->sueldo_base,
                        'sueldoLiquido' => $liquidacion->sueldo_liquido,
                        'documento' => $liquidacion->documento
                    );
                }
            }
        }        
        
        $listaLiquidaciones = Funciones::ordenar($listaLiquidaciones, 'mes');
        
        return $listaLiquidaciones;
    }
    
    public function misCartasNotificacion()
    {        
        $cartasNotificacion = $this->trabajador->cartasNotificacion;
        $listaCartasNotificacion = array();
        
        if($cartasNotificacion->count()){
            foreach($cartasNotificacion as $cartaNotificacion){
                $listaCartasNotificacion[]=array(
                    'id' => $cartaNotificacion->trabajador_id,
                    'sid' => $cartaNotificacion->sid,
                    'motivo' => $cartaNotificacion->plantillaCartaNotificacion->nombre,
                    'sidDocumento' => $cartaNotificacion->documento->sid,
                    'nombreCompleto' => $cartaNotificacion->trabajador_nombre_completo,
                    'fecha' => $cartaNotificacion->fecha,
                    'documento' => $cartaNotificacion->documento
                );
            }
        }        
        
        $listaCartasNotificacion = Funciones::ordenar($listaCartasNotificacion, 'fecha');
        
        return $listaCartasNotificacion;
    }
    
    public function misCertificados()
    {        
        $certificados = $this->trabajador->certificados;
        $listaCertificados = array();
        
        if($certificados->count()){
            foreach($certificados as $certificado){
                $listaCertificados[]=array(
                    'id' => $certificado->trabajador_id,
                    'sid' => $certificado->sid,
                    'motivo' => $certificado->plantillaCertificado->nombre,
                    'sidDocumento' => $certificado->documento->sid,
                    'nombreCompleto' => $certificado->trabajador_nombre_completo,
                    'fecha' => $certificado->fecha,
                    'documento' => $certificado->documento
                );
            }
        }        
        
        $listaCertificados = Funciones::ordenar($listaCertificados, 'fecha');
        
        return $listaCertificados;
    }
  

}

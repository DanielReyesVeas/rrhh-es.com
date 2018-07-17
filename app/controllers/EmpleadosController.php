<?php
class EmpleadosController extends BaseController {

    public function buscar_rut($rut){
        $datos=array();
        $rut = Funciones::quitar_formato_rut($rut);
        $funcionario = Funcionario::where('rut', $rut)->first();
        if($funcionario){
            $datos = array(
                'id' => $funcionario->id,
                'sid' => $funcionario->usuario->sid,
                'rut' => $funcionario->rut_formato(),
                'nombre_completo' => $funcionario->nombre_completo(),
                //'departamento_id' => $funcionario->departamento->id,
                //'departamento' => $funcionario->departamento->obtenerArbolDependenciaAscendente($funcionario->departamento->departamento)
            );
            $datos['success']=true;
        }else{
            $datos['success']=false;
            $datos['mensaje']="No se encontro el Rut ingresado!";
        }
        return Response::json($datos);
    }
    
    public function crearUsuario()
    {
        /*
        $user = new Usuario();
        $user->sid = Funciones::generarSID();
        $user->funcionario_id = $empleado->id;
        $user->username = $empleado->rut;
        $pass = substr($empleado->rut, -4);
        $user->password = Hash::make($pass);
        $user->activo = 0;
        $user->save();

        $permiso = new Permiso();
        $permiso->usuario_id = $user->id;
        $permiso->documentos_empresa = 1;
        $permiso->cartas_notificacion = 1;
        $permiso->certificados = 1;
        $permiso->liquidaciones = 1;
        $permiso->solicitudes = 1;
        $permiso->save();*/
    }
	
    public function index() {
        
       	$usuarios = Usuario::all();
	  	$permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#empleados');
	    $todosActivos = true;
	    $todosNuevos = true;
	    $todosInactivos = true;
        
        $lista=array();
        if( $usuarios->count() ){
        	foreach ($usuarios as $usuario ){
                if($usuario->trabajador){
                    $empleado = $usuario->trabajador->ultimaFicha();
                    if($empleado){
                        $lista[] = array(
                            'id' => $usuario->id,
                            'sid' => $usuario->sid,
                            'rut' => $usuario->trabajador->rut,
                            'rutFormato' => $usuario->trabajador->rut_formato(),
                            'username' => $usuario->username,
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'estado' => $empleado->estado,
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'email' => $empleado->email,
                            'activo' => ($usuario->activo==1) ? true : false
                        );
                        if($todosActivos){
                            if($usuario->activo!=1){
                                $todosActivos = false;
                            }
                        }
                        if($todosNuevos){
                            if($usuario->activo!=2){
                                $todosNuevos = false;
                            }
                        }
                        if($todosInactivos){
                            if($usuario->activo==1){
                                $todosInactivos = false;
                            }
                        }
                    }
                }
        	}
        }
        
        $lista = Funciones::ordenar($lista, 'apellidos');
        
        $datos = array(
            'datos' => $lista,
		  	'accesos' => $permisos,
            'todosActivos' => $todosActivos,
            'todosNuevos' => $todosNuevos,
            'todosInactivos' => $todosInactivos
        );
        
        return Response::json($datos);
    }

    
    public function cambiarPermisos()
    {
        $datos = Input::all();
        $usuario = Usuario::find($datos['id']);
        $permisos = $usuario->permisos;
        $permisos->documentos_empresa = $datos['accesos'][0]['check'];
        $permisos->liquidaciones = $datos['accesos'][1]['check'];
        $permisos->cartas_notificacion = $datos['accesos'][2]['check'];
        $permisos->certificados = $datos['accesos'][3]['check'];
        $permisos->solicitudes = $datos['accesos'][4]['check'];
        $permisos->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $usuario->sid
        );
        
        return Response::json($respuesta);
    }
    
    public function cambiarPermisosMasivo()
    {
        $datos = Input::all();
        foreach($datos['usuarios'] as $dato){
            $usuario = Usuario::find($dato['id']);
            $permisos = $usuario->permisos;
            $permisos->documentos_empresa = $datos['accesos'][0]['check'];
            $permisos->liquidaciones = $datos['accesos'][1]['check'];
            $permisos->cartas_notificacion = $datos['accesos'][2]['check'];
            $permisos->certificados = $datos['accesos'][3]['check'];
            $permisos->solicitudes = $datos['accesos'][4]['check'];
            $permisos->save();
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function activarUsuario()
    {
        $datos = Input::all();
        $usuario = Usuario::find($datos['id']);
        $usuario->activo = true;
        
        $permisos = $usuario->permisos;
        $permisos->documentos_empresa = $datos['accesos'][0]['check'];
        $permisos->liquidaciones = $datos['accesos'][1]['check'];
        $permisos->cartas_notificacion = $datos['accesos'][2]['check'];
        $permisos->certificados = $datos['accesos'][3]['check'];
        $permisos->solicitudes = $datos['accesos'][4]['check'];
        
        $clave = $usuario->generarClave();
        
        $usuario->password = Hash::make($clave);
        $usuario->enviarClave($datos['email'], true, $clave);
        $usuario->save();
        $permisos->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "El Usuario <b>" . $usuario->username . "</b> fue activado, la clave fue enviada al correo <b>" . $datos['email'] . "</b>.",
            'sid' => $usuario->sid
        ); 
        
        return Response::json($respuesta);
    }
    
    public function activarMasivo()
    {        
        $datos = Input::all();

        foreach($datos['usuarios'] as $dato){
            $usuario = Usuario::find($dato['id']);
            $usuario->activo = true;
            $ficha = $usuario->trabajador->ultimaFicha();
            
            $permisos = $usuario->permisos;
            $permisos->documentos_empresa = $datos['accesos'][0]['check'];
            $permisos->liquidaciones = $datos['accesos'][1]['check'];
            $permisos->cartas_notificacion = $datos['accesos'][2]['check'];
            $permisos->certificados = $datos['accesos'][3]['check'];
            $permisos->solicitudes = $datos['accesos'][4]['check'];

            $clave = $usuario->generarClave();

            $usuario->password = Hash::make($clave);
            $usuario->enviarClave($ficha->email, true, $clave);
            $usuario->save();
            $permisos->save();
        }

        $respuesta = array(
            'success' => true,
            'mensaje' => "Los Usuarios fueron activados, las claves fueron enviadas a sus respectivos correos."
        ); 
        
        return Response::json($respuesta);
    }
	
    public function generarClave()
    {
        $datos = Input::all();
        $usuario = Usuario::find($datos['id']);        
        $clave = $usuario->generarClave();        
        $usuario->password = Hash::make($clave);
        $usuario->enviarClave($datos['email'], false, $clave);
        $usuario->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La clave del Usuario <b>" . $usuario->username . "</b> fue generada y enviada al correo <b>" . $datos['email'] . "</b>.",
            'sid' => $usuario->sid
        ); 
        
        return Response::json($respuesta);
    }
    
    public function generarClaveMasivo()
    {
        $datos = Input::all();
        foreach($datos as $dato){
            $usuario = Usuario::find($dato['id']);    
            $ficha = $usuario->trabajador->ultimaFicha();
            $clave = $usuario->generarClave();        
            $usuario->password = Hash::make($clave);
            $usuario->enviarClave($ficha->email, false, $clave);
            $usuario->save();
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "Las claves fueron generadas y enviadas a los correos respectivos de los Usuarios."
        ); 
        
        return Response::json($respuesta);
    }
    
    public function actualizar_password()
    {
        $registerData = array(
            'actual'                =>  Input::get('actual'),
            'password'              =>  Input::get('nueva'),
            'password_confirmation' =>  Input::get('repNueva')
        );

        $rules = array(
            'actual'                  => 'required',
            'password'              => 'required|confirmed|min:4'
        );
        
        $password_act = Input::get('actual');

        $messages = array(
            'actual.required'         => "El campo <b>Contraseña Actual</b> es Obligatorio.",
            'min'                   => 'El campo <b>Nueva Contraseña</b> no puede tener menos de :min carácteres.',
            'confirmed'             => 'La <b>Nueva Contraseña</b> y la <b>Repetición de la Nueva Contraseña</b>  no coinciden.'
        );

        $validation = Validator::make($registerData, $rules, $messages);
        if ($validation->fails())
        {
            return Response::json(array(
                'success' => false,
                'mensaje' => "Error en la información ingresada!"
            ));
        }
        else
        {
            if (Hash::check($password_act, Auth::empleado()->user()->password))
            {

                $user = Usuario::find(Auth::empleado()->user()->id);
                $user->password = Hash::make($registerData['password']);
                $user->save();

                return Response::json(array(
                    'success' => true,
                    'mensaje' => "La Contraseña fue actualizada correctamente"
                ));
            }
            else
            {

                return Response::json(array(
                    'success' => false,
                    'mensaje' =>"La Contraseña Actual ingresada es incorrecta!"
                ));
            }
        }
    }
		
    public function reactivarUsuario()
    {
        $datos = Input::all();
        
        $usuario = Usuario::find($datos['id']);
        $usuario->activo = true;
        
        $permisos = $usuario->permisos;
        $permisos->documentos_empresa = $datos['accesos'][0]['check'];
        $permisos->liquidaciones = $datos['accesos'][1]['check'];
        $permisos->cartas_notificacion = $datos['accesos'][2]['check'];
        $permisos->certificados = $datos['accesos'][3]['check'];
        $permisos->solicitudes = $datos['accesos'][4]['check'];
        $usuario->save();
        $permisos->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "El Usuario <b>" . $usuario->username . "</b> fue activado exitósamente.",
            'sid' => $usuario->sid
        );
        
        return Response::json($respuesta);
    }
    
    public function update($sid)
    {
        $datos = Input::all();
        $usuario = Usuario::whereSid($sid)->first();
        $usuario->activo = $datos['activo'];
        $permisos = $usuario->permisos;
        $permisos->documentos_empresa = 1;
        $permisos->liquidaciones = 1;
        $permisos->cartas_notificacion = 1;
        $permisos->certificados = 1;
        $permisos->solicitudes = 1;
        $permisos->save();
        $usuario->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $usuario->sid
        );
        
        return Response::json($respuesta);
    }
    
    public function desactivarMasivo()
    {
        $datos = Input::all();
        foreach($datos as $dato){
            $usuario = Usuario::whereSid($dato['sid'])->first();
            $usuario->activo = false;
            $permisos = $usuario->permisos;
            $permisos->documentos_empresa = 1;
            $permisos->liquidaciones = 1;
            $permisos->cartas_notificacion = 1;
            $permisos->certificados = 1;
            $permisos->solicitudes = 1;
            $permisos->save();
            $usuario->save();
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function show($sid)
    {
        if($sid){
            $usuario = Usuario::whereSid($sid)->first();
            $empleado = $usuario->trabajador->ultimaFicha();

            $datosUsuario = array(
                'id' => $usuario->id,
                'sid' => $usuario->sid,
                'username' => $usuario->username,
                'rut' => $usuario->trabajador->rut_formato(),
                'nombreCompleto' => $empleado->nombreCompleto(),
                'email' => $empleado->email,
                'accesos' => $usuario->accesos(),
                'activo' => ($usuario->activo==1) ? true : false,
                'nuevo' => ($usuario->activo==2) ? true : false
            );
        }else{
            $datosUsuario = array(
                'id' => 0,
                'sid' => 0,
                'username' => "",
                'rut' => "",
                'nombreCompleto' => "",
                'email' => "",
                'accesos' => Usuario::todosAccesos(),
                'activo' => false,
                'nuevo' => false
            );
        }
        
        $datos = array(
            'datos' => $datosUsuario
        );
        
        return Response::json($datos);        
    }
        
    public function destroy($id) {
        $mensaje = "La Información fue eliminada correctamente";
        Usuario::whereSid($id)->delete();
        return Response::json( array('success' => true, 'mensaje' => $mensaje) );
    }
        
}

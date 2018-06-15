<?php
class UsuariosController extends BaseController {
    public function ajax_buscador_todos_usuarios(){
        $termino = Input::get('termino');

        $usuarios = User::where(function($q) use($termino) {
            $q->whereHas('funcionario', function ($q1) use ($termino) {
                    $q1->where('nombres', 'LIKE', '%' . $termino . '%')
                        ->orWhere('paterno', 'LIKE', '%' . $termino . '%')
                        ->orWhere('materno', 'LIKE', '%' . $termino . '%')
                        ->orWhere('rut', 'LIKE', '%' . $termino . '%');
                });
        })->where('id', '>', '1')->get();

        $lista=array();
        if( $usuarios->count() ){
            foreach( $usuarios as $user ){
                $item = $user->funcionario;
                if( $item ) {
                    $lista[] = array(
                        'id' => $user->id,
                        'rut' => $item->rut_formato(),
                        'nombreCompleto' => $item->nombre_completo(),
                        'tipo' => $user->tipo
                    );
                }
            }
        }
        return Response::json($lista);
    }

    public function ajax_buscador_usuarios(){
        $termino = Input::get('termino');
        $usuarios = Funcionario::where('id', '>', '1')->where( function($q) use($termino){
            $q->where('nombres', 'LIKE', '%'.$termino.'%')
                ->orWhere('paterno', 'LIKE', '%'.$termino.'%')
                ->orWhere('materno', 'LIKE', '%'.$termino.'%')
                ->orWhere('rut', 'LIKE', '%'.$termino.'%');
        })->orderBy('nombres', 'ASC')->get();

        $lista=array();
        if( $usuarios->count() ){
            foreach( $usuarios as $item ){
                $lista[]=array(
                    'id' => $item->usuario->id,
                    'rut' => $item->rut_formato(),
                    'nombre' => $item->nombre_completo()
                );
            }
        }
        return Response::json($lista);
    }


    public function ajax_buscador_vendedores(){
        $termino = Input::get('termino');
        $usuarios = Funcionario::where('id', '>', '1')->where( function($q) use($termino){
            $q->where('nombres', 'LIKE', '%'.$termino.'%')
                ->orWhere('paterno', 'LIKE', '%'.$termino.'%')
                ->orWhere('materno', 'LIKE', '%'.$termino.'%')
                ->orWhere('rut', 'LIKE', '%'.$termino.'%');
        })->where('es_vendedor', '1')->orderBy('nombres', 'ASC')->get();

        $lista=array();
        if( $usuarios->count() ){
            foreach( $usuarios as $item ){
                $lista[]=array(
                    'id' => $item->usuario->id,
                    'rut' => $item->rut_formato(),
                    'nombre' => $item->nombre_completo()
                );
            }
        }
        return Response::json($lista);
    }


    public function ajax_buscador_productManager(){
        $termino = Input::get('termino');
        $usuarios = Funcionario::where('id', '>', '1')->where( function($q) use($termino){
            $q->where('nombres', 'LIKE', '%'.$termino.'%')
                ->orWhere('paterno', 'LIKE', '%'.$termino.'%')
                ->orWhere('materno', 'LIKE', '%'.$termino.'%')
                ->orWhere('rut', 'LIKE', '%'.$termino.'%');
        })->where('es_product_manager', '1')->orderBy('nombres', 'ASC')->get();

        $lista=array();
        if( $usuarios->count() ){
            foreach( $usuarios as $item ){
                $lista[]=array(
                    'id' => $item->id,
                    'rut' => $item->rut_formato(),
                    'nombre' => $item->nombre_completo()
                );
            }
        }
        return Response::json($lista);
    }



    public function lista_vendedores(){
        $funcionarios = Funcionario::whereHas('usuario', function($query){
            $query->where('id', '>', '1');
        })->where('es_vendedor', '1')->orderBy('nombres')->orderBy('paterno')->orderBy('materno')->get();


        $lista=array();
        if( $funcionarios->count() ){
            foreach ($funcionarios as $funcionario ){
                //$depto = $funcionario->departamento;
                $lista[] = array(
                    'id' => $funcionario->usuario->id,
                    'sid' => $funcionario->usuario->sid,
                    'fotografia' => $funcionario->fotografia ? URL::to("stories/min_".$funcionario->fotografia) : "images/usuario.png",
                    'rut' => $funcionario->rut_formato(),
                    'funcionario' => $funcionario->nombre_completo(),
                    'email' => $funcionario->email,
                    //'departamento' =>$depto?( $depto->departamento.( $depto->obtenerDependencia ? " ".$depto->obtenerArbolDependencia('') : '') ) : "",
                    'estado' => $funcionario->usuario->estado == 1 ? "Activo" : "Bloqueado",
                    'estado_id' => $funcionario->usuario->estado,
                    'roles' => $funcionario->listaRoles()
                );
            }
        }
        return Response::json($lista);
    }

    public function lista_product_manager(){
        $funcionarios = Funcionario::whereHas('usuario', function($query){
            $query->where('id', '>', '1');
        })->where('es_product_manager', '1')->orderBy('nombres')->orderBy('paterno')->orderBy('materno')->get();


        $lista=array();
        if( $funcionarios->count() ){
            foreach ($funcionarios as $funcionario ){
                //$depto = $funcionario->departamento;
                $lista[] = array(
                    'id' => $funcionario->usuario->id,
                    'sid' => $funcionario->usuario->sid,
                    'fotografia' => $funcionario->fotografia ? URL::to("stories/min_".$funcionario->fotografia) : "images/usuario.png",
                    'rut' => $funcionario->rut_formato(),
                    'funcionario' => $funcionario->nombre_completo(),
                    'email' => $funcionario->email,
                    //'departamento' =>$depto?( $depto->departamento.( $depto->obtenerDependencia ? " ".$depto->obtenerArbolDependencia('') : '') ) : "",
                    'estado' => $funcionario->usuario->estado == 1 ? "Activo" : "Bloqueado",
                    'estado_id' => $funcionario->usuario->estado,
                    'roles' => $funcionario->listaRoles()
                );
            }
        }
        return Response::json($lista);
    }



    public function exportar_excel($version){
        $baseDatos = \Session::get('basedatos');
        $empresa = Empresa::where('base_datos', $baseDatos)->first();
        if($version== 2007) $extension = "xlsx";
        else $extension = "xls";
        if( $empresa ){
            $datos['empresa'] = $empresa->razon_social;
            $datos['usuarios'] = array();

            // super administrador
            $funcionarios = Funcionario::whereHas('usuario', function($query){
                $query->where('id', '>', '1');
            })->orderBy('nombres')->orderBy('paterno')->orderBy('materno')->get();

            if( $funcionarios->count() ){
                foreach ($funcionarios as $funcionario ){
                    //$depto = $funcionario->departamento;
                    $datos['usuarios'][] = array(
                        'fotografia' => $funcionario->fotografia ? URL::to("stories/min_".$funcionario->fotografia) : "images/usuario.png",
                        'rut' => $funcionario->rut_formato(),
                        'funcionario' => $funcionario->nombre_completo(),
                        'email' => $funcionario->email,
                        //'departamento' => $depto? ( $depto->departamento.( $depto->obtenerDependencia ? " ".$depto->obtenerArbolDependencia('') : '') ) : ""
                    );
                }
            }

            Excel::create('Funcionarios', function($excel) use($datos) {
                $excel->sheet('Funcionarios', function($sheet) use($datos) {
                    $sheet->loadView('excel.funcionarios')->with('datos', $datos);


                    $filaFinal = count($datos['usuarios'])? count($datos['usuarios'])+3 : 4;

                    $sheet->getStyle('B4:B'.$filaFinal)->getAlignment()->setWrapText(true);

                    $sheet->setWidth(array(
                        'A'     =>  15,
                        'B'     =>  60,
                        'C'     =>  40,
                        'D'     =>  50
                    ));
                });
            })->download( $extension );
        }
    }

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
    
	public function objetosFormulario(){
		$datos=array(
			//'dependencias' => Departamento::listaDepartamentos(),
			'perfiles' => array_merge(  array(array('sid' => '0', 'perfil' => "Personalizado")) , Perfil::listaPerfiles() ),
            'perfilesExistentes' => Perfil::listaPerfiles()
		);
		return Response::json($datos);
	}
	
    public function index() {
        
        // super administrador
        $funcionarios = Funcionario::whereHas('usuario', function($query){
            $query->where('id', '>', '1');
        })->orderBy('nombres')->orderBy('paterno')->orderBy('materno')->get();
            

        $lista=array();
        if( $funcionarios->count() ){
        	foreach ($funcionarios as $funcionario ){
        		//$depto = $funcionario->departamento;
        		$lista[] = array(
        			'sid' => $funcionario->usuario->sid,
                    'fotografia' => $funcionario->fotografia ? URL::to("stories/min_".$funcionario->fotografia) : "images/usuario.png",
                    'rut' => $funcionario->rut,
                    'rutFormato' => $funcionario->rut_formato(),
        			'apellidos' => ucwords(strtolower($funcionario->paterno)) . ' ' . ucwords(strtolower($funcionario->materno)),
        			'funcionario' => $funcionario->nombre_completo(),
                    'email' => $funcionario->email,
        			//'departamento' =>$depto?( $depto->departamento.( $depto->obtenerDependencia ? " ".$depto->obtenerArbolDependencia('') : '') ) : "",
        			'estado' => $funcionario->usuario->estado == 1 ? "Activo" : "Bloqueado",
        			'estado_id' => $funcionario->usuario->estado,
                    'username' => $funcionario->usuario->username,
                    'perfil' => array(
                        'id' => $funcionario->usuario->perfil ? $funcionario->usuario->perfil->id : 0,
                        'nombre' => $funcionario->usuario->perfil ? $funcionario->usuario->perfil->perfil : 'Personalizado'
                    )
        		);
        	}
        }
        return Response::json($lista);
    }
    
    public function almacenarPerfil($permisos, $usuario_id){
        $perfilRetorno = 0;
        if( is_array( $permisos ) ){
            if( array_key_exists('perfil', $permisos) ) {
                if ($permisos['perfil']['sid'] == "0" or $permisos['modificarAcceso']) {
                    // perfil personalizado
                    if (is_array($permisos['seleccion'])) {

                        if (array_key_exists('global', $permisos['seleccion']) and $permisos['empresa']['nRut'] == "global" ) {

                            if (count($permisos['seleccion']['global']) > 0) {
                                // configuracion global

                                if (array_key_exists('id', $permisos['guardarPerfil']) ) {
                                    if ($permisos['guardarPerfil']['id'] == "0") {

                                        if( is_array($permisos['seleccion']['global']) ) {

                                            if (count($permisos['seleccion']['global'])) {

                                                foreach ($permisos['seleccion']['global'] as $opcMenu => $matrizPer) {
                                                    $menu = MenuSistema::where('sid', $opcMenu)->first();
                                                    if ($menu) {
                                                        $perfilDetalle = new UsuarioPerfilDetalle();
                                                        $perfilDetalle->tipo_acceso = 1;
                                                        $perfilDetalle->usuario_id = $usuario_id;
                                                        $perfilDetalle->empresa_id = 100000;
                                                        $perfilDetalle->menu_id = $menu->id;

                                                        if (is_array($matrizPer)) {
                                                            if (count($matrizPer) > 0) {
                                                                foreach ($matrizPer as $permiso => $valor) {
                                                                    $perfilDetalle->$permiso = 1;
                                                                }
                                                            }
                                                        }

                                                        // segun los permisos de acceso
                                                        $perfilDetalle->save();
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($permisos['guardarPerfil']['id'] == "1") {
                                        // modificar perfil existente
                                        $perfil = Perfil::where('sid', $permisos['modificarPerfil']['sid'])->first();
                                        if ($perfil) {
                                            PerfilDetalle::where('perfil_id', $perfil->id)->delete();
                                            foreach ($permisos['seleccion']['global'] as $opcMenu => $matrizPer) {
                                                $menu = MenuSistema::where('sid', $opcMenu)->first();
                                                if ($menu) {
                                                    $perfilDetalle = new PerfilDetalle();
                                                    $perfilDetalle->tipo_acceso = 1;
                                                    $perfilDetalle->perfil_id = $perfil->id;
                                                    $perfilDetalle->menu_id = $menu->id;
                                                    $perfilDetalle->empresa_id = 100000;

                                                    if (is_array($matrizPer)) {
                                                        if (count($matrizPer) > 0) {
                                                            foreach ($matrizPer as $permiso => $valor) {
                                                                $perfilDetalle->$permiso = 1;
                                                            }
                                                        }
                                                    }

                                                    $perfilDetalle->save();
                                                }
                                            }
                                            $perfilRetorno = $perfil->id;
                                        }

                                    } elseif ($permisos['guardarPerfil']['id'] == "2") {
                                        // nuevo perfil
                                        $perfil = new Perfil();
                                        $perfil->sid = Funciones::generarSID();
                                        $perfil->perfil = $permisos['nombrePerfil'];
                                        $perfil->save();
                                        foreach ($permisos['seleccion']['global'] as $opcMenu => $matrizPer) {
                                            $menu = MenuSistema::where('sid', $opcMenu)->first();
                                            if ($menu) {
                                                $perfilDetalle = new PerfilDetalle();
                                                $perfilDetalle->tipo_acceso = 1;
                                                $perfilDetalle->perfil_id = $perfil->id;
                                                $perfilDetalle->menu_id = $menu->id;
                                                $perfilDetalle->empresa_id = 100000;

                                                if (is_array($matrizPer)) {
                                                    if (count($matrizPer) > 0) {
                                                        foreach ($matrizPer as $permiso => $valor) {
                                                            $perfilDetalle->$permiso = 1;
                                                        }
                                                    }
                                                }

                                                $perfilDetalle->save();
                                            }
                                        }
                                        $perfilRetorno = $perfil->id;
                                    }
                                }

                            }


                        } else {
                            if ($permisos['guardarPerfil']['id'] == "0") {
                                foreach ($permisos['seleccion'] as $rutEmp => $arregloOpc) {
                                    $nRut = substr($rutEmp, 1, 20);
                                    if ($rutEmp != "global") {
                                        $empresa = Empresa::where('rut', $nRut)->first();
                                        $empresa_id = $empresa->id;
                                        if (is_array($arregloOpc)) {
                                            foreach ($arregloOpc as $opcMenu => $matrizPer) {
                                                $menu = MenuSistema::where('sid', $opcMenu)->first();
                                                $perfilDetalle = new UsuarioPerfilDetalle();
                                                $perfilDetalle->tipo_acceso = 1;
                                                $perfilDetalle->usuario_id = $usuario_id;
                                                $perfilDetalle->empresa_id = $empresa_id;
                                                $perfilDetalle->menu_id = $menu->id;
                                                if (is_array($matrizPer)) {
                                                    if (count($matrizPer) > 0) {
                                                        foreach ($matrizPer as $permiso => $valor) {
                                                            if ($valor == true) {
                                                                $perfilDetalle->$permiso = 1;
                                                            }
                                                        }
                                                    }
                                                }
                                                $perfilDetalle->save();
                                            }
                                        }
                                    }
                                }
                            } elseif ($permisos['guardarPerfil']['id'] == "1") {
                                // modificar perfil existente
                                $perfil = Perfil::where('sid', $permisos['modificarPerfil']['sid'])->first();
                                if ($perfil) {
                                    PerfilDetalle::where('perfil_id', $perfil->id)->delete();
                                    foreach ($permisos['seleccion'] as $rutEmp => $arregloOpc) {
                                        $nRut = substr($rutEmp, 1, 20);
                                        if ($rutEmp != "global") {
                                            $empresa = Empresa::where('rut', $nRut)->first();
                                            $empresa_id = $empresa->id;
                                            if (is_array($arregloOpc)) {
                                                foreach ($arregloOpc as $opcMenu => $matrizPer) {
                                                    $menu = MenuSistema::where('sid', $opcMenu)->first();
                                                    if ($menu) {
                                                        $perfilDetalle = new PerfilDetalle();
                                                        $perfilDetalle->tipo_acceso = 1;
                                                        $perfilDetalle->perfil_id = $perfil->id;
                                                        $perfilDetalle->menu_id = $menu->id;
                                                        $perfilDetalle->empresa_id = $empresa_id;
                                                        if (is_array($matrizPer)) {
                                                            if (count($matrizPer) > 0) {
                                                                foreach ($matrizPer as $permiso => $valor) {
                                                                    if ($valor == true) {
                                                                        $perfilDetalle->$permiso = 1;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        $perfilDetalle->save();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $perfilRetorno = $perfil->id;
                                }
                            } elseif ($permisos['guardarPerfil']['id'] == "2") {
                                // nuevo perfil

                                $perfil = new Perfil();
                                $perfil->perfil = $permisos['nombrePerfil'];
                                $perfil->sid = Funciones::generarSID();
                                $perfil->save();

                                foreach ($permisos['seleccion'] as $rutEmp => $arregloOpc) {
                                    $nRut = substr($rutEmp, 1, 20);
                                    if ($rutEmp != "global") {
                                        $empresa = Empresa::where('rut', $nRut)->first();
                                        $empresa_id = $empresa->id;
                                        if (is_array($arregloOpc)) {
                                            foreach ($arregloOpc as $opcMenu => $matrizPer) {
                                                $menu = MenuSistema::where('sid', $opcMenu)->first();
                                                if ($menu) {
                                                    $perfilDetalle = new PerfilDetalle();
                                                    $perfilDetalle->tipo_acceso = 1;
                                                    $perfilDetalle->perfil_id = $perfil->id;
                                                    $perfilDetalle->menu_id = $menu->id;
                                                    $perfilDetalle->empresa_id = $empresa_id;
                                                    if (is_array($matrizPer)) {
                                                        if (count($matrizPer) > 0) {
                                                            foreach ($matrizPer as $permiso => $valor) {
                                                                if ($valor == true) {
                                                                    $perfilDetalle->$permiso = 1;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $perfilDetalle->save();
                                                }
                                            }
                                        }
                                    }
                                }
                                $perfilRetorno = $perfil->id;
                            }
                        }
                    }
                } else {
                    // perfil seleccionado
                    $perfil = Perfil::whereSid($permisos['perfil']['sid'])->first();
                    if ($perfil) {
                        $perfilRetorno = $perfil->id;
                    }
                }
            }
        }
        return $perfilRetorno;
    }
    
    public function store() { 
        $datos = $this->get_datos_formulario();
        $datos['id']=0;
        $datos['usuario_id']=0;
        $errores_func = Funcionario::errores($datos);        
        if(!$errores_func ){
            $funcionario = new Funcionario();
            $funcionario->rut = $datos['rut'];
            $funcionario->sid = Funciones::generarSID();
            $funcionario->nombres = $datos['nombres'];
            $funcionario->paterno = $datos['paterno'];
            $funcionario->materno = $datos['materno'];
            $funcionario->telefono = $datos['telefono'];
            $funcionario->email = $datos['email'];

            if($datos['fotografia']){
                $nombreImagen = Funciones::subirImagenPerfil( public_path()."/stories", "user_".$datos['rut']."_".date("YmdHis") , $datos['fotografia'] );
                $funcionario->fotografia = $nombreImagen;
            }

            if($datos['firma']){
                $nombreImagen = Funciones::subirImagenPerfil( public_path()."/stories", "user_firma_".$datos['rut']."_".date("YmdHis") , $datos['firma'] );
                $funcionario->firma = $nombreImagen;
            }

            $funcionario->save();


            $user = new User();
            $user->sid = Funciones::generarSID();
            $user->tipo = 1;
            $user->username = $datos['usuario'];
            $user->password = Hash::make($datos['password']);
            $user->estado = 1;
            $user->funcionario_id = $funcionario->id;
            $user->save();

            // almacenar perfil
            $user->perfil_id = $this->almacenarPerfil( $datos['permisos'], $user->id );
            $user->save();

            

            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            		'sid' => $user->sid
            );
  		}else{
           	$respuesta = array(
          		'success' => false,
           		'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
           		'errores' => $errores_func
           	);
        }
        return Response::json($respuesta);
    }
    
    public function show($id){
        $datos=array();
        $user = User::whereSid($id)->first();
        if( $user ){
            $lista=array();
            $lista['global']="";
            // permisos
            if( $user->perfil_id == 0){
                // personalizado
                if( $user->perfilDetalle->count() ){
                    foreach( $user->perfilDetalle as $detalle ){
                        if( $detalle['empresa_id'] == 100000 ){
                            // global
                            if( !array_key_exists('global', $lista) ){
                                $lista['global']=array();
                            }
                            if( $detalle->menu ) {
                                $lista['global'][$detalle->menu->sid] = array(
                                    'crear' => $detalle->crear ? true : false,
                                    'editar' => $detalle->editar ? true : false,
                                    'eliminar' => $detalle->eliminar ? true : false,
                                    'ver' => $detalle->ver ? true : false
                                );
                            }
                        }else{
                            // por empresa
                            if( !array_key_exists('_'.$detalle->empresa->rut, $lista) ){
                                $lista[ '_'.$detalle->empresa->rut ]=array();
                            }
                            if( $detalle->empresa and $detalle->menu ) {
                                $lista['_' . $detalle->empresa->rut][$detalle->menu->sid] = array(
                                    'crear' => $detalle->crear ? true : false,
                                    'editar' => $detalle->editar ? true : false,
                                    'eliminar' => $detalle->eliminar ? true : false,
                                    'ver' => $detalle->ver ? true : false
                                );
                            }
                        }
                    }
                }
                $datosPerfil=array('sid' => '0', 'perfil' => "Personalizado");
            }else{
                // desde perfil
                if( $user->perfil->detalles->count() ){
                    foreach( $user->perfil->detalles as $detalle ){
                        if( $detalle['empresa_id'] == 100000 ){
                            // global
                            if( !array_key_exists('global', $lista) ){
                                $lista['global']=array();
                            }
                            if( $detalle->menu ) {
                                $lista['global'][$detalle->menu->sid] = array(
                                    'crear' => $detalle->crear ? true : false,
                                    'editar' => $detalle->editar ? true : false,
                                    'eliminar' => $detalle->eliminar ? true : false,
                                    'ver' => $detalle->ver ? true : false
                                );
                            }
                        }else{
                            // por empresa
                            if( !array_key_exists('_'.$detalle->empresa->rut, $lista) ){
                                $lista['_'.$detalle->empresa->rut]=array();
                            }
                            $lista['_'.$detalle->empresa->rut][ $detalle->menu->sid ]=array(
                                'crear' => $detalle->crear ? true : false,
                                'editar' => $detalle->editar ? true : false,
                                'eliminar' => $detalle->eliminar ? true : false,
                                'ver' => $detalle->ver ? true : false
                            );
                        }
                    }
                }
                $datosPerfil=array(
                    'sid' => $user->perfil->sid,
                    'perfil' => $user->perfil->perfil
                );
            }


        	$datos = array(
        		'sid' => $user->sid,
        		'rut' => $user->funcionario->rut_formato(),
        		'nombres' => $user->funcionario->nombres,
        		'paterno' => $user->funcionario->paterno,
        		'materno' => $user->funcionario->materno,
        		'telefono' => $user->funcionario->telefono,
        		'email' => $user->funcionario->email,
                'fotografia' => $user->funcionario->fotografia,

                'usuario' => $user->username,

                'permisos' => array(
                    'editar' => true,
                    'creando' => true,
                    'perfil' => $datosPerfil,
                    'seleccion' => $lista,
                    'guardarPerfil' => array(
                        'id' => 0, 'opcion' => 'No'
                    ),
                    'modificarPerfil' => array(
                        'sid' => 0,
                        'perfil' => ''
                    ),
                    'nuevoPerfil' => '',
                    'modificarAcceso' => false
                )
        	);
        }
        
        return Response::json($datos);        
    }
  
    public function update($id) { 
        
        $user = User::whereSid($id)->first();                
        $datos = $this->get_datos_formulario();
        $datos['id'] = $user->funcionario->id;
        $datos['usuario_id'] = $user->id;
        $errores_func = Funcionario::errores($datos);
        if(!$errores_func){
            // se almacena la informacion
            $user->username = $datos['usuario'];
            
            if( $datos['crear_password'] ){
            	// reescribiendo la password
            	$user->password = Hash::make($datos['password']);
            }
            $user->funcionario->rut = $datos['rut'];
            $user->funcionario->nombres = $datos['nombres'];
            $user->funcionario->paterno = $datos['paterno'];
            $user->funcionario->materno = $datos['materno'];
            $user->funcionario->telefono = $datos['telefono'];
            $user->funcionario->email = $datos['email'];


            if($datos['fotografia']){
                $nombreImagen = Funciones::subirImagenPerfil(public_path()."/stories", "user_".$datos['rut']."_".date("YmdHis") , $datos['fotografia']);
                if( $user->funcionario->fotografia ){
                    unlink('stories/'. $user->funcionario->fotografia );
                    unlink('stories/min_'. $user->funcionario->fotografia );
                }
                $user->funcionario->fotografia = $nombreImagen;
            }


            if($datos['firma']){
                $nombreImagen = Funciones::subirImagenPerfil(public_path()."/stories", "user_firma_".$datos['rut']."_".date("YmdHis") , $datos['firma']);
                if( $user->funcionario->firma ){
                    unlink('stories/'. $user->funcionario->firma );
                    unlink('stories/min_'. $user->funcionario->firma );
                }
                $user->funcionario->firma = $nombreImagen;
            }

            $user->funcionario->save();
            $user->save();

            // perfil del usuario
            UsuarioPerfilDetalle::where('usuario_id', '=', $user->id)->delete();

            // almacenar perfil
            $user->perfil_id = $this->almacenarPerfil( $datos['permisos'], $user->id );
            $user->save();

            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
            		'sid' => $user->sid
            );
		}else{
          	$respuesta = array(
         		'success' => false,
           		'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
           		'errores' => $errores_func
           	);
        }
        return Response::json($respuesta);
    }
    
    public function destroy($id) {
        $mensaje = "La Información fue eliminada correctamente";
        User::whereSid($id)->delete();
        return Response::json( array('success' => true, 'mensaje' => $mensaje) );
    }
    
    public function get_datos_formulario(){
        $datos=array(
        	'rut' => Funciones::quitar_formato_rut(Input::get('rut')),
            'nombres' => Input::get('nombres'),
            'paterno' => Input::get('paterno'),
            'materno' => Input::get('materno'),
            'fotografia' => Input::get('fotografiaBase64')? Input::get('fotografiaBase64') : "",
            'email' => Input::get('email'),
            'telefono' => Input::get('telefono'),
            'usuario' => Input::get('usuario'),
            'password' => Input::get('contrasena'),
            'password_confirmation' => Input::get('repetirContrasena'),
            'permisos' => Input::get('permisos'),
        	'crear_password' => Input::get('crearPassword'),
            'modificarAcceso' => Input::get('modificarAcceso'),
            'firma' => Input::get('firma_base64')? Input::get('firmaBase64') : ""
        );
        return $datos;
    }
}

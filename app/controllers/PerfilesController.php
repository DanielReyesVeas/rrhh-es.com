<?php
class PerfilesController extends BaseController {

    public function exportar_excel($version){
        $baseDatos = \Session::get('basedatos');
        $empresa = Empresa::where('base_datos', $baseDatos)->first();
        if($version== 2007) $extension = "xlsx";
        else $extension = "xls";
        if( $empresa ){
            $datos['empresa'] = $empresa->razon_social;
            $datos['perfiles'] = array();

            if( Auth::usuario()->user()->id == "1"){
                $perfiles = Perfil::orderBy('perfil')->get();
            }else{
                $acceso = array( Auth::usuario()->user()->funcionario->departamento_id );
                //$perfiles = Perfil::whereIn('departamento_id', $acceso )->orderBy('departamento_id')->orderBy('perfil')->get();
            }

            if( $perfiles->count() ){
                foreach ($perfiles as $item ){
                    $datos['perfiles'][] = array(
                        'perfil' => $item->perfil,
                        'descripcion' => $item->descripcion,
                        'asignaciones' => $item->usuarios->count()
                    );
                }
            }

            Excel::create('Perfiles de Usuario', function($excel) use($datos) {
                $excel->sheet('Perfiles de Usuario', function($sheet) use($datos) {
                    $sheet->loadView('excel.perfiles_de_usuario')->with('datos', $datos);

                    $filaFinal = count($datos['perfiles'])? count($datos['perfiles'])+3 : 4;

                    $sheet->getStyle('B4:B'.$filaFinal)->getAlignment()->setWrapText(true);

                    $sheet->setWidth(array(
                        'A'     =>  30,
                        'B'     =>  90,
                        'C'     =>  15
                    ));
                });
            })->download( $extension );
        }
    }

    public function objetosFormulario(){
		$datos=array(
		    'dependencias' => array()
		    //'dependencias' => Departamento::listaDepartamentos()
		);
		return Response::json($datos);
	}
	
    public function index() {

        $perfiles = Perfil::orderBy('perfil')->get();

        $listaPerfiles=array();
        if( $perfiles->count() ){
        	foreach ($perfiles as $item ){
        		$listaPerfiles[] = array(
        			'id' => $item->id,
        			'sid' => $item->sid,
        			'perfil' => $item->perfil,
        			'descripcion' => $item->descripcion,
        			'asignaciones' => $item->usuarios->count()
        		);
        	}
        }
        $datos=array(
            'datos' => $listaPerfiles,
            'opciones' => array(
                'dependencias' => array()
                //'dependencias' => Departamento::listaDepartamentos()
            )
        );
        
        return Response::json($datos);
    }
   
    
    public function store() { 
        $datos = $this->get_datos_formulario();
        $erroresPer = Perfil::errores($datos);  

        if(!$erroresPer){
            // se almacena la informacion
            $perfil = new Perfil();
            $perfil->sid = Funciones::generarSID();
            $perfil->perfil=$datos['perfil'];
            $perfil->descripcion = $datos['descripcion'];
            $perfil->save();
            $ingresoGlobal = false;
            if(is_array($datos['seleccion'])){
                foreach($datos['seleccion'] as $rutEmp => $subArray){
                    if( $rutEmp == "global" ){
                        foreach( $subArray as $opcMenu => $matrizPer ){
                            $menu = MenuSistema::whereSid($opcMenu)->first();
                            if( $menu ){
                                $perfilDetalle=new PerfilDetalle();
                                $perfilDetalle->tipo_acceso=1;
                                $perfilDetalle->perfil_id=$perfil->id;
                                $perfilDetalle->menu_id=$menu->id;
                                $perfilDetalle->empresa_id=100000;

                                if( is_array($matrizPer) ){
                                    if(count($matrizPer) > 0){
                                        foreach( $matrizPer as $permiso => $valor ){
                                            $perfilDetalle->$permiso = 1;
                                        }
                                    }
                                }

                                $perfilDetalle->save();
                            }
                            $ingresoGlobal = true;
                        }
                    }
                }
            }

            if(!$ingresoGlobal){
                if(is_array($datos['seleccion'])){
                    foreach($datos['seleccion'] as $rutEmp => $arregloEmpresa){
                        $nRut = substr($rutEmp,1, 20);
                        $empresa = Empresa::where('rut', $nRut)->first();
                        if( $empresa ){
                            $empresa_id = $empresa->id;
                            foreach( $arregloEmpresa as $opcMenu => $matrizPer){
                                $menu = MenuSistema::whereSid($opcMenu)->first();
                                if( $menu ){
                                    $perfilDetalle=new PerfilDetalle();
                                    $perfilDetalle->tipo_acceso=1;
                                    $perfilDetalle->perfil_id=$perfil->id;
                                    $perfilDetalle->menu_id=$menu->id;
                                    $perfilDetalle->empresa_id=$empresa_id;

                                    if( is_array($matrizPer) ){
                                        if(count($matrizPer) > 0){
                                            foreach( $matrizPer as $permiso => $valor ){
                                                $perfilDetalle->$permiso = 1;
                                            }
                                        }
                                    }

                                    $perfilDetalle->save();
                                }
                            }
                        }
                    }
                }
            }

            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            		'sid' => $perfil->sid
            );
        }else{
        	$respuesta = array(
        		'success' => false,
        		'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
        		'errores' => $erroresPer
        	);
        }
        return Response::json($respuesta);
    }
    
    public function show($id) { 
        $listaAccesos=array();
        $perfil = Perfil::whereSid($id)->first();       
        $perfilDetalle = PerfilDetalle::where('perfil_id', $perfil->id )->get();
        if($perfilDetalle->count()){
        	foreach ($perfilDetalle as $item){
                if( $item->empresa || $item->empresa_id == 100000 ){
                    if( $item->empresa_id != 100000) $index = "_".$item->empresa->rut;
                    else $index='global';
                    if( $item->menu ) {
                        $listaAccesos[$index][$item->menu->sid] = array(
                            'crear' => $item->crear ? true : false,
                            'editar' => $item->editar ? true : false,
                            'eliminar' => $item->eliminar ? true : false,
                            'ver' => $item->ver ? true : false
                        );
                    }
                }
        	}
        }
        
		$datos = array(
			'sid' => $perfil->sid,
			'perfil' => $perfil->perfil,
			'descripcion' => $perfil->descripcion,
			'seleccion' => $listaAccesos
		);
       
        return Response::json($datos);
    }
  
    public function update($id) {        
    	$datos = $this->get_datos_formulario();
    	$erroresPer = Perfil::errores($datos);
    	$perfil = Perfil::whereSid($id)->first();
    	if(!$erroresPer && $perfil ){
    		// se almacena la informacion
    		$perfil->perfil=$datos['perfil'];
    		$perfil->descripcion = $datos['descripcion'];
    		$perfil->save();
    		PerfilDetalle::where('perfil_id', $perfil->id)->delete();

            $ingresoGlobal = false;
            if(is_array($datos['seleccion'])){
                foreach($datos['seleccion'] as $rutEmp => $subArray){
                    if( $rutEmp == "global" ){
                        foreach( $subArray as $opcMenu => $matrizPer ){
                            $menu = MenuSistema::whereSid($opcMenu)->first();
                            if( $menu ){
                                $perfilDetalle=new PerfilDetalle();
                                $perfilDetalle->tipo_acceso=1;
                                $perfilDetalle->perfil_id=$perfil->id;
                                $perfilDetalle->menu_id=$menu->id;
                                $perfilDetalle->empresa_id=100000;

                                if( is_array($matrizPer) ){
                                    if(count($matrizPer) > 0){
                                        foreach( $matrizPer as $permiso => $valor ){
                                            $perfilDetalle->$permiso = 1;
                                        }
                                    }
                                }

                                $perfilDetalle->save();
                            }
                            $ingresoGlobal = true;
                        }
                    }
                }
            }

            if(!$ingresoGlobal){
                if(is_array($datos['seleccion'])){
                    foreach($datos['seleccion'] as $rutEmp => $arregloEmpresa){
                        $nRut = substr($rutEmp,1, 20);
                        $empresa = Empresa::where('rut', $nRut)->first();
                        if( $empresa ){
                            $empresa_id = $empresa->id;
                            foreach( $arregloEmpresa as $opcMenu => $matrizPer){
                                $menu = MenuSistema::whereSid($opcMenu)->first();
                                if( $menu ){
                                    $perfilDetalle=new PerfilDetalle();
                                    $perfilDetalle->tipo_acceso=1;
                                    $perfilDetalle->perfil_id=$perfil->id;
                                    $perfilDetalle->menu_id=$menu->id;
                                    $perfilDetalle->empresa_id=$empresa_id;

                                    if( is_array($matrizPer) ){
                                        if(count($matrizPer) > 0){
                                            foreach( $matrizPer as $permiso => $valor ){
                                                $perfilDetalle->$permiso = 1;
                                            }
                                        }
                                    }

                                    $perfilDetalle->save();
                                }
                            }
                        }
                    }
                }
            }


    		$respuesta = array(
    			'success' => true,
    			'mensaje' => "La Información fue almacenada correctamente",
    				'sid' => $perfil->sid
    		);
    	}else{
    		$respuesta = array(
    			'success' => false,
    			'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
    			'errores' => $erroresPer
    		);
    	}
    	return Response::json($respuesta);
    }
    
    public function destroy($sid) {
        $mensaje="El Perfil de Usuario fue eliminado correctamente";
        Perfil::where('sid', $sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'sid' => Input::get('sid'),
            'perfil' => Input::get('perfil'),
            'descripcion' => Input::get('descripcion'),
            'seleccion' => Input::get('seleccion')
        );
        return $datos;
    }
}

<?php

class DocumentosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#documentos');
        
        $documentos = Documento::all();
        $listaDocumentos=array();
        if( $documentos->count() ){
            foreach( $documentos as $documento ){
                $listaDocumentos[]=array(
                    'id' => $documento->id,
                    'sid' => $documento->sid,
                    'nombre' => $documento->nombre,
                    'alias' => $documento->alias,
                    'tipo' => array(
                        'id' => $documento->tipoDocumento->id,
                        'sid' => $documento->tipoDocumento->sid,
                        'nombre' => $documento->tipoDocumento->nombre
                    )
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaDocumentos
        );
        
        return Response::json($datos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $datos = $this->get_datos_formulario();
        $errores = Documento::errores($datos);      
        
        if(!$errores){
            $documento = new Documento();
            $documento->sid = Funciones::generarSID();
            $documento->trabajador_id = $datos['trabajador_id'];
            $documento->tipo_documento_id = $datos['tipo_documento_id'];
            $documento->nombre = $datos['nombre'];
            $documento->descripcion = $datos['descripcion'];
            $documento->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $documento->sid
            );
        }else{
            $respuesta=array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        } 
        return Response::json($respuesta);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#documentos');
        
        $documento = Documento::whereSid($sid)->first();
        $tiposDocumentos = TipoDocumento::listaTiposDocumento();
        
        if($documento){
            $datosDocumentos = array(
                'id' => $documento->id,
                'sid' => $documento->sid,
                'nombre' => $documento->nombre,
                'alias' => $documento->alias,
                'descripcion' => $documento->descripcion,
                'tipo' => array(
                    'id' => $documento->tipoDocumento->id,
                    'sid' => $documento->tipoDocumento->sid,
                    'nombre' => $documento->tipoDocumento->nombre
                )
            );
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $tiposDocumentos,
            'documento' => $datosDocumentos
        );
        
        return Response::json($datos);
    }
    
    public function importarDocumento()
    {        
        if(Input::hasFile('file')){            
            /*if(!empty($data) && $data->count()){
                foreach($data as $key => $value){
                    if(isset($value->rut) && isset($value->moneda) && isset($value->monto)){
                        $insert[] = array(
                            'rut' => $value->rut,              
                            'moneda' => $value->moneda,                 
                            'monto' => $value->monto,                 
                            'temporalidad' => $value->temporalidad               
                        );
                    }else{
                        $errores = array();
                        $errores[] = 'El formato no corresponde con el archivo de la planilla. Por favor vuelva a descargar la planilla.';
                    }
                }
            }*/
            
            //$filename = date("d-m-Y-H-i-s")."_".Funciones::elimina_acentos( $_FILES['file']['name'] );
            //$destination = public_path() . '/uploads/' . $filename;
            /*if(move_uploaded_file($_FILES['file']['tmp_name'], $destination)){
                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente",
                    'nombre' => $filename
                );
            }else{
                $respuesta=array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'nombre' => $filename
                );
            }*/
            
        }
        
        $name = $_FILES['file']['name'];
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente",
            'nombre' => $name
        );
            
        return Response::json($respuesta);
    }
    
    public function subirDocumento()
    {
        $datos = $this->get_datos_formulario();
        $idTrabajador = $datos['trabajador_id'];
        $idTipoDocumento = $datos['tipo_documento_id'];
        $descripcion = $datos['descripcion'];
        $menu = $datos['menu'];
        $submenu = $datos['submenu'];
        
        if(Input::hasFile('file')){            
            $name = $_FILES['file']['name'];
            $filename = date("d-m-Y-H-i-s")."_".Funciones::elimina_acentos($_FILES['file']['name']);
            $destination = public_path() . '/stories/' . $filename;
            if(move_uploaded_file($_FILES['file']['tmp_name'], $destination)){
                $documento = new Documento();
                $documento->sid = Funciones::generarSID();
                $documento->trabajador_id = $idTrabajador;
                $documento->tipo_documento_id = $idTipoDocumento;
                $documento->nombre = $filename;
                $documento->alias = $name;
                $documento->descripcion = $descripcion;
                $documento->save();
                
                Logs::crearLog($menu, $documento->id, $documento->alias, 'Import', $documento->trabajador_id, $documento->tipo_documento_id, $submenu);
                
                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente",
                    'nombre' => $name
                );
            }else{
                $respuesta=array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'nombre' => $name
                );
            }
        }
            
        return Response::json($respuesta);
    }
    
    public function documentoPDF($sid)
    {
		$documento = Documento::whereSid($sid)->first();

		if( $documento ){
			$destination = public_path() . '/stories/' . $documento->nombre;

			/*if( !file_exists($destination) ){
				$datos = $this->obtener_datos_cotizacion( $cotizacion->id );
				$html = View::make('cotizacion.cotizacion_pdf')->with('datos', $datos);
				File::put($pdfPath, PDF::load($html, 'letter', 'portrait')->output());
			}*/

			return Response::make(file_get_contents($destination), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$documento->nombre.'"'
            ]);
      }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($sid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($sid)
    {
        $documento = Documento::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $menu = $datos['menu'];
        $submenu = $datos['submenu'];
        $errores = Documento::errores($datos);       
        
        if(!$errores and $documento){
            $documento->tipo_documento_id = $datos['tipo_documento_id'];
            $documento->nombre = $datos['nombre'];
            $documento->descripcion = $datos['descripcion'];
            $documento->save();
            
            Logs::crearLog($menu, $documento->id, $documento->alias, 'Update', $documento->trabajador_id, $documento->tipo_documento_id, $submenu);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $documento->sid
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        } 
        return Response::json($respuesta);
    }
    
    public function eliminarDocumento()
    {
        $datos = Input::all();
        $sid = $datos['sid'];
        $documento = Documento::whereSid($sid)->first();
        $id = $documento['id'];
        $idTrabajador = $documento['trabajador_id'];
        $idTipoDocumento= $documento['tipo_documento_id'];
        $alias = $documento['alias'];
        $menu = $datos['menu'];
        $submenu = $datos['submenu'];

        if($documento->eliminarDocumento()){
            
            Logs::crearLog($menu, $id, $alias, 'Delete', $idTrabajador, $idTipoDocumento, $submenu);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue eliminada correctamente"
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => 'El archivo no existe'
            );
        }
        
        return Response::json($respuesta);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($sid)
    {
        $documento = Documento::whereSid($sid)->first();
        if($documento->eliminarDocumento()){
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue eliminada correctamente",
                'sid' => $documento->sid
            );
        }else{
            $respuesta = array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => 'El archivo no existe'
            );
        }
        
        return Response::json($respuesta);            
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'tipo_documento_id' => Input::get('idTipoDocumento'),
            'menu' => Input::get('menu'),
            'submenu' => Input::get('submenu'),
            'nombre' => Input::get('nombre'),
            'descripcion' => Input::get('descripcion')
        );
        return $datos;
    }

}
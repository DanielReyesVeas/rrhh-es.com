<?php

class DocumentosEmpresaController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#documentos-empresa');
        
        $documentos = DocumentoEmpresa::all();
        $listaDocumentos=array();
        if( $documentos->count() ){
            foreach( $documentos as $documento ){
                $listaDocumentos[]=array(
                    'id' => $documento->id,
                    'sid' => $documento->sid,
                    'nombre' => $documento->nombre,
                    'alias' => $documento->alias,
                    'descripcion' => $documento->descripcion,
                    'extension' => $documento->extension(),
                    'fecha' => $documento->created_at,
                    'publico' => $documento->publico ? true : false
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
        $errores = DocumentoEmpresa::errores($datos);      
        
        if(!$errores){
            $documento = new DocumentoEmpresa();
            $documento->sid = Funciones::generarSID();
            $documento->alias = $datos['alias'];
            $documento->nombre = $datos['nombre'];
            $documento->descripcion = $datos['descripcion'];
            $documento->publico = $datos['publico'];
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
    
    public function publicos()
    {
        $documentos = DocumentoEmpresa::all();
        $listaDocumentos=array();
        if( $documentos->count() ){
            foreach( $documentos as $documento ){
                if($documento->publico){
                    $listaDocumentos[]=array(
                        'id' => $documento->id,
                        'sid' => $documento->sid,
                        'nombre' => $documento->nombre,
                        'alias' => $documento->alias,
                        'descripcion' => $documento->descripcion,
                        'extension' => $documento->extension(),
                        'fecha' => $documento->created_at
                    );
                }
            }
        }        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaDocumentos
        );
        
        return Response::json($datos);    
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#documentos-empresa');
        
        $documento = DocumentoEmpresa::whereSid($sid)->first();
        
        if($documento){
            $datosDocumentos = array(
                'id' => $documento->id,
                'sid' => $documento->sid,
                'nombre' => $documento->nombre,
                'alias' => $documento->alias,
                'fecha' => $documento->created_at,
                'publico' => $documento->publico ? true : false,
                'descripcion' => $documento->descripcion,
            );
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosDocumentos
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
    
    public function documentoPDF($sid)
    {
        $name = DocumentoEmpresa::whereSid($sid)->first()['nombre'];
        
        $destination = public_path() . '/stories/empresa/' . $name;
      
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$name.'"'
        ]);      
		
    }
    
    public function subirDocumento()
    {
        $datos = Input::all();
        
        if(Input::hasFile('file')){            
            $name = $_FILES['file']['name'];
            $filename = date("d-m-Y-H-i-s")."_".Funciones::elimina_acentos($_FILES['file']['name']);
            $destination = public_path() . '/stories/empresa/' . $filename;
            if(move_uploaded_file($_FILES['file']['tmp_name'], $destination)){
                $documento = new DocumentoEmpresa();
                $documento->sid = Funciones::generarSID();
                $documento->nombre = $filename;
                $documento->alias = $name;
                $documento->publico = (boolean) $datos['publico'];
                $documento->descripcion = $datos['descripcion'];
                $documento->save();
                
                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente",
                    'nombre' => $name,
                    'a' => $datos
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
    
    public function descargarDocumento($sid)
    {
		$documento = DocumentoEmpresa::whereSid($sid)->first();
		if( $documento ){
			$destination = public_path() . '/stories/empresa/' . $documento->nombre;
            $formato = pathinfo($destination, PATHINFO_EXTENSION);

			return Response::make(file_get_contents($destination), 200, [
                'Content-Type' => 'application/'.$formato,
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
        $documento = DocumentoEmpresa::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = DocumentoEmpresa::errores($datos);       
        
        if(!$errores and $documento){
            $documento->alias = $datos['alias'];
            $documento->nombre = $datos['nombre'];
            $documento->descripcion = $datos['descripcion'];
            $documento->publico = $datos['publico'];
            $documento->save();
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($sid)
    {
        $documento = DocumentoEmpresa::whereSid($sid)->first();
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
            'nombre' => Input::get('nombre'),
            'alias' => Input::get('alias'),
            'publico' => Input::get('publico'),
            'descripcion' => Input::get('descripcion')
        );
        return $datos;
    }

}
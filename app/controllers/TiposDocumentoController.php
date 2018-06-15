<?php

class TiposDocumentoController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $tiposDocumento = TipoDocumento::all();
        $listaTiposDocumento=array();
        if( $tiposDocumento->count() ){
            foreach( $tiposDocumento as $tipoDocumento ){
                $listaTiposDocumento[]=array(
                    'id' => $tipoDocumento->id,
                    'sid' => $tipoDocumento->sid,
                    'nombre' => $tipoDocumento->nombre
                );
            }
        }
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaTiposDocumento
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
        $errores = TipoDocumento::errores($datos);      
        
        if(!$errores){
            $tipoDocumento = new TipoDocumento();
            $tipoDocumento->sid = Funciones::generarSID();
            $tipoDocumento->nombre = $datos['nombre'];
            $tipoDocumento->save();
            
            Logs::crearLog('#asociar-documentos', $tipoDocumento->id, $tipoDocumento->nombre, 'Create', NULL, NULL, 'Tipos de Documento');
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $tipoDocumento->sid
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
        $tipoDocumento = TipoDocumento::whereSid($sid)->first();

        $datosTipoDocumento=array(
            'id' => $tipoDocumento->id,
            'sid' => $tipoDocumento->sid,
            'nombre' => $tipoDocumento->nombre
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosTipoDocumento
        );
        
        return Response::json($datos);
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
        $tipoDocumento = TipoDocumento::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TipoDocumento::errores($datos);       
        
        if(!$errores and $tipoDocumento){
            $tipoDocumento->nombre = $datos['nombre'];
            $tipoDocumento->save();
            
            Logs::crearLog('#asociar-documentos', $tipoDocumento->id, $tipoDocumento->nombre, 'Update', NULL, NULL, 'Tipos de Documento');
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tipoDocumento->sid
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
        $mensaje="La Información fue eliminada correctamente";
        $tipoDocumento = TipoDocumento::whereSid($sid)->first();
        
        Logs::crearLog('#asociar-documentos', $tipoDocumento['id'], $tipoDocumento['nombre'], 'Delete', NULL, NULL, 'Tipos de Documento');
        
        $tipoDocumento->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre'),
            'jornada' => Input::get('jornada')
        );
        return $datos;
    }

}
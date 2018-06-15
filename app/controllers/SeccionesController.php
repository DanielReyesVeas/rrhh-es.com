<?php

class SeccionesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        /*
        $secciones = Seccion::orderBy('dependencia_id', 'ASC')->get();
        $listaSecciones=array();
        if( $secciones->count() ){
            foreach( $secciones as $seccion ){
                $listaSecciones[]=array(
                    'id' => $seccion->id,
                    'sid' => $seccion->sid,
                    'nombre' => $seccion->nombre,
                    'idDependencia' => $seccion->dependencia_id,
                    'nivel' => $seccion->nivel()
                );
            }
        }
        */
        if(!\Session::get('empresa')){
            return Response::json(array('secciones' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#organica');
        $listaSecciones=array();
        Seccion::listaSecciones($listaSecciones, 0, 1);
        
        $datos = array(
            'accesos' => $permisos,
            'secciones' => $listaSecciones
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
        $errores = Seccion::errores($datos);      
        
        if(!$errores){
            $seccion = new Seccion();
            $seccion->sid = Funciones::generarSID();
            $seccion->dependencia_id = $datos['dependencia_id'];
            //$seccion->encargado_id = $datos['encargado_id'];
            $seccion->nombre = $datos['nombre'];
            $seccion->codigo = $datos['codigo'];
            $seccion->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $seccion->sid
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
        $seccion = Seccion::whereSid($sid)->first();
        $seccion_id = $seccion->id;
        $listaSecciones=array();
        Seccion::listaSeccionesDependencia($listaSecciones, 0, 1, $seccion_id);

        $seccion = array(
            'id' => $seccion->id,
            'sid' => $seccion->sid,            
            'nombre' => $seccion->nombre,         
            'codigo' => $seccion->codigo,         
            'dependencia' => $seccion->dependencia(),
            //'encargado' => $seccion->encargado()
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $seccion,
            'secciones' => $listaSecciones
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
        $seccion = Seccion::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Seccion::errores($datos);       
        
        if($seccion->id==1){
            $idDependencia = 0;
        }else{
            $idDependencia = $datos['dependencia_id'];
        }
        
        if(!$errores and $seccion){
            $seccion->dependencia_id = $idDependencia;
            //$seccion->encargado_id = $datos['encargado_id'];
            $seccion->nombre = $datos['nombre'];
            $seccion->codigo = $datos['codigo'];
            $seccion->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $seccion->sid
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
        $seccion = Seccion::whereSid($sid)->first();
        
        $errores = $seccion->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#organica', $seccion->id, $seccion->nombre, 'Delete');       
            $seccion->delete();
            $datos = array(
                'success' => true,
                'mensaje' => "La Información fue eliminada correctamente"
            );
        }else{
            $datos = array(
                'success' => false,
                'errores' => $errores,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada"
            );
        }
        return Response::json($datos);
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'dependencia_id' => Input::get('dependencia')['id'],
            //'encargado_id' => Input::get('encargado')['id'],
            'nombre' => Input::get('nombre'),
            'codigo' => Input::get('codigo')
        );
        return $datos;
    }

}
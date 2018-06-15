<?php

class CentrosCostoController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#centro-costos');
        $listaCentrosCostos=array();
        CentroCosto::arbolCentrosCosto($listaCentrosCostos, 0, 1);
        
        $datos = array(
            'accesos' => $permisos,
            'centrosCostos' => $listaCentrosCostos
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
        $errores = CentroCosto::errores($datos);      
        
        if(!$errores){
            $centroCosto = new CentroCosto();
            $centroCosto->nombre = $datos['nombre'];
            $centroCosto->codigo = $datos['codigo'];
            $centroCosto->dependencia_id = $datos['dependencia'] ? $datos['dependencia']['id'] : 0;
            $centroCosto->sid = Funciones::generarSID();
            $centroCosto->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $centroCosto->id
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
        $centroCosto = CentroCosto::whereSid($sid)->first();
        $listaCentrosCosto=array();
        CentroCosto::listaCentrosCostoDependencia($listaCentrosCosto, 0, 1, $centroCosto->id);
        $detallesCentroCosto = array();

        $detallesCentroCosto = array(
            'id' => $centroCosto->id,
            'sid' => $centroCosto->sid,            
            'codigo' => $centroCosto->codigo,            
            'nivel' => $centroCosto->nivel(),            
            'nombre' => $centroCosto->nombre,         
            'dependencia' => $centroCosto->dependencia()
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $detallesCentroCosto,
            'centrosCostos' => $listaCentrosCosto
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
        $centroCosto = CentroCosto::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = CentroCosto::errores($datos);       
        
        if(!$errores and $centroCosto){
            $centroCosto->nombre = $datos['nombre'];
            $centroCosto->codigo = $datos['codigo'];
            $centroCosto->dependencia_id = $datos['dependencia'] ? $datos['dependencia']['id'] : 0;
            $centroCosto->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $centroCosto->sid
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
        $centroCosto = CentroCosto::whereSid($sid)->first();
        
        $errores = $centroCosto->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#centro-costos', $centroCosto->id, $centroCosto->nombre, 'Delete');       
            $centroCosto->delete();
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
            'codigo' => Input::get('codigo'),
            'dependencia' => Input::get('dependencia'),
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}
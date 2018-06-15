<?php

class TiposContratoController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tipos-contrato');
        $tiposContrato = TipoContrato::all();
        $listaTiposContrato=array();
        if( $tiposContrato->count() ){
            foreach( $tiposContrato as $tipoContrato ){
                $listaTiposContrato[]=array(
                    'id' => $tipoContrato->id,
                    'sid' => $tipoContrato->sid,
                    'nombre' => $tipoContrato->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTiposContrato
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
        $errores = TipoContrato::errores($datos);      
        
        if(!$errores){
            $tipoContrato = new TipoContrato();
            $tipoContrato->sid = Funciones::generarSID();
            $tipoContrato->nombre = $datos['nombre'];
            $tipoContrato->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $tipoContrato->sid
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
        //
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
        $tipoContrato = TipoContrato::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TipoContrato::errores($datos);       
        
        if(!$errores and $tipoContrato){
            $tipoContrato->nombre = $datos['nombre'];
            $tipoContrato->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tipoContrato->sid
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
        $contrato = TipoContrato::whereSid($sid)->first();
        
        $errores = $contrato->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#tipos-contrato', $contrato->id, $contrato->nombre, 'Delete');       
            $contrato->delete();
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
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}
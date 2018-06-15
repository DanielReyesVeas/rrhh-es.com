<?php

class TitulosController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#titulos');
        $titulos = Titulo::all();
        $listaTitulos=array();
        if( $titulos->count() ){
            foreach( $titulos as $titulo ){
                $listaTitulos[]=array(
                    'id' => $titulo->id,
                    'sid' => $titulo->sid,
                    'nombre' => $titulo->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTitulos
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
        $errores = Titulo::errores($datos);      
        
        if(!$errores){
            $titulo = new Titulo();
            $titulo->nombre = $datos['nombre'];
            $titulo->sid = Funciones::generarSID();
            $titulo->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $titulo->id
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
        $titulo = Titulo::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Titulo::errores($datos);       
        
        if(!$errores and $titulo){
            $titulo->nombre = $datos['nombre'];
            $titulo->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $titulo->sid
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
        $titulo = Titulo::whereSid($sid)->first();
        
        $errores = $titulo->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#titulos', $titulo->id, $titulo->nombre, 'Delete');       
            $titulo->delete();
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
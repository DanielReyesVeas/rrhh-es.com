<?php

class RecaudadoresController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#recaudadores');
        $recaudadores = Recaudador::all();
        $listaRecaudadores=array();
        if( $recaudadores->count() ){
            foreach( $recaudadores as $recaudador ){
                $listaRecaudadores[]=array(
                    'id' => $recaudador->id,
                    'nombre' => $recaudador->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaRecaudadores
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
        $errores = Recaudador::errores($datos);      
        
        if(!$errores){
            $recaudador = new Recaudador();
            $recaudador->nombre = $datos['nombre'];
            $recaudador->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $recaudador->id
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
        $recaudador = Recaudador::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Recaudador::errores($datos);       
        
        if(!$errores and $recaudador){
            $recaudador->nombre = $datos['nombre'];
            $recaudador->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $recaudador->sid
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
        Recaudador::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}
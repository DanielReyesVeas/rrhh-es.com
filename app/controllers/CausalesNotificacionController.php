<?php

class CausalesNotificacionController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#causales-notificacion');
        $causalesNotificacion = CausalNotificacion::all();
        $listaCausalesNotificacion=array();
        if( $causalesNotificacion->count() ){
            foreach( $causalesNotificacion as $causalNotificacion ){
                $listaCausalesNotificacion[]=array(
                    'id' => $causalNotificacion->id,
                    'sid' => $causalNotificacion->sid,
                    'codigo' => $causalNotificacion->codigo,
                    'articulo' => $causalNotificacion->articulo,
                    'nombre' => $causalNotificacion->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaCausalesNotificacion
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
        $errores = CausalNotificacion::errores($datos);      
        
        if(!$errores){
            $causalNotificacion = new CausalNotificacion();
            $causalNotificacion->sid = Funciones::generarSID();
            $causalNotificacion->codigo = $datos['codigo'];
            $causalNotificacion->articulo = $datos['articulo'];
            $causalNotificacion->nombre = $datos['nombre'];
            $causalNotificacion->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $causalNotificacion->sid
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
        $causalNotificacion = CausalNotificacion::whereSid($sid)->first();

        $datos=array(
            'id' => $causalNotificacion->id,
            'sid' => $causalNotificacion->sid,
            'codigo' => $causalNotificacion->codigo,
            'articulo' => $causalNotificacion->articulo,
            'nombre' => $causalNotificacion->nombre
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
        $causalNotificacion = CausalNotificacion::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = CausalNotificacion::errores($datos);       
        
        if(!$errores and $causalNotificacion){
            $causalNotificacion->nombre = $datos['nombre'];
            $causalNotificacion->codigo = $datos['codigo'];
            $causalNotificacion->articulo = $datos['articulo'];
            $causalNotificacion->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $causalNotificacion->sid
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
        CausalNotificacion::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'codigo' => Input::get('codigo'),
            'articulo' => Input::get('articulo'),
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}
<?php

class CausalesFiniquitoController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#causales-finiquito');
        $causalesFiniquito = CausalFiniquito::all();
        $listaCausalesFiniquito=array();
        if( $causalesFiniquito->count() ){
            foreach( $causalesFiniquito as $causalFiniquito ){
                $listaCausalesFiniquito[]=array(
                    'id' => $causalFiniquito->id,
                    'sid' => $causalFiniquito->sid,
                    'codigo' => $causalFiniquito->codigo,
                    'articulo' => $causalFiniquito->articulo,
                    'nombre' => $causalFiniquito->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaCausalesFiniquito
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
        $errores = CausalFiniquito::errores($datos);      
        
        if(!$errores){
            $causalFiniquito = new CausalFiniquito();
            $causalFiniquito->sid = Funciones::generarSID();
            $causalFiniquito->codigo = $datos['codigo'];
            $causalFiniquito->articulo = $datos['articulo'];
            $causalFiniquito->nombre = $datos['nombre'];
            $causalFiniquito->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $causalFiniquito->sid
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
        $causalFiniquito = CausalFiniquito::whereSid($sid)->first();

        $datos=array(
            'id' => $causalFiniquito->id,
            'sid' => $causalFiniquito->sid,
            'codigo' => $causalFiniquito->codigo,
            'articulo' => $causalFiniquito->articulo,
            'nombre' => $causalFiniquito->nombre
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
        $causalFiniquito = CausalFiniquito::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = CausalFiniquito::errores($datos);       
        
        if(!$errores and $causalFiniquito){
            $causalFiniquito->nombre = $datos['nombre'];
            $causalFiniquito->codigo = $datos['codigo'];
            $causalFiniquito->articulo = $datos['articulo'];
            $causalFiniquito->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $causalFiniquito->sid
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
        CausalFiniquito::whereSid($sid)->delete();
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
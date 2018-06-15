<?php

class EstadosCivilesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $estadosCiviles = EstadoCivil::all();
        $listaEstadosCiviles=array();
        if( $estadosCiviles->count() ){
            foreach( $estadosCiviles as $estadoCivil ){
                $listaEstadosCiviles[]=array(
                    'id' => $estadoCivil->id,
                    'sid' => $estadoCivil->sid,
                    'nombre' => $estadoCivil->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaEstadosCiviles
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
        $errores = EstadoCivil::errores($datos);      
        
        if(!$errores){
            $estadoCivil = new EstadoCivil();
            $estadoCivil->sid = Funciones::generarSID();
            $estadoCivil->nombre = $datos['nombre'];
            $estadoCivil->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $estadoCivil->sid
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
        $estadoCivil = EstadoCivil::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = EstadoCivil::errores($datos);       
        
        if(!$errores and $estadoCivil){
            $estadoCivil->nombre = $datos['nombre'];
            $estadoCivil->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $estadoCivil->sid
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
        EstadoCivil::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}
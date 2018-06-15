<?php

class GlosasController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function index()
    {
        
        $tablas = Glosa::all();
        
        $listaGlosas=array();
        if( $glosas->count() ){
            foreach( $glosas as $glosa ){
                $listaGlosas[]=array(
                    'id' => $glosa->id,
                    'nombre' => $glosa->glosa
                );
            }
        }
        
        $recaudadores = Recaudador::listaRecaudadores();
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaGlosas
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
        $errores = Glosa::errores($datos);      
        
        if(!$errores){
            $glosa = new Glosa();
            $glosa->tipo_estructura_id = $datos['tipo_estructura_id'];
            $glosa->glosa = $datos['glosa'];
            $glosa->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $glosa->id
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
        $glosa = Glosa::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Glosa::errores($datos);       
        
        if(!$errores and $glosa){
            $glosa->tipo_estructura_id = $datos['tipo_estructura_id'];
            $glosa->glosa = $datos['glosa'];
            $glosa->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $glosa->sid
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
        Glosa::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'glosa' => Input::get('glosa'),
            'tipo_estructura_id' => Input::get('tipo_estructura_id')
        );
        return $datos;
    }

}
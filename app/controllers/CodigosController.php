<?php

class CodigosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function index()
    {
        
        $tablas = Codigo::all();
        
        $listaCodigos=array();
        if( $codigos->count() ){
            foreach( $codigos as $codigo ){
                $listaCodigos[]=array(
                    'id' => $codigo->id,
                    'codigo' => $codigo->codigo
                );
            }
        }
                
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaCodigos
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
    
    public function ingresoMasivo()
    {
        $datos = Input::all();
    
        foreach($datos['datos'] as $cod){
            $errores = Codigo::errores($cod);   
            if(!$errores){
                $codigo = new Codigo();
                $codigo->glosa_id = $cod['glosa_id'];
                $codigo->recaudador_id = $cod['recaudador_id'];
                $codigo->codigo = $cod['codigo'];
                $codigo->save(); 
                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente"
                );
            }else{
                $respuesta=array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'errores' => $errores
                );
            }
        }
        
        return Response::json($respuesta);
    }
    
    public function updateMasivo()
    {
        
        $datos = Input::all();
        
        foreach($datos['datos'] as $cod){
            
            $errores = Codigo::errores($datos);       
            $codigo = Codigo::find($cod['id']);
            if(!$errores and $codigo){
                $codigo->glosa_id = $cod['glosa_id'];
                $codigo->recaudador_id = $cod['recaudador_id'];
                $codigo->codigo = $cod['codigo'];
                $codigo->save();
                $respuesta = array(
                    'success' => true,
                    'mensaje' => "La Información fue actualizada correctamente",
                    'id' => $codigo->id
                );
            }else{
                $respuesta = array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'errores' => $errores
                );
            } 
        }
        return Response::json($respuesta);
        
        
        
        $datos = Input::all();
    
        foreach($datos['datos'] as $cod){
            $errores = Codigo::errores($cod);   
            if(!$errores){
                $codigo = new Codigo();
                $codigo->glosa_id = $cod['glosa_id'];
                $codigo->recaudador_id = $cod['recaudador_id'];
                $codigo->codigo = $cod['codigo'];
                $codigo->save(); 
                $respuesta=array(
                    'success' => true,
                    'mensaje' => "La Información fue almacenada correctamente"
                );
            }else{
                $respuesta=array(
                    'success' => false,
                    'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                    'errores' => $errores
                );
            }
        }
        
        return Response::json($respuesta);
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
            $codigo = new Codigo();
            $codigo->glosa_id = $datos['glosa_id'];
            $codigo->recaudador_id = $datos['recaudador_id'];
            $codigo->codigo = $datos['codigo'];
            $codigo->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $codigo->id
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
        $codigo = Codigo::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Codigo::errores($datos);       
        
        if(!$errores and $codigo){
            $codigo->glosa_id = $datos['glosa_id'];
            $codigo->recaudador_id = $datos['recaudador_id'];
            $codigo->codigo = $datos['codigo'];
            $codigo->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $codigo->sid
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
        Codigo::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'glosa_id' => Input::get('glosa_id'),
            'codigo_id' => Input::get('codigo_id'),
            'codigo' => Input::get('codigo')
        );
        return $datos;
    }

}
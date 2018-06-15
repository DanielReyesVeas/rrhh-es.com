<?php

class TiendasController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tiendas');
        $tiendas = Tienda::all();
        $listaTiendas=array();
        if( $tiendas->count() ){
            foreach( $tiendas as $tienda ){
                $listaTiendas[]=array(
                    'id' => $tienda->id,
                    'sid' => $tienda->sid,
                    'codigo' => $tienda->codigo,
                    'nombre' => $tienda->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTiendas
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
        $errores = Tienda::errores($datos);      
        
        if(!$errores){
            $tienda = new Tienda();
            $tienda->nombre = $datos['nombre'];
            $tienda->codigo = $datos['codigo'];
            $tienda->sid = Funciones::generarSID();
            $tienda->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $tienda->id
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
        $tienda = Tienda::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Tienda::errores($datos);       
        
        if(!$errores and $tienda){
            $tienda->nombre = $datos['nombre'];
            $tienda->codigo = $datos['codigo'];
            $tienda->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tienda->sid
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
        $tienda = Tienda::whereSid($sid)->first();
        
        $errores = $tienda->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#tiendas', $tienda->id, $tienda->nombre, 'Delete');       
            $tienda->delete();
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
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}
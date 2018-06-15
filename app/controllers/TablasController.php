<?php

class TablasController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function index()
    {
        //$permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tablas');
        $permisos = array(
            'ver' => true,
            'crear' => false,
            'editar' => false,
            'eliminar' => false,
            'abierto' => false
        );
        $tablas = Tabla::all();
        
        $listaTablas=array();
        if( $tablas->count() ){
            foreach( $tablas as $tabla ){
                $listaTablas[]=array(
                    'id' => $tabla->id,
                    'numero' => $tabla->numero,
                    'nombre' => $tabla->nombre,
                    'glosas' => $tabla->misGlosas()
                );
            }
        }
        
        $recaudadores = Recaudador::listaRecaudadores();
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTablas,
            'recaudadores' => $recaudadores
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
        $errores = Tabla::errores($datos);      
        
        if(!$errores){
            $tabla = new Tabla();
            $tabla->nombre = $datos['nombre'];
            $tabla->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $tabla->id
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
    public function show($id)
    {
        $tabla = Tabla::find($id);
        $recaudadores = Recaudador::listaRecaudadores();
        
        $datosTabla=array(
            'id' => $tabla->id,
            'numero' => $tabla->numero,
            'nombre' => $tabla->nombre,
            'glosas' => $tabla->misGlosas()
        );
                
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosTabla,
            'recaudadores' => $recaudadores
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
        $tabla = Tabla::WhereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Tabla::errores($datos);       
        
        if(!$errores and $tabla){
            $tabla->nombre = $datos['nombre'];
            $tabla->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tabla->sid
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
        Tabla::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre')
        );
        return $datos;
    }

}
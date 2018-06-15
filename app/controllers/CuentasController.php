<?php

class CuentasController extends \BaseController {
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cuentas');
        $cuentas = Cuenta::all();
        $listaCuentas=array();
        if( $cuentas->count() ){
            foreach( $cuentas as $cuenta ){
                $listaCuentas[]=array(
                    'id' => $cuenta->id,
                    'sid' => $cuenta->sid,
                    'codigo' => $cuenta->codigo,
                    'comportamiento' => $cuenta->comportamiento,
                    'nombre' => $cuenta->nombre
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaCuentas
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
        $errores = Cuenta::errores($datos);      
        
        if(!$errores){
            $cuenta = new Cuenta();
            $cuenta->nombre = $datos['nombre'];
            $cuenta->comportamiento = $datos['comportamiento'];
            $cuenta->codigo = $datos['codigo'];
            $cuenta->sid = Funciones::generarSID();
            $cuenta->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'id' => $cuenta->id
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
        $cuenta = Cuenta::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Cuenta::errores($datos);       
        
        if(!$errores and $cuenta){
            $cuenta->nombre = $datos['nombre'];
            $cuenta->comportamiento = $datos['comportamiento'];
            $cuenta->codigo = $datos['codigo'];
            $cuenta->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $cuenta->sid
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
        $cuenta = Cuenta::whereSid($sid)->first();
        
        $errores = $cuenta->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#cuentas', $cuenta->id, $cuenta->nombre, 'Delete');       
            $cuenta->delete();
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
            'comportamiento' => Input::get('comportamiento')['id'],
            'codigo' => Input::get('codigo'),
            'nombre' => Input::get('nombre'),
            'id' => Input::get('id')
        );
        return $datos;
    }

}
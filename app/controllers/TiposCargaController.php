<?php

class TiposCargaController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'accesosTabla' => array(), 'accesosIngreso' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tipos-carga');
    
        $tiposCarga = TipoCarga::all();
        $listaTiposCarga=array();
        if( $tiposCarga->count() ){
            foreach( $tiposCarga as $tipoCarga ){
                $listaTiposCarga[]=array(
                    'id' => $tipoCarga->id,
                    'sid' => $tipoCarga->sid,
                    'nombre' => $tipoCarga->nombre,
                );
            }
        }
        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTiposCarga
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
        $errores = TipoCarga::errores($datos);      
        
        if(!$errores){
            $tipoCarga = new TipoCarga();
            $tipoCarga->sid = Funciones::generarSID();
            $tipoCarga->nombre = $datos['nombre'];
            $tipoCarga->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $tipoCarga->sid
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
        $tipoCarga = TipoCarga::whereSid($sid)->first();

        $datosCarga=array(
            'id' => $tipoCarga->id,
            'sid' => $tipoCarga->sid,
            'nombre' => $tipoCarga->nombre
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosCarga
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
        $tipoCarga = TipoCarga::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TipoCarga::errores($datos);       
        
        if(!$errores and $tipoCarga){
            $tipoCarga->nombre = $datos['nombre'];          
            $tipoCarga->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tipoCarga->sid
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
        $tipoCarga = TipoCarga::whereSid($sid)->first();
        $cargas = Carga::where('tipo_carga_id', $tipoCarga->id)->get();
        if($cargas->count()){
            $respuesta = array(
            	'success' => false,
            	'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => 'El tipo de Carga Familliar seleccionado posee datos que dependen de él. <br />Asegúrese que no existan dependencias sobre los datos que desea eliminar.'
            );
        }else{
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue eliminada correctamente",
                'sid' => $tipoCarga->sid
            );
            $tipoCarga->delete();
        }
        return Response::json($respuesta);
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'codigo' => Input::get('codigo'),
            'nombre' => Input::get('nombre'),
            'caja' => Input::get('caja'),
            'descripcion' => Input::get('descripcion')
        );
        return $datos;
    }

}
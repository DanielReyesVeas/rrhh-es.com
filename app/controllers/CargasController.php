<?php

class CargasController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $cargas = Carga::all();
        $listaCargas=array();
        if( $cargas->count() ){
            foreach( $cargas as $carga ){
                $listaCargas[]=array(
                    'id' => $carga->id,
                    'sid' => $carga->sid,
                    'idTrabajador' => $carga->trabajador_id,
                    'parentesco' => $carga->parentesco,
                    'nombreCompleto' => $carga->nombre_completo,
                    'tipoCarga' => array(
                        'id' => $carga->tipoCarga->id,
                        'nombre' => $carga->tipoCarga->nombre
                    ),
                    'fechaNacimiento' => $carga->fecha_nacimiento,
                    'sexo' => $carga->sexo,
                    'esCarga' => $carga->es_carga ? true : false,
                    'esAutorizada' => $carga->es_autorizada ? true : false
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaCargas
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
        $errores = Carga::errores($datos);      
        
        if(!$errores){
            $carga = new Carga();
            $carga->sid = Funciones::generarSID();
            $carga->trabajador_id = $datos['trabajador_id'];
            $carga->parentesco = $datos['parentesco'];
            $carga->es_carga = $datos['es_carga'];
            $carga->tipo_carga_id = $datos['tipo_carga_id'];
            $carga->rut = $datos['rut'];
            $carga->nombre_completo = $datos['nombre_completo'];
            $carga->fecha_nacimiento = $datos['fecha_nacimiento'];
            $carga->sexo = $datos['sexo'];
            $carga->fecha_autorizacion = $datos['fecha_autorizacion'];
            $carga->fecha_pago_desde = $datos['fecha_pago_desde'];
            $carga->fecha_pago_hasta = $datos['fecha_pago_hasta'];
            $carga->save();
            
            $trabajador = $carga->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#cargas-familiares', $carga->trabajador_id, $ficha->nombreCompleto(), 'Create', $carga->id, $carga->nombre_completo, 'Cargas Trabajadores');

            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $carga->sid
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cargas-familiares');
        $datosCarga = null;
        $trabajadores = array();
        
        if($sid){
            $carga = Carga::whereSid($sid)->first();
            $datosCarga=array(
                'id' => $carga->id,
                'sid' => $carga->sid,
                'idTrabajador' => $carga->trabajador_id,
                'rut' => $carga->rut,
                'parentesco' => $carga->parentesco,
                'nombreCompleto' => $carga->nombre_completo,
                'fechaNacimiento' => $carga->fecha_nacimiento,
                'fechaAutorizacion' => $carga->fecha_autorizacion,
                'fechaPagoDesde' => $carga->fecha_pago_desde,
                'fechaPagoHasta' => $carga->fecha_pago_hasta,
                'tipo' => array(
                    'id' => $carga->tipoCarga->id,
                    'nombre' => $carga->tipoCarga->nombre
                ),
                'sexo' => $carga->sexo,
                'esCarga' => $carga->es_carga ? true : false,
                'esAutorizada' => $carga->es_autorizada ? true : false,
                'trabajador' => $carga->trabajadorCarga()
            );
        }else{
            $trabajadores = Trabajador::activosFiniquitados();
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosCarga,
            'trabajadores' => $trabajadores
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
        $carga = Carga::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Carga::errores($datos);       
        
        if(!$errores and $carga){
            $carga->parentesco = $datos['parentesco'];
            $carga->es_carga = $datos['es_carga'];
            $carga->rut = $datos['rut'];
            $carga->nombre_completo = $datos['nombre_completo'];
            $carga->fecha_nacimiento = $datos['fecha_nacimiento'];
            $carga->fecha_autorizacion = $datos['fecha_autorizacion'];
            $carga->fecha_pago_desde = $datos['fecha_pago_desde'];
            $carga->fecha_pago_hasta = $datos['fecha_pago_hasta'];
            $carga->sexo = $datos['sexo'];
            $carga->tipo_carga_id = $datos['tipo_carga_id'];
            $carga->save();
            
            $trabajador = $carga->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#cargas-familiares', $carga->trabajador_id, $ficha->nombreCompleto(), 'Update', $carga->id, $carga->nombre_completo, 'Cargas Trabajadores');
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $carga->sid
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
        $carga = Carga::whereSid($sid)->first();
        
        $trabajador = $carga->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#cargas-familiares', $carga['trabajador_id'], $ficha->nombreCompleto(), 'Delete', $carga['id'], $carga['nombre_completo'], 'Cargas Trabajadores');
        
        $carga->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'parentesco' => Input::get('parentesco'),
            'es_carga' => Input::get('esCarga'),
            'tipo_carga_id' => Input::get('tipo'),
            'rut' => Input::get('rut'),
            'nombre_completo' => Input::get('nombreCompleto'),
            'fecha_nacimiento' => Input::get('fechaNacimiento'),
            'fecha_autorizacion' => Input::get('fechaAutorizacion'),
            'fecha_pago_desde' => Input::get('fechaPagoDesde'),
            'fecha_pago_hasta' => Input::get('fechaPagoHasta'),
            'sexo' => Input::get('sexo')
        );
        return $datos;
    }

}
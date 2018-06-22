<?php

class LicenciasController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $licencias = Licencia::all();
        $listaLicencias=array();
        if( $licencias->count() ){
            foreach( $licencias as $licencia ){
                $listaLicencias[]=array(
                    'id' => $licencia->id,
                    'sid' => $licencia->sid,
                    'idTrabajador' => $licencia->trabajador_id,
                    'idMes' => $licencia->mes_id,
                    'desde' => $licencia->desde,
                    'hasta' => $licencia->hasta,
                    'dias' => $licencia->dias,
                    'codigo' => $licencia->codigo,
                    'observacion' => $licencia->observacion,
                    'fechaCreacion' => $licencia->created_at
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaLicencias
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
        $datos = Input::all();
        $errores = null;  
        
        if(!$errores){
            foreach($datos as $dato){
                $mes = MesDeTrabajo::where('mes', $dato['mes'])->first();
                $licencia = new Licencia();
                $licencia->sid = Funciones::generarSID();
                $licencia->trabajador_id = $dato['idTrabajador'];
                $licencia->mes_id = $mes->id;
                $licencia->desde = $dato['desde'];
                $licencia->hasta = $dato['hasta'];
                $licencia->dias = $dato['dias'];
                $licencia->codigo = $dato['codigo'];
                $licencia->observacion = $dato['observacion'];
                $licencia->save();

                $trabajador = $licencia->trabajador;
                $ficha = $trabajador->ficha();
                Logs::crearLog('#ingreso-licencias', $trabajador->id, $ficha->nombreCompleto(), 'Create', $licencia->id, $licencia->dias, NULL);
            }
            
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
        
        return Response::json($respuesta);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showa($sid)
    {
        $licencia = Licencia::whereSid($sid)->first();

        $datos=array(
            'id' => $licencia->id,
            'sid' => $licencia->sid,            
            'desde' => $licencia->desde,
            'hasta' => $licencia->hasta,
            'codigo' => $licencia->codigo,
            'observacion' => $licencia->observacion,
            'dias' => $licencia->dias,        
            'trabajador' => $licencia->trabajadorLicencia()
        );
        
        return Response::json($datos);
    }
    
    public function show($sid)
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-licencias');
        $datosLicencia = null;
        $trabajadores = array();
        $primerMes = null;
        $ultimoMes = null;
        
        if($sid){
            $licencia = Licencia::whereSid($sid)->first();

            $datosLicencia=array(
                'id' => $licencia->id,
                'sid' => $licencia->sid,            
                'desde' => $licencia->desde,
                'hasta' => $licencia->hasta,
                'codigo' => $licencia->codigo,
                'observacion' => $licencia->observacion,
                'dias' => $licencia->dias,        
                'trabajador' => $licencia->trabajadorLicencia()
            );
        }else{
            $empresa = \Session::get('empresa');
            $primerMes = $empresa->primerMes();
            $ultimoMes = $empresa->ultimoMes();
            $trabajadores = Trabajador::activosFiniquitados();
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosLicencia,
            'primerMes' => $primerMes,
            'ultimoMes' => $ultimoMes,
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
        $licencia = Licencia::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Licencia::errores($datos);       
        
        if(!$errores and $licencia){
            $licencia->trabajador_id = $datos['trabajador_id'];
            $licencia->mes_id = $datos['mes_id'];
            $licencia->desde = $datos['desde'];
            $licencia->hasta = $datos['hasta'];
            $licencia->dias = $datos['dias'];
            $licencia->codigo = $datos['codigo'];
            $licencia->observacion = $datos['observacion'];
            $licencia->save();
            
            $trabajador = $licencia->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#ingreso-licencias', $trabajador->id, $ficha->nombreCompleto(), 'Update', $licencia->id, $licencia->dias, NULL);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $licencia->sid
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
        
        $licencia = Licencia::whereSid($sid)->first();
        
        $trabajador = $licencia->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#ingreso-licencias', $trabajador->id, $ficha->nombreCompleto(), 'Delete', $licencia['id'], $licencia['dias'], NULL);
        
        $licencia->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'mes_id' => Input::get('idMes'),
            'desde' => Input::get('desde'),
            'hasta' => Input::get('hasta'),
            'dias' => Input::get('dias'),
            'codigo' => Input::get('codigo'),
            'observacion' => Input::get('observacion')
        );
        return $datos;
    }

}
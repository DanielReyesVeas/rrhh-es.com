<?php

class VacacionesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $vacaciones = Vacaciones::all();
        $listaVacaciones=array();
        if( $vacaciones->count() ){
            foreach( $vacaciones as $vac ){
                $listaVacaciones[]=array(
                    'id' => $vac->id,
                    'dias' => $vac->dias
                );
            }
        }
        
        
        $datos = array(
            'datos' => $listaVacaciones
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
        
        if(!$errores){
            $vacaciones = new Vacaciones();
            $vacaciones->sid = Funciones::generarSID();;
            $vacaciones->trabajador_id = $datos['trabajador_id'];
            $vacaciones->mes_id = $datos['mes_id'];
            $vacaciones->dias = $datos['dias'];
            $vacaciones->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $vacaciones->sid
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
        $permisos = array();
        $detalle = array();
        $vacaciones = Vacaciones::whereSid($sid)->first();
        
        if($vacaciones){
            $detalle = array(
                'id' => $vacaciones->id,
                'sid' => $vacaciones->sid,
                'mes' => $vacaciones->mes,
                'dias' => $vacaciones->dias,
                'totalTomaVacaciones' => $vacaciones->totalTomaVacaciones(),
                'tomaVacaciones' => $vacaciones->tomaVacacionesMes()
            );
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $detalle
        );
        
        return Response::json($datos);
    }
    
    public function eliminarTomaVacaciones()
    {        
        $datos = Input::all();        
        $sid = $datos['sid'];        
        $tomaVacaciones = TomaVacaciones::whereSid($sid)->first();
        $mes = $tomaVacaciones['mes'];
        $trabajador = Trabajador::find($tomaVacaciones->trabajador_id);
        $tomaVacaciones->delete();
        $empleado = $trabajador->ficha();
        $dias = $empleado->vacaciones;
        $trabajador->recalcularVacaciones($dias);
        $vacaciones = Vacaciones::where('mes', $mes)->first();
        
        $datos = array(
            'success' => true, 
            'mensaje' => "La Información fue eliminada correctamente",
            'vacaciones' => $vacaciones
        );
        
        return Response::json($datos);
    }
    
    public function tomaVacaciones()
    {
        $datos = Input::all();
        $sidTrabajador = $datos['sid'];
        $tomaVacaciones = $datos['tomaVacaciones'];
        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        
        foreach($tomaVacaciones as $toma){
            $vacaciones = new TomaVacaciones();
            $vacaciones->sid = Funciones::generarSID();
            $vacaciones->trabajador_id = $trabajador['id'];
            $vacaciones->mes = $toma['mes'];
            $vacaciones->desde = $toma['desde'];
            $vacaciones->hasta = $toma['hasta'];
            $vacaciones->dias = $toma['dias'];
            $vacaciones->save();
        }
        
        $ficha = $trabajador->ficha();
        $dias = $ficha->dias;
        
        $trabajador->recalcularVacaciones($dias, $tomaVacaciones[0]['mes']);
        
        Logs::crearLog('#trabajadores-vacaciones', $trabajador->id, $trabajador->rut_formato(), 'Toma Vacaciones', $trabajador->id, $ficha->nombreCompleto(), NULL, $vacaciones->dias, $vacaciones->dias);
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente",
            'sidTrabajador' => $trabajador['sid'],
            'datos' => $datos,
            'd' => $tomaVacaciones[0]['mes']
        );
        
        return Response::json($respuesta);
    }
    
    public function recalcularVacaciones()
    {
        $datos = Input::all();
        $sidTrabajador = $datos['sid'];
        $dias = $datos['dias'];
        $desde = null;
        $calcularDesde = $datos['desde'];

        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        if($calcularDesde=='primerMesSistema'){
            $primerMes = MesDeTrabajo::orderBy('mes')->first();
            $desde = $primerMes->mes;
            if($dias==0){
                $dias = 1.25;   
            }
        }else{
            if($dias==0){
                $dias = $trabajador->diasInicialesVacaciones();   
            }
        }
        $trabajador->recalcularVacaciones($dias, $desde);
        
        $ficha = $trabajador->ficha();
        Logs::crearLog('#trabajadores-vacaciones', $trabajador->id, $trabajador->rut_formato(), 'Recálculo', $trabajador->id, $ficha->nombreCompleto(), NULL, $dias, $dias);
        
        $datos = array(
            'trabajador' => $trabajador,
            'desde' => $desde
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
        $vacaciones = Vacaciones::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Vacaciones::errores($datos);       
        
        if(!$errores and $vacaciones){
            $vacaciones->dias = $datos['dias'];
            $vacaciones->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $vacaciones->sid
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
        Vacaciones::whereSid($sid)->first()->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'dias' => Input::get('dias')
        );
        return $datos;
    }

}
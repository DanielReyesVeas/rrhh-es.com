<?php

class LibrosRemuneracionesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {

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
        $errores = LibroRemuneraciones::errores($datos);      
        
        if(!$errores){
            $libroRemuneraciones = new LibroRemuneraciones();
            $libroRemuneraciones->sid = Funciones::generarSID();
            $libroRemuneraciones->empresa_id = $datos['empresa_id'];
            $libroRemuneraciones->empresa_razon_social = $datos['empresa_razon_social'];
            $libroRemuneraciones->empresa_rut = $datos['empresa_rut'];
            $libroRemuneraciones->empresa_direccion = $datos['empresa_direccion'];
            $libroRemuneraciones->liquidacion_id = $datos['liquidacion_id'];
            $libroRemuneraciones->trabajador_id = $datos['trabajador_id'];
            $libroRemuneraciones->trabajador_nombre = $datos['trabajador_nombre'];
            $libroRemuneraciones->trabajador_rut = $datos['trabajador_rut'];
            $libroRemuneraciones->sueldo_base = $datos['sueldo_base'];
            $libroRemuneraciones->total_haberes = $datos['total_haberes'];
            $libroRemuneraciones->dias_trabajados = $datos['dias_trabajados'];
            $libroRemuneraciones->sueldo = $datos['sueldo'];
            $libroRemuneraciones->total_afp = $datos['total_afp'];
            $libroRemuneraciones->inasistencias_atrasos = $datos['inasistencias_atrasos'];
            $libroRemuneraciones->total_apv = $datos['total_apv'];
            $libroRemuneraciones->gratificacion = $datos['gratificacion'];
            $libroRemuneraciones->total_salud = $datos['total_salud'];
            $libroRemuneraciones->imponibles = $datos['imponibles'];
            $libroRemuneraciones->impuesto_renta = $datos['impuesto_renta'];
            $libroRemuneraciones->horas_extra = $datos['horas_extra'];
            $libroRemuneraciones->total_otros_imponibles = $datos['total_otros_imponibles'];
            $libroRemuneraciones->total_imponibles = $datos['total_imponibles'];
            $libroRemuneraciones->anticipos = $datos['anticipos'];
            $libroRemuneraciones->asignacion_familiar = $datos['asignacion_familiar'];
            $libroRemuneraciones->seguro_desempleo = $datos['seguro_desempleo'];
            $libroRemuneraciones->total_descuentos = $datos['total_descuentos'];
            $libroRemuneraciones->haberes_no_imponibles = $datos['haberes_no_imponibles'];
            $libroRemuneraciones->sueldo_liquido = $datos['sueldo_liquido'];
            $libroRemuneraciones->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $libroRemuneraciones->sid
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
        $libroRemuneraciones = LibroRemuneraciones::whereSid($sid)->first();
                
        $datosLibroRemuneraciones=array(
            'id' => $libroRemuneraciones->id,
            'sid' => $libroRemuneraciones->sid,
            'nombreTrabajador' => $libroRemuneraciones->trabajador_nombre,
            'rutTrabajador' => $libroRemuneraciones->trabajador_rut,
            'rutFormatoTrabajador' => $libroRemuneraciones->trabajador->rut_formato(),
            'sueldoBase' => $libroRemuneraciones->sueldo_base,
            'totalHaberes' => $libroRemuneraciones->total_haberes,
            'diasTrabajados' => $libroRemuneraciones->dias_trabajados,
            'sueldo' => $libroRemuneraciones->sueldo,
            'totalAfp' => $libroRemuneraciones->total_afp,
            'inasistenciasAtrasos' => $libroRemuneraciones->inasistencias_atrasos,
            'totalApv' => $libroRemuneraciones->total_apv,
            'gratificacion' => $libroRemuneraciones->gratificacion,
            'totalSalud' => $libroRemuneraciones->total_salud,
            'imponibles' => $libroRemuneraciones->imponibles,
            'impuestoRenta' => $libroRemuneraciones->impuesto_renta,
            'horasExtra' => $libroRemuneraciones->horas_extra,
            'totalOtrosDescuentos' => $libroRemuneraciones->total_otros_descuentos,
            'totalImponibles' => $libroRemuneraciones->total_imponibles,
            'sueldoBase' => $libroRemuneraciones->sueldo_base,
            'anticipos' => $libroRemuneraciones->anticipos,
            'asignacionFamiliar' => $libroRemuneraciones->asignacion_familiar,
            'seguroDesempleo' => $libroRemuneraciones->seguro_desempleo,
            'totalDescuentos' => $libroRemuneraciones->total_descuentos,
            'haberesNoImponibles' => $libroRemuneraciones->haberes_no_imponibles,
            'sueldoLiquido' => $libroRemuneraciones->sueldo_liquido
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosLibroRemuneraciones
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
        $libroRemuneraciones = LibroRemuneraciones::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = LibroRemuneraciones::errores($datos);       
        
        if(!$errores and $libroRemuneraciones){
            $libroRemuneraciones->empresa_id = $datos['empresa_id'];
            $libroRemuneraciones->empresa_razon_social = $datos['empresa_razon_social'];
            $libroRemuneraciones->empresa_rut = $datos['empresa_rut'];
            $libroRemuneraciones->empresa_direccion = $datos['empresa_direccion'];
            $libroRemuneraciones->liquidacion_id = $datos['liquidacion_id'];
            $libroRemuneraciones->trabajador_id = $datos['trabajador_id'];
            $libroRemuneraciones->trabajador_nombre = $datos['trabajador_nombre'];
            $libroRemuneraciones->trabajador_rut = $datos['trabajador_rut'];
            $libroRemuneraciones->sueldo_base = $datos['sueldo_base'];
            $libroRemuneraciones->total_haberes = $datos['total_haberes'];
            $libroRemuneraciones->dias_trabajados = $datos['dias_trabajados'];
            $libroRemuneraciones->sueldo = $datos['sueldo'];
            $libroRemuneraciones->total_afp = $datos['total_afp'];
            $libroRemuneraciones->inasistencias_atrasos = $datos['inasistencias_atrasos'];
            $libroRemuneraciones->total_apv = $datos['total_apv'];
            $libroRemuneraciones->gratificacion = $datos['gratificacion'];
            $libroRemuneraciones->total_salud = $datos['total_salud'];
            $libroRemuneraciones->imponibles = $datos['imponibles'];
            $libroRemuneraciones->impuesto_renta = $datos['impuesto_renta'];
            $libroRemuneraciones->horas_extra = $datos['horas_extra'];
            $libroRemuneraciones->total_otros_imponibles = $datos['total_otros_imponibles'];
            $libroRemuneraciones->total_imponibles = $datos['total_imponibles'];
            $libroRemuneraciones->anticipos = $datos['anticipos'];
            $libroRemuneraciones->asignacion_familiar = $datos['asignacion_familiar'];
            $libroRemuneraciones->seguro_desempleo = $datos['seguro_desempleo'];
            $libroRemuneraciones->total_descuentos = $datos['total_descuentos'];
            $libroRemuneraciones->haberes_no_imponibles = $datos['haberes_no_imponibles'];
            $libroRemuneraciones->sueldo_liquido = $datos['sueldo_liquido'];
            $libroRemuneraciones->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $libroRemuneraciones->sid
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
        LibroRemuneraciones::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'empresa_id' => Input::get('idEmpresa'),
            'empresa_razon_social' => Input::get('razonSocialEmpresa'),
            'empresa_rut' => Input::get('rutEmpresa'),
            'empresa_direccion' => Input::get('direccionEmpresa'),
            'liquidacion_id' => Input::get('idLiquidacion'),
            'trabajador_id' => Input::get('idTrabajador'),
            'trabajador_nombre' => Input::get('nombreTrabajador'),
            'trabajador_rut' => Input::get('rutTrabajador'),
            'sueldo_base' => Input::get('sueldoBase'),
            'total_haberes' => Input::get('totalHaberes'),
            'dias_trabajados' => Input::get('diasTrabajados'),
            'sueldo' => Input::get('sueldo'),
            'total_afp' => Input::get('totalAfp'),
            'inasistencias_atrasos' => Input::get('inasistenciasAtrasos'),
            'total_apv' => Input::get('totalApv'),
            'gratificacion' => Input::get('gratificacion'),
            'total_salud' => Input::get('totalSalud'),
            'imponibles' => Input::get('imponibles'),
            'impuesto_renta' => Input::get('impuestoRenta'),
            'horas_extra' => Input::get('horasExtra'),
            'total_otros_descuentos' => Input::get('totalOtrosDescuentos'),
            'total_imponibles' => Input::get('totalImponibles'),
            'anticipos' => Input::get('anticipos'),
            'asignacion_familiar' => Input::get('asignacionFamiliar'),
            'seguro_desempleo' => Input::get('seguroDesempleo'),
            'total_descuentos' => Input::get('totalDescuentos'),
            'haberes_no_imponibles' => Input::get('noImponibles'),
            'sueldo_liquido' => Input::get('sueldoLiquido')
        );
        return $datos;
    }

}
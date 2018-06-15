<?php

class CartasNotificacionController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $cartasNotificacion = CartaNotificacion::all();
        $listaCartasNotificacion=array();
        if( $cartasNotificacion->count() ){
            foreach( $cartasNotificacion as $cartaNotificacion ){
                $listaCartasNotificacion[]=array(
                    'id' => $cartaNotificacion->id,
                    'sid' => $cartaNotificacion->sid
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaCartasNotificacion
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
        $errores = CartaNotificacion::errores($datos);      
        
        if(!$errores){
            $filename = date("d-m-Y-H-i-s")."_CartaNotificacion_".$datos['trabajador_rut']. '.pdf';
            $destination = public_path() . '/stories/' . $filename;
                        
            File::put($destination, PDF::load(utf8_decode($datos['cuerpo']), 'A4', 'portrait')->output());
            
            $documento = new Documento();
            $documento->sid = Funciones::generarSID();
            $documento->trabajador_id = $datos['trabajador_id'];
            $documento->tipo_documento_id = 3;
            $documento->nombre = $filename;
            $documento->alias = 'Carta Notificación ' . $datos['trabajador_nombre_completo'] . '.pdf';
            $documento->descripcion = 'Carta de Notificación de ' . $datos['trabajador_nombre_completo'];
            $documento->save();
            
            $cartaNotificacion = new CartaNotificacion();
            $cartaNotificacion->sid = Funciones::generarSID();
            $cartaNotificacion->documento_id = $documento->id;
            $cartaNotificacion->plantilla_carta_id = $datos['plantilla_carta_id'];
            $cartaNotificacion->trabajador_id = $datos['trabajador_id'];
            $cartaNotificacion->encargado_id = $datos['encargado_id'];
            $cartaNotificacion->empresa_id = $datos['empresa_id'];
            $cartaNotificacion->empresa_razon_social = $datos['empresa_razon_social'];
            $cartaNotificacion->empresa_rut = $datos['empresa_rut'];
            $cartaNotificacion->empresa_direccion = $datos['empresa_direccion'];
            $cartaNotificacion->fecha = $datos['fecha'];
            $cartaNotificacion->folio = $datos['folio'];
            $cartaNotificacion->cuerpo = $datos['cuerpo'];
            $cartaNotificacion->trabajador_rut = $datos['trabajador_rut'];
            $cartaNotificacion->trabajador_nombre_completo = $datos['trabajador_nombre_completo'];
            $cartaNotificacion->trabajador_cargo = $datos['trabajador_cargo'];
            $cartaNotificacion->trabajador_seccion = $datos['trabajador_seccion'];
            $cartaNotificacion->trabajador_fecha_ingreso = $datos['trabajador_fecha_ingreso'];
            $cartaNotificacion->trabajador_direccion = $datos['trabajador_direccion'];
            $cartaNotificacion->trabajador_provincia = $datos['trabajador_provincia'];
            $cartaNotificacion->trabajador_comuna = $datos['trabajador_comuna'];
            $cartaNotificacion->save();   
            
            $trabajador = $cartaNotificacion->trabajador;
            $ficha = $trabajador->ficha();
            
            Logs::crearLog('#cartas-de-notificacion', $documento->id, $documento->alias, 'Create', $documento->trabajador_id, $ficha->nombreCompleto(), 'Cartas de Notificación Trabajadores');
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $cartaNotificacion->sid
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cartas-de-notificacion');
        $datosCarta = null;
        $trabajadores = array();
        $trabajador = null;
        $plantillas = PlantillaCartaNotificacion::plantillas();
        
        if($sid){
            $cartaNotificacion = CartaNotificacion::whereSid($sid)->first();
            $datosCarta=array(
                'id' => $cartaNotificacion->id,
                'sid' => $cartaNotificacion->sid,
                'cuerpo' => $cartaNotificacion->cuerpo,
                'trabajador' => $cartaNotificacion->trabajadorCarta()
            );
            $trabajador = array(
                'nombreCompleto' => $cartaNotificacion->trabajador_nombre_completo,
                'direccion' => $cartaNotificacion->trabajador_direccion,
                'comuna' => array(
                    'comuna' => $cartaNotificacion->trabajador_comuna,
                    'provincia' => $cartaNotificacion->trabajador_provincia, 
                ),                                               
                'fechaIngreso' => $cartaNotificacion->trabajador_fecha_ingreso
            );
        }else{
            $trabajadores = Trabajador::activosFiniquitados();
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosCarta,
            'trabajadores' => $trabajadores,
            'trabajador' => $trabajador,
            'plantillas' => $plantillas
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
        $cartaNotificacion = CartaNotificacion::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = CartaNotificacion::errores($datos);       
        
        if(!$errores and $cartaNotificacion){
            $cartaNotificacion->plantilla_carta_id = $datos['plantilla_carta_id'];
            $cartaNotificacion->trabajador_id = $datos['trabajador_id'];
            $cartaNotificacion->encargado_id = $datos['encargado_id'];
            $cartaNotificacion->empresa_id = $datos['empresa_id'];
            $cartaNotificacion->empresa_razon_social = $datos['empresa_razon_social'];
            $cartaNotificacion->empresa_rut = $datos['empresa_rut'];
            $cartaNotificacion->empresa_direccion = $datos['empresa_direccion'];
            $cartaNotificacion->fecha = $datos['fecha'];
            $cartaNotificacion->folio = $datos['folio'];
            $cartaNotificacion->cuerpo = $datos['cuerpo'];
            $cartaNotificacion->trabajador_rut = $datos['trabajador_rut'];
            $cartaNotificacion->trabajador_nombre_completo = $datos['trabajador_nombre_completo'];
            $cartaNotificacion->trabajador_cargo = $datos['trabajador_cargo'];
            $cartaNotificacion->trabajador_seccion = $datos['trabajador_seccion'];
            $cartaNotificacion->trabajador_fecha_ingreso = $datos['trabajador_fecha_ingreso'];
            $cartaNotificacion->trabajador_direccion = $datos['trabajador_direccion'];
            $cartaNotificacion->trabajador_provincia = $datos['trabajador_provincia'];
            $cartaNotificacion->trabajador_comuna = $datos['trabajador_comuna'];
            $cartaNotificacion->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $cartaNotificacion->sid
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
        $cartaNotificacion = CartaNotificacion::whereSid($sid)->first();
        $idDoc = $cartaNotificacion->documento_id;
        $documento = Documento::find($idDoc);
        
        $trabajador = $cartaNotificacion->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#cartas-de-notificacion', $documento->id, $documento->alias, 'Delete', $documento->trabajador_id, $ficha->nombreCompleto(), 'Cartas de Notificación Trabajadores');
        
        $documento->delete();
        $cartaNotificacion->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'plantilla_carta_id' => Input::get('idPlantillaCarta'),
            'trabajador_id' => Input::get('idTrabajador'),
            'encargado_id' => Input::get('idEncargado'),
            'empresa_id' => Input::get('idEmpresa'),
            'empresa_razon_social' => Input::get('razonSocialEmpresa'),
            'empresa_rut' => Input::get('rutEmpresa'),
            'empresa_direccion' => Input::get('direccionEmpresa'),
            'trabajador_rut' => Input::get('rutTrabajador'),
            'trabajador_nombre_completo' => Input::get('nombreCompletoTrabajador'),
            'trabajador_cargo' => Input::get('cargoTrabajador'),
            'trabajador_seccion' => Input::get('seccionTrabajador'),
            'trabajador_fecha_ingreso' => Input::get('fechaIngresoTrabajador'),
            'trabajador_direccion' => Input::get('direccionTrabajador'),
            'trabajador_provincia' => Input::get('provinciaTrabajador'),
            'trabajador_comuna' => Input::get('comunaTrabajador'),
            'folio' => Input::get('folio'),
            'cuerpo' => Input::get('cuerpo'),
            'inasistencias' => Input::get('inasistencias'),
            'fecha' => Input::get('fecha')
        );
        
        return $datos;
    }

}
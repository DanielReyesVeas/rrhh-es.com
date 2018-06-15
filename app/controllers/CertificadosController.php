<?php

class CertificadosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $certificados = Certificado::all();
        $listaCertificados=array();
        if( $certificados->count() ){
            foreach( $certificados as $certificado ){
                $listaCertificados[]=array(
                    'id' => $certificado->id,
                    'sid' => $certificado->sid
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaCertificados
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
        $errores = Certificado::errores($datos);      
        
        if(!$errores){            
            $filename = date("d-m-Y-H-i-s")."_Certificado_".$datos['trabajador_rut']. '.pdf';
            $destination = public_path() . '/stories/' . $filename;
                        
            File::put($destination, PDF::load(utf8_decode($datos['cuerpo']), 'A4', 'portrait')->output());
            $documento = new Documento();
            $documento->sid = Funciones::generarSID();
            $documento->trabajador_id = $datos['trabajador_id'];
            $documento->tipo_documento_id = 2;
            $documento->nombre = $filename;
            $documento->alias = 'Certificado ' . $datos['trabajador_nombre_completo'] . '.pdf';
            $documento->descripcion = 'Certificado de ' . $datos['trabajador_nombre_completo'];
            $documento->save();
            
            $certificado = new Certificado();
            $certificado->sid = Funciones::generarSID();
            $certificado->documento_id = $documento->id;
            $certificado->plantilla_certificado_id = $datos['plantilla_certificado_id'];
            $certificado->trabajador_id = $datos['trabajador_id'];
            $certificado->encargado_id = $datos['encargado_id'];
            $certificado->empresa_id = $datos['empresa_id'];
            $certificado->empresa_razon_social = $datos['empresa_razon_social'];
            $certificado->empresa_rut = $datos['empresa_rut'];
            $certificado->empresa_direccion = $datos['empresa_direccion'];
            $certificado->fecha = $datos['fecha'];
            $certificado->folio = $datos['folio'];
            $certificado->cuerpo = $datos['cuerpo'];
            $certificado->trabajador_rut = $datos['trabajador_rut'];
            $certificado->trabajador_nombre_completo = $datos['trabajador_nombre_completo'];
            $certificado->trabajador_cargo = $datos['trabajador_cargo'];
            $certificado->trabajador_seccion = $datos['trabajador_seccion'];
            $certificado->trabajador_fecha_ingreso = $datos['trabajador_fecha_ingreso'];
            $certificado->trabajador_direccion = $datos['trabajador_direccion'];
            $certificado->trabajador_provincia = $datos['trabajador_provincia'];
            $certificado->trabajador_comuna = $datos['trabajador_comuna'];
            $certificado->save();  
            
            $trabajador = $certificado->trabajador;
            $ficha = $trabajador->ficha();
            
            Logs::crearLog('#certificados', $documento->id, $documento->alias, 'Create', $documento->trabajador_id, $ficha->nombreCompleto(), 'Certificados Trabajadores');
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $certificado->sid
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
        $certificado = Certificado::whereSid($sid)->first();

        $datosCertificado = array(
            'id' => $certificado->id,
            'sid' => $certificado->sid,
            'nombre' => $certificado->plantillaCertificado->nombre,
            'cuerpo' => $certificado->cuerpo            
        );
        
        $trabajador = array(
            'nombreCompleto' => $certificado->trabajador_nombre_completo,
            'direccion' => $certificado->trabajador_direccion,
            'comuna' => array(
                'comuna' => $certificado->trabajador_comuna,
                'provincia' => $certificado->trabajador_provincia, 
            ),                                   
            'fechaIngreso' => $certificado->trabajador_fecha_ingreso                        
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosCertificado,
            'trabajador' => $trabajador
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
        $certificado = Certificado::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Certificado::errores($datos);       
        
        if(!$errores and $cartaNotificacion){
            $certificado->plantilla_certificado_id = $datos['plantilla_certificado_id'];
            $certificado->trabajador_id = $datos['trabajador_id'];
            $certificado->encargado_id = $datos['encargado_id'];
            $certificado->empresa_id = $datos['empresa_id'];
            $certificado->empresa_razon_social = $datos['empresa_razon_social'];
            $certificado->empresa_rut = $datos['empresa_rut'];
            $certificado->empresa_direccion = $datos['empresa_direccion'];
            $certificado->fecha = $datos['fecha'];
            $certificado->folio = $datos['folio'];
            $certificado->cuerpo = $datos['cuerpo'];
            $certificado->trabajador_rut = $datos['trabajador_rut'];
            $certificado->trabajador_nombre_completo = $datos['trabajador_nombre_completo'];
            $certificado->trabajador_cargo = $datos['trabajador_cargo'];
            $certificado->trabajador_seccion = $datos['trabajador_seccion'];
            $certificado->trabajador_fecha_ingreso = $datos['trabajador_fecha_ingreso'];
            $certificado->trabajador_direccion = $datos['trabajador_direccion'];
            $certificado->trabajador_provincia = $datos['trabajador_provincia'];
            $certificado->trabajador_comuna = $datos['trabajador_comuna'];
            $certificado->save();                    
            
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
        
        $certificado = Certificado::whereSid($sid)->first();
        $idDoc = $certificado->documento_id;
        $documento = Documento::find($idDoc);
        
        $trabajador = $certificado->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#certificados', $documento->id, $documento->alias, 'Delete', $documento->trabajador_id, $ficha->nombreCompleto(), 'Certificados Trabajadores');
        
        $documento->delete();
        $certificado->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'plantilla_certificado_id' => Input::get('idPlantillaCertificado'),
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
            'fecha' => Input::get('fecha')
        );
        return $datos;
    }

}
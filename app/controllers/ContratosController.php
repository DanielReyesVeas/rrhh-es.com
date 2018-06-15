<?php

class ContratosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $contratos = Contrato::all();
        $listaContratos=array();
        if( $contratos->count() ){
            foreach( $contratos as $contrato ){
                $listaContratos[]=array(
                    'id' => $contrato->id,
                    'sid' => $contrato->sid
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaContratos
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
        $errores = Contrato::errores($datos);      
        $empresa = \Session::get('empresa');
        
        if(!$errores){                        
            $filename = date("d-m-Y-H-i-s")."_Contrato_".$datos['trabajador_rut']. '.pdf';
            $destination = public_path() . '/stories/' . $filename;
            $datos['cuerpo'] = $datos['cuerpo'] . '<div style="margin-left: 10px; margin-top: 300px;"><table style="width: 100%;"><tr><td style="width: 30%; border-bottom: 1px solid black;"></td><td style="width: 10%;"></td><td style="width: 30%; border-bottom: 1px solid black;"></td></tr><tr><td style="text-align: center;">' . strtoupper($datos['trabajador_nombre_completo']) . '</td><td></td><td style="text-align: center;">' . strtoupper($datos['empresa_razon_social']) . '</td></tr><tr><td style="text-align: center;">' . Funciones::formatear_rut($datos['trabajador_rut']) . '</td><td></td><td style="text-align: center;">' . Funciones::formatear_rut($datos['empresa_rut']) . '</td></tr></table></div><div align="right" style="margin-top: 80px;"><p style="font-size: 10px; color: #A9A9A9"><i>Contrato de Trabajo - ' . $datos['empresa_razon_social'] . '<br />Rut: ' . Funciones::formatear_rut($datos['empresa_rut']) . '<br />' . $empresa->direccion . ' - ' . $empresa->comuna->comuna . '</i></p></div>';
      
            File::put($destination, PDF::load(utf8_decode($datos['cuerpo']), 'A4', 'portrait')->output());
            $documento = new Documento();
            $documento->sid = Funciones::generarSID();
            $documento->trabajador_id = $datos['trabajador_id'];
            $documento->tipo_documento_id = 1;
            $documento->nombre = $filename;
            $documento->alias = 'Contrato ' . $datos['trabajador_nombre_completo'] . '.pdf';
            $documento->descripcion = 'Contrato de Trabajo de ' . $datos['trabajador_nombre_completo'];
            $documento->save();
            
            $contrato = new Contrato();
            $contrato->sid = Funciones::generarSID();
            $contrato->documento_id = $documento->id;
            $contrato->tipo_contrato_id = $datos['tipo_contrato_id'];
            $contrato->fecha_vencimiento = $datos['fecha_vencimiento'];
            $contrato->trabajador_id = $datos['trabajador_id'];
            $contrato->trabajador_nombre_completo = $datos['trabajador_nombre_completo'];
            $contrato->trabajador_rut = $datos['trabajador_rut'];
            $contrato->trabajador_cargo = $datos['trabajador_cargo'];
            $contrato->trabajador_seccion = $datos['trabajador_seccion'];
            $contrato->trabajador_domicilio = $datos['trabajador_domicilio'];
            $contrato->trabajador_estado_civil = $datos['trabajador_estado_civil'];
            $contrato->trabajador_fecha_nacimiento = $datos['trabajador_fecha_nacimiento'];
            $contrato->trabajador_fecha_ingreso = $datos['trabajador_fecha_ingreso'];
            $contrato->encargado_id = $datos['encargado_id'];
            $contrato->empresa_id = $datos['empresa_id'];
            $contrato->empresa_rut = $datos['empresa_rut'];
            $contrato->empresa_razon_social = $datos['empresa_razon_social'];
            $contrato->empresa_domicilio = $datos['empresa_domicilio'];
            $contrato->empresa_representante_nombre_completo = $datos['empresa_representante_nombre_completo'];
            $contrato->empresa_representante_rut = $datos['empresa_representante_rut'];
            $contrato->empresa_representante_domicilio = $datos['empresa_representante_domicilio'];
            $contrato->cuerpo = $datos['cuerpo'];
            $contrato->save();    
            
            Logs::crearLog('#trabajadores', $documento->id, $documento->alias, 'Create', $contrato->id, $contrato->trabajador_nombre_completo, 'Contratos Trabajador'); 
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $contrato
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
        $contrato = Contrato::whereSid($sid)->first();

        $contrato = array(
            'id' => $contrato->id,
            'sid' => $contrato->sid,
            'cuerpo' => $contrato->cuerpo            
        );
        
        $trabajador = array(
            'nombreCompleto' => $contrato->trabajador_nombre_completo,
            'rut' => $contrato->trabajador->rut_formato(),
            'domicilio' => $contrato->domicilio,                                   
            'fechaIngreso' => $contrato->trabajador_fecha_ingreso                        
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $contrato,
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
    public function edit($id)
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
        $contrato = Contrato::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Contrato::errores($datos);       
        
        if(!$errores and $contrato){
            $contrato->tipo_contrato_id = $datos['tipo_contrato_id'];
            $contrato->fecha_vencimiento = $datos['fecha_vencimiento'];
            $contrato->trabajador_id = $datos['trabajador_id'];
            $contrato->trabajador_nombre_completo = $datos['trabajador_nombre_completo'];
            $contrato->trabajador_rut = $datos['trabajador_rut'];
            $contrato->trabajador_cargo = $datos['trabajador_cargo'];
            $contrato->trabajador_seccion = $datos['trabajador_seccion'];
            $contrato->trabajador_domicilio = $datos['trabajador_domicilio'];
            $contrato->trabajador_estado_civil = $datos['trabajador_estado_civil'];
            $contrato->trabajador_fecha_nacimiento = $datos['trabajador_fecha_nacimiento'];
            $contrato->trabajador_fecha_ingreso = $datos['trabajador_fecha_ingreso'];
            $contrato->encargado_id = $datos['encargado_id'];
            $contrato->empresa_id = $datos['empresa_id'];
            $contrato->empresa_rut = $datos['empresa_rut'];
            $contrato->empresa_razon_social = $datos['empresa_razon_social'];
            $contrato->empresa_domicilio = $datos['empresa_domicilio'];
            $contrato->empresa_responsable_nombre_completo = $datos['empresa_representante_nombre_completo'];
            $contrato->empresa_responsable_rut = $datos['empresa_representante_rut'];
            $contrato->empresa_responsable_domicilio = $datos['empresa_representante_domicilio'];
            $contrato->cuerpo = $datos['cuerpo'];
            $contrato->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $contrato->sid
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
        $contrato = Contrato::whereSid($sid)->first();
        $idDoc = $contrato->documento_id;
        $documento = Documento::find($idDoc);
        $documento->delete();
        $contrato->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }    
    
    public function get_datos_formulario(){
        $datos = array(
            'tipo_contrato_id' => Input::get('idTipoContrato'),
            'fecha_vencimiento' => Input::get('fechaVencimiento'),
            'trabajador_id' => Input::get('idTrabajador'),
            'trabajador_nombre_completo' => Input::get('nombreCompletoTrabajador'),
            'trabajador_rut' => Input::get('rutTrabajador'),
            'trabajador_cargo' => Input::get('cargoTrabajador'),
            'trabajador_seccion' => Input::get('seccionTrabajador'),
            'trabajador_domicilio' => Input::get('domicilioTrabajador'),
            'trabajador_estado_civil' => Input::get('estadoCivilTrabajador'),
            'trabajador_fecha_nacimiento' => Input::get('fechaNacimientoTrabajador'),
            'encargado_id' => Input::get('idEncargado'),
            'trabajador_fecha_ingreso' => Input::get('fechaIngresoTrabajador'),
            'empresa_id' => Input::get('idEmpresa'),
            'empresa_rut' => Input::get('rutEmpresa'),
            'empresa_razon_social' => Input::get('razonSocialEmpresa'),
            'empresa_domicilio' => Input::get('domicilioEmpresa'),
            'empresa_representante_nombre_completo' => Input::get('nombreCompletoRepresentanteEmpresa'),
            'empresa_representante_rut' => Input::get('rutRepresentanteEmpresa'),
            'empresa_representante_domicilio' => Input::get('domicilioRepresentanteEmpresa'),
            'cuerpo' => Input::get('cuerpo'),
        );
        return $datos;
    }

}
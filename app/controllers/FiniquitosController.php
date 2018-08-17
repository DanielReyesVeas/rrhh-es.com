<?php

class FiniquitosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $finiquitos = Finiquito::all();
        $listaFiniquitos = array();
        if( $finiquitos->count() ){
            foreach( $finiquitos as $finiquito ){
                $listaFiniquitos[]=array(
                    'id' => $finiquito->id,
                    'sid' => $finiquito->sid
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaFiniquitos
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
        $errores = Finiquito::errores($datos);      
        
        if(!$errores){
            $filename = date("d-m-Y-H-i-s")."_Finiquito_".$datos['trabajador']['rut']. '.pdf';
            $destination = public_path() . '/stories/' . $filename;
            
            $datos['cuerpo'] = $datos['cuerpo'] . 
                '<div style="margin-left: 10px; margin-top: 200px;">
                    <table style="width: 100%;" class="noClass">
                        <tr>
                            <td style="width: 30%; border-bottom: 1px solid black;"></td>
                            <td style="width: 10%;"></td>
                            <td style="width: 30%; border-bottom: 1px solid black;"></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">' . strtoupper($datos['trabajador']['nombreCompleto']) . '</td>
                            <td></td>
                            <td style="text-align: center;">' . strtoupper($datos['empresa']['empresa']) . '</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">' . Funciones::formatear_rut($datos['trabajador']['rut']) . '</td>
                            <td></td>
                            <td style="text-align: center;">' . Funciones::formatear_rut($datos['empresa']['rut']) . '</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-size: 12px;">c.c. carpeta personal</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-size: 12px;">Inspección del Trabajo</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </div></body></html>';
            
            File::put($destination, PDF::load(utf8_decode($datos['cuerpo']), 'A4', 'portrait')->output());
            $idTrabajador = $datos['trabajador']['id'];
            
            $trabajador = Trabajador::find($idTrabajador);
            $trabajador->finiquitar($datos['fecha']);
            
            $documento = new Documento();
            $documento->sid = Funciones::generarSID();
            $documento->trabajador_id = $idTrabajador;
            $documento->tipo_documento_id = 5;
            $documento->nombre = $filename;
            $documento->alias = 'Finiquito ' . $datos['trabajador']['nombreCompleto'] . '.pdf';
            $documento->descripcion = 'Finiquito de ' . $datos['trabajador']['nombreCompleto'];
            $documento->save();
            
            $finiquito = new Finiquito();
            $finiquito->sid = Funciones::generarSID();
            $finiquito->documento_id = $documento->id;
            $finiquito->causal_finiquito_id = $datos['causal']['id'];
            $finiquito->plantilla_finiquito_id = $datos['plantilla_finiquito_id'];
            $finiquito->trabajador_id = $idTrabajador;
            $finiquito->encargado_id = $datos['encargado_id'];
            $finiquito->empresa_id = $datos['empresa']['id'];
            $finiquito->empresa_razon_social = $datos['empresa']['empresa'];
            $finiquito->empresa_rut = $datos['empresa']['rut'];
            $finiquito->empresa_direccion = $datos['empresa']['direccion'];
            $finiquito->fecha = $datos['fecha'];
            $finiquito->folio = $datos['folio'];
            $finiquito->cuerpo = $datos['cuerpo'];
            $finiquito->trabajador_rut = $datos['trabajador']['rut'];
            $finiquito->trabajador_nombre_completo = $datos['trabajador']['nombreCompleto'];
            $finiquito->trabajador_cargo = $datos['trabajador']['cargo']['nombre'];
            $finiquito->trabajador_seccion = $datos['trabajador']['seccion']['nombre'];
            $finiquito->trabajador_fecha_ingreso = $datos['trabajador']['fechaIngreso'];
            $finiquito->trabajador_direccion = $datos['trabajador']['direccion'];
            $finiquito->trabajador_provincia = $datos['trabajador']['comuna']['provincia'];
            $finiquito->trabajador_comuna = $datos['trabajador']['comuna']['comuna'];
            $finiquito->mes_aviso = $datos['mesAviso'];
            $finiquito->no_imponibles = $datos['noImponibles'];
            $finiquito->vacaciones = $datos['vacaciones']['dias'];
            $finiquito->monto_vacaciones = $datos['vacaciones']['monto'];
            $finiquito->indemnizacion = $datos['indemnizacion']['anios'];
            $finiquito->monto_indemnizacion = $datos['indemnizacion']['monto'];
            $finiquito->sueldo_normal = $datos['sueldoNormal'];
            $finiquito->sueldo_variable = $datos['sueldoVariable'];
            $finiquito->total_finiquito = $datos['totalFiniquito'];
            $finiquito->recibido = false;
            $finiquito->save();     
            
            Logs::crearLog('#finiquitar-trabajador', $documento->id, $documento->alias, 'Create', $documento->trabajador_id, $finiquito->trabajador_nombre_completo, NULL);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $finiquito
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
        $finiquito = Finiquito::whereSid($sid)->first();
        $causalesFiniquito = CausalFiniquito::listaCausalesFiniquito();
        $trabajador = array(
            'id' => $finiquito->trabajador->id,
            'sid' => $finiquito->trabajador->sid,
            'nombreCompleto' => $finiquito->trabajador->ficha()->nombreCompleto()
        );
        
        $datosFiniquito = array(
            'id' => $finiquito->id,
            'sid' => $finiquito->sid,
            'causal' => array(
                'id' => $finiquito->causalFiniquito->id,
                'sid' => $finiquito->causalFiniquito->sid,
                'codigo' => $finiquito->causalFiniquito->codigo,
                'articulo' => $finiquito->causalFiniquito->articulo,
                'nombre' => $finiquito->causalFiniquito->nombre
            ),            
            'fecha' => $finiquito->fecha,            
            'vacaciones' => $finiquito->vacaciones ? true : false,            
            'sueldoNormal' => $finiquito->sueldo_normal ? true : false,            
            'sueldoVariable' => $finiquito->sueldo_variable ? true : false,            
            'mesAviso' => $finiquito->mes_aviso ? true : false,            
            'indemnizacion' => $finiquito->indemnizacion ? true : false,   
            'recibido' => $finiquito->recibido ? true : false
        );
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => array(
                'finiquito' => $datosFiniquito,
                'trabajador' => $trabajador
            ),
            'causales' => $causalesFiniquito
        );
        
        return Response::json($datos);
    }
    
    public function calcular()
    {
        $datos = Input::all();
        $idTrabajador = $datos['idTrabajador'];
        $trabajador = Trabajador::find($idTrabajador);
        $empleado = $trabajador->ficha();
        $mes = \Session::get('mesActivo')->mes;
        $ufAnterior = ValorIndicador::ufAnterior();
        $tope = round($ufAnterior->valor * 90);
        $sumaImponibles = 0;
        $totalImponibles = 0;
        $sumaNoImponibles = 0;
        $totalNoImponibles = 0;
        $gratificacion = 0;
        $sueldo = 0;
        $montoSueldo = 0;
        $imponibles = array();
        $noImponibles = array();
        $vacacionesDetalle = array();
        $anios = 0;
        $indemnizacion = 0;
        $vacaciones = 0;
        $montoVacaciones = 0;
        $montoGratificacion = 0;
        $sueldosVariables = array();
        $promedioSueldos = 0;
        $detalle = array();
        $meses = 0;
        $prestamos = $trabajador->cuotasFiniquito();
        
        $datosTrabajador = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rut' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto()
        );
        
        $plantillasFiniquitos = PlantillaFiniquito::listaPlantillasFiniquito();
        
        if($datos['indemnizacion'] || $datos['mesAviso'] || $datos['vacaciones']){
                        
            if($datos['sueldoNormal']){
                $meses = 1;
                $fecha = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes)));        
                $mesAnterior = MesDeTrabajo::where('mes', $fecha)->first();
                $mes = $mesAnterior['mes'];
                $liquidacion = Liquidacion::where('trabajador_id', $idTrabajador)->where('mes', $mes)->first();
                
                if($liquidacion){
                    $sueldo = $liquidacion->sueldo;
                    $gratificacion = $liquidacion->gratificacion;
                    $detalles = DetalleLiquidacion::where('liquidacion_id', $liquidacion->id)->get();
                                        
                    $montoSueldo = $sueldo;

                    if($detalles){
                        foreach($detalles as $det){
                            if($det['tipo_id']==1){
                                if($det['tipo']=='imponible'){
                                    $imponibles[] = array(
                                        'id' => $det['id'],
                                        'nombre' => $det['nombre'],
                                        'monto' => $det['valor']
                                    );
                                    $sumaImponibles = ($sumaImponibles + $det['valor']);

                                }else if($det['tipo']=='no imponible' && $datos['noImponibles']){
                                    $noImponibles[] = array(
                                        'id' => $det['id'],
                                        'nombre' => $det['nombre'],
                                        'monto' => $det['valor']
                                    );
                                    $sumaNoImponibles = ($sumaNoImponibles + $det['valor']);
                                }
                            }
                        }                                            
                    }
                    
                    if($datos['mesAviso']){
                        $rentaImponible = ($sumaImponibles + $sueldo + $gratificacion);
                        $rentaImponible = $liquidacion->sueldo_base;
                        if($datos['gratificacionMesAviso']){
                            $rentaImponible += $liquidacion->gratificacion;
                        }
                        if($rentaImponible > $tope){
                            $rentaImponible = $tope;
                        }
                    }else{
                        $rentaImponible = 0;
                    }
                    
                    
                    $detalle[] = array(
                        'mes' => $mesAnterior['nombre'] . ' ' . $mesAnterior->anioRemuneracion->anio,
                        'imponibles' => array(
                            'sueldo' => array(
                                'monto' => $sueldo
                            ),
                            'gratificacion' => array(
                                'monto' => $gratificacion,
                            ),
                            'haberes' => array(
                                'haberes' => $imponibles,
                                'suma' => $sumaImponibles,
                            ),
                            'rentaImponible' => array(
                                'monto' => $rentaImponible
                            )
                        ),
                        'noImponibles' => array(
                            'haberes' => array(
                                'haberes' => $noImponibles,
                                'suma' => $sumaNoImponibles,
                            ),
                            'suma' => $sumaNoImponibles
                        ),                        
                    );
                    $totalImponibles = ($sumaImponibles + $sueldo + $gratificacion);
                    $totalImponibles = $liquidacion->sueldo_base;
                    $totalImponiblesVacaciones = $totalImponibles;
                    if($totalImponibles>$tope){
                        $totalImponibles = $tope;
                    }
                    $totalNoImponibles = $sumaNoImponibles;
                }else{
                                        
                    $respuesta=array(
                        'success' => false,
                        'trabajador' => $datosTrabajador,
                        'mensaje' => 'El Trabajador no posee liquidación en el mes anterior.',
                        'mensaje2' => 'Por favor genere su Liquidación para poder calcular el Finiquito.',
                        'errores' => ''
                    ); 
                    
                    return Response::json($respuesta);
                }
                
            }else{
                $meses = $datos['meses']['id'];                    
                $mesPromediar = $mes;
                $sumaSueldos = 0;
                $sumaGratificacion = 0;
                $sumaImponibles = 0;
                $sumaNoImponibles = 0;
                $liquidaciones = array();
                $errores = array();
                
                for($i=1; $i<=$meses; $i++){
                    $mesPromediar = date('Y-m-d', strtotime('-' . $i . ' month', strtotime($mes)));
                    $anio = substr($mesPromediar, 0, 4);
                    $numeroMesPromediar = substr($mesPromediar, 5, 2);
                    $mesDeTrabajo = MesDeTrabajo::where('mes', $mesPromediar)->first();                    
                    $liquidacion = Liquidacion::where('trabajador_id', $idTrabajador)->where('mes', $mesPromediar)->first();
                    if($mesDeTrabajo){
                        $anio = $mesDeTrabajo->anioRemuneracion->anio;
                        if($liquidacion){
                            $liquidacion->mes = Funciones::obtenerMesTexto($numeroMesPromediar) . ' ' . $anio;
                            $liquidaciones[] = $liquidacion;
                        }else{
                            $errores[] = array(
                                'mes' => $mesDeTrabajo['nombre'] . ' ' . $mesDeTrabajo->anioRemuneracion->anio
                            );
                        }
                    }else{                        
                        $erroresSistema[] = array(
                            'mes' => Funciones::obtenerMesTexto($numeroMesPromediar) . ' ' . $anio
                        );

                        $respuesta=array(
                            'success' => false,
                            'trabajador' => $datosTrabajador,
                            'mensaje' => 'No se ha iniciado el siguiente mes en el sistema:',
                            'mensaje2' => 'Para generar el finiquito debe escoger promediar los meses posteriores a esa fecha.',
                            'errores' => $erroresSistema
                        ); 
                        
                        return Response::json($respuesta);
                    }
                    
                }
                $liquidaciones = array_reverse($liquidaciones);
                if(!$errores){
                    foreach($liquidaciones as $liquidacion){
                        $gratificacion = $liquidacion['gratificacion'];
                        $sueldo = $liquidacion['sueldo'];
                        $detalles = DetalleLiquidacion::where('liquidacion_id', $liquidacion->id)->get();
                        $imponibles = array();
                        $noImponibles = array();
                        $sumaNoImponibles = 0;
                        $sumaImponibles = 0;
                        
                        if($detalles){
                            foreach($detalles as $det){                                
                                if($det['tipo_id']==1){
                                    if($det['tipo']=='imponible'){
                                        $imponibles[] = array(
                                            'id' => $det['id'],
                                            'nombre' => $det['nombre'],
                                            'monto' => $det['valor']
                                        );
                                        $sumaImponibles = ($sumaImponibles + $det['valor']);
                                    }else if($det['tipo']=='no imponible' && $datos['noImponibles']){
                                        $noImponibles[] = array(
                                            'id' => $det['id'],
                                            'nombre' => $det['nombre'],
                                            'monto' => $det['valor']
                                        );
                                        $sumaNoImponibles = ($sumaNoImponibles + $det['valor']);
                                    }
                                }
                            }
                        }
                        $rentaImponible = ($sumaImponibles + $sueldo + $gratificacion);
                        $sueldoBase = $liquidacion->sueldo_base;
                        if($sueldoBase > $tope){
                            $sueldoBase = $tope;
                        }
                        $detalle[] = array(
                            'mes' => $liquidacion['mes'],
                            'imponibles' => array(
                                'sueldo' => array(
                                    'monto' => $sueldoBase
                                ),
                                'gratificacion' => array(
                                    'monto' => $gratificacion,
                                ),
                                'haberes' => array(
                                    'haberes' => $imponibles,
                                    'suma' => $sumaImponibles,
                                ),
                                'rentaImponible' => array(
                                    'monto' => $rentaImponible
                                )
                            ),
                            'noImponibles' => array(
                                'haberes' => array(
                                    'haberes' => $noImponibles,
                                    'suma' => $sumaNoImponibles,
                                ),
                                'suma' => $sumaNoImponibles
                            )
                        );
                        $sumaSueldos = ($sumaSueldos + $sueldo);
                        $sumaGratificacion = ($sumaGratificacion + $gratificacion);
                        $totalImponibles = ($totalImponibles + $rentaImponible);
                        $totalImponibles = $liquidacion->sueldo_base;
                        $totalImponiblesVacaciones = $totalImponibles;
                        $totalNoImponibles = ($totalNoImponibles + $sumaNoImponibles);
                    }
                    
                    if($datos['mesAviso']){
                        $rentaImponible = ($sumaImponibles + $sueldo + $gratificacion);
                        $rentaImponible = $trabajador->sueldoBase();
                        $sueldoBase = $trabajador->sueldoBase();
                        if($datos['gratificacionMesAviso']){
                            $rentaImponible += $gratificacion;
                        }
                        if($sueldoBase > $tope){
                            $sueldoBase = $tope;
                        }
                    }else{
                        $sueldoBase = 0;
                    }
                    
                    $montoSueldo = round($sumaSueldos / $meses);
                    $montoGratificacion = round($sumaGratificacion / $meses);
                    $totalImponibles = round($totalImponibles / $meses);
                    $totalNoImponibles = round($totalNoImponibles / $meses); 
                }else{
                    $respuesta=array(
                        'success' => false,
                        'trabajador' => $datosTrabajador,
                        'mensaje' => 'El Trabajador no posee liquidación los siguientes meses:',
                        'mensaje2' => 'Por favor genere su Liquidación para poder calcular el Finiquito.',
                        'errores' => $errores
                    ); 
                    
                    return Response::json($respuesta);
                }      
            }
            
            if($datos['indemnizacion']){
                $fechaReconocimiento = new DateTime($empleado->fecha_reconocimiento);
                $fechaTermino = new DateTime($datos['fecha']);
                $diferencia = $fechaReconocimiento->diff($fechaTermino);
                $anios = $diferencia->y;

                if($diferencia->m >= 6){
                    $anios = $anios + 1;
                }

                if($anios>11){
                    if($datos['tope']['id']!=21){
                        $anios = $datos['tope']['id'];
                    }
                }
                if($datos['gratificacionIndemnizacion']){
                    $totalImponibles += $gratificacion;
                }
                if($totalImponibles > $tope){
                    $totalImponibles = $tope;
                }
                $indemnizacion = ($anios * $totalImponibles);                    
            }

            if($datos['vacaciones']){
                $sueldoVacaciones = $trabajador->sueldoBase();
                if($datos['vacacionesManual']){
                    $vacaciones = $datos['diasVacaciones'];
                }else{
                    $vacacionesDetalle = $trabajador->misVacacionesFiniquito($datos['fecha']);   
                    $vacaciones = $vacacionesDetalle['total'];
                    $vacacionesDetalle['totalProporcional'] = round(($sueldoVacaciones / 30 ) * $vacacionesDetalle['feriadoProporcional']['diasF']);
                    $vacacionesDetalle['totalNormal'] = round(($sueldoVacaciones / 30 ) * $vacacionesDetalle['dias']);
                }
                $montoVacaciones = round(($sueldoVacaciones / 30 ) * $vacaciones);
            }      
            $sueldoBase = 0;
            if($datos['indemnizacion'] || $datos['mesAviso']){
                $sueldoBase = $trabajador->sueldoBase();
            }
            if($sueldoBase > $tope){
                $sueldoBase = $tope;
            }
        }else{
            $sueldoBase = 0;
            $rentaImponible = 0;
        }
        
        $respuesta=array(
            'success' => true,
            'trabajador' => $datosTrabajador,
            'plantillasFiniquitos' => $plantillasFiniquitos,
            'mesAviso' => $datos['mesAviso'],
            'sueldoNormal' => $datos['sueldoNormal'],
            'sueldoVariable' => $datos['sueldoVariable'],
            'meses' => $meses,
            'noImponibles' => array(
                'noImponibles' => $datos['noImponibles'],
                'suma' => $totalNoImponibles
            ),
            'imponibles' => array(
                'suma' => $rentaImponible,
                'sumas' => $rentaImponible
            ),
            'indemnizacion' => array(
                'indemnizacion' => $datos['indemnizacion'],
                'monto' => $indemnizacion,
                'anios' => $anios
            ),
            'vacaciones' => array(
                'detalle' => $vacacionesDetalle,
                'vacaciones' => $datos['vacaciones'],
                'dias' => $vacaciones,
                'monto' => $montoVacaciones,
                'checkVacaciones' => true,
                'checkFeriado' => true
            ),
            'prestamos' => array(
                'monto' => $prestamos
            ),
            'fecha' => $datos['fecha'],
            'idCausal' => $datos['idCausal'],
            'detalle' => $detalle,
            'totalImponibles' => $totalImponibles,
            'tope' => $tope
        );     
  
        return Response::json($respuesta);
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
        $finiquito = Finiquito::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Finiquito::errores($datos);       
        
        if(!$errores and $finiquito){
            $finiquito->causal_finiquito_id = $datos['causal_finiquito_id'];
            $finiquito->fecha = $datos['fecha'];
            $finiquito->vacaciones = $datos['vacaciones'];
            $finiquito->sueldo_normal = $datos['sueldo_normal'];
            $finiquito->sueldo_variable = $datos['sueldo_variable'];
            $finiquito->mes_aviso = $datos['mes_aviso'];
            $finiquito->indemnizacion = $datos['indemnizacion'];
            $finiquito->recibido = false;
            $finiquito->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $finiquito->sid
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
        $finiquito = Finiquito::whereSid($sid)->first();
        $idTrabajador = $finiquito->trabajador_id;
        $trabajador = Trabajador::find($idTrabajador);
        $ficha = $trabajador->ficha();
        $ficha->fecha_finiquito = null;
        $ficha->estado = 'Ingresado';
        $ficha->save();
        $documento = Documento::find($finiquito->documento_id);
        
        Logs::crearLog('#finiquitar-trabajador', $documento->id, $documento->alias, 'Delete', $documento->trabajador_id, $ficha->nombreCompleto(), NULL);
        
        $documento->eliminarDocumento();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'causal_finiquito_id' => Input::get('idCausalFiniquito'),
            'vacaciones' => Input::get('vacaciones'),
            'fecha' => Input::get('fecha'),
            'sueldo_normal' => Input::get('sueldoNormal'),
            'sueldo_variable' => Input::get('sueldoVariable'),
            'mes_aviso' => Input::get('mesAviso'),
            'indemnizacion' => Input::get('indemnizacion')
        );
        return $datos;
    }

}
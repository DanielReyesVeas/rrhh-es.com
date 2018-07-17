<?php

class AportesController extends \BaseController {
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
        $empresa = \Session::get('empresa');
        
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#gestion-cuentas');
        $aportes = Aporte::all();
        $cuentas = Cuenta::listaCuentas();
        $listaCentrosCostos = CentroCosto::listaCentrosCostoCuentas();
        $listaAportes = array();
        $listaHaberesImp = array();
        $listaHaberesNoImp = array();
        $listaDescuentos = array();
        $listaDescuentosLegales = array();
        $listaApvsA = array();
        $listaApvsB = array();
        $listaApvcs = array();
        $listaCCAFs = array();
        $listaAfpsEmpleador = array();
        $listaAfpsTrabajador = array();
        $listaSalud = array();
        $listaSeguroCesantiaEmpleador = array();
        $listaSeguroCesantiaTrabajador = array();
        $listaCuentasAhorroAfps = array();
        $listaExCajas = array();
        $listaGenerales = array();
        $haberes = TipoHaber::orderBy('codigo')->get();
        $descuentos = TipoDescuento::orderBy('codigo')->get();
        
        if( $haberes->count() ){
            foreach( $haberes as $haber ){
                if($haber->imponible){
                    $listaHaberesImp[]=array(
                        'id' => $haber->id,
                        'sid' => $haber->sid,
                        'cuenta' => $haber->cuenta($cuentas),
                        'nombre' => $haber->nombre
                    );
                }else{
                    $listaHaberesNoImp[]=array(
                        'id' => $haber->id,
                        'sid' => $haber->sid,
                        'cuenta' => $haber->cuenta($cuentas),
                        'nombre' => $haber->nombre
                    );
                }
            }
        }
        
        if( $descuentos->count() ){
            foreach( $descuentos as $descuento ){
                if($descuento->estructura_descuento_id==4){
                    $listaApvsA[]=array(
                        'id' => $descuento->id,
                        'sid' => $descuento->sid,
                        'cuenta' => $descuento->cuenta($cuentas),
                        'nombre' => 'APV Régimen A AFP ' . $descuento->nombreAfp()
                    );
                }else if($descuento->estructura_descuento_id==5){
                    $listaApvsB[]=array(
                        'id' => $descuento->id,
                        'sid' => $descuento->sid,
                        'cuenta' => $descuento->cuenta($cuentas),
                        'nombre' => 'APV Régimen B AFP ' . $descuento->nombreAfp()
                    );
                }else if($descuento->estructura_descuento_id==6){
                    if($empresa->mutual_id!=257){
                        $listaCCAFs[]=array(
                            'id' => $descuento->id,
                            'sid' => $descuento->sid,
                            'cuenta' => $descuento->cuenta($cuentas),
                            'nombre' => $descuento->nombre
                        );
                    }
                }else if($descuento->estructura_descuento_id==3){
                    $listaApvcs[]=array(
                        'id' => $descuento->id,
                        'sid' => $descuento->sid,
                        'cuenta' => $descuento->cuenta($cuentas),
                        'nombre' => 'APVC ' . $descuento->nombreAfp()
                    );
                }else if($descuento->estructura_descuento_id==7){
                    $listaCuentasAhorroAfps[]=array(
                        'id' => $descuento->id,
                        'sid' => $descuento->sid,
                        'cuenta' => $descuento->cuenta($cuentas),
                        'nombre' => 'Cuenta de Ahorro AFP ' . $descuento->nombreAfp()
                    );
                }else if($descuento->estructura_descuento_id==8){
                    $listaDescuentosLegales[]=array(
                        'id' => $descuento->id,
                        'sid' => $descuento->sid,
                        'cuenta' => $descuento->cuenta($cuentas),
                        'nombre' => $descuento->nombre
                    );
                }else if($descuento->estructura_descuento_id==9){
                    $listaSalud[]=array(
                        'id' => $descuento->id,
                        'sid' => $descuento->sid,
                        'cuenta' => $descuento->cuenta($cuentas),
                        'a' => $descuento,
                        'nombre' => $descuento->nombreIsapre()
                    );
                }else if($descuento->estructura_descuento_id<3 || $descuento->estructura_descuento_id==10){
                    $listaDescuentos[]=array(
                        'id' => $descuento->id,
                        'sid' => $descuento->sid,
                        'cuenta' => $descuento->cuenta($cuentas),
                        'nombre' => $descuento->nombre
                    );
                }
            }
        }
    
        if( $aportes->count() ){
            foreach( $aportes as $aporte ){
                if($aporte->tipo_aporte==1){
                    if($empresa->mutual_id==263){
                        if($aporte->id==1){
                            $listaAportes[]=array(
                                'id' => $aporte->id,
                                'sid' => $aporte->sid,
                                'cuenta' => $aporte->cuenta($cuentas),
                                'nombre' => $aporte->nombre
                            );
                        }
                    }else{
                        if($aporte->id==2){
                            $listaAportes[]=array(
                                'id' => $aporte->id,
                                'sid' => $aporte->sid,
                                'cuenta' => $aporte->cuenta($cuentas),
                                'nombre' => $aporte->nombre
                            );
                        }
                    }
                }else if($aporte->tipo_aporte==2){
                    $listaAfpsEmpleador[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => 'SIS AFP ' . $aporte->afp()
                    );
                }else if($aporte->tipo_aporte==4){
                    $listaAfpsTrabajador[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => 'Aporte Trabajador AFP ' . $aporte->afp()
                    );
                }else if($aporte->tipo_aporte==3){
                    $listaExCajas[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => 'Ex-Caja ' . $aporte->exCaja()
                    );
                }else if($aporte->tipo_aporte==5){
                    $listaSeguroCesantiaTrabajador[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => 'Seguro Cesantía Trabajador AFP ' . $aporte->afp()
                    );
                }else if($aporte->tipo_aporte==6){
                    $listaSeguroCesantiaEmpleador[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => 'Seguro Cesantía Empleador AFP ' . $aporte->afp()
                    );
                }else if($aporte->tipo_aporte>6){
                    $listaGenerales[]=array(
                        'id' => $aporte->id,
                        'sid' => $aporte->sid,
                        'cuenta' => $aporte->cuenta($cuentas),
                        'nombre' => $aporte->nombre
                    );
                }
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'abierto' => true,
                'ver' => true,
                'crear' => true,
                'editar' => true,
                'eliminar' => true
            ),
            'aportes' => $listaAportes,
            'generales' => $listaGenerales,
            'afpsEmpleador' => $listaAfpsEmpleador,
            'afpsTrabajador' => $listaAfpsTrabajador,
            'seguroCesantiaTrabajador' => $listaSeguroCesantiaTrabajador,
            'seguroCesantiaEmpleador' => $listaSeguroCesantiaEmpleador,
            'cuentasAhorroAfps' => $listaCuentasAhorroAfps,
            'exCajas' => $listaExCajas,
            'haberesImp' => $listaHaberesImp,
            'haberesNoImp' => $listaHaberesNoImp,
            'descuentos' => $listaDescuentos,
            'descuentosLegales' => $listaDescuentosLegales,
            'apvsA' => $listaApvsA,
            'apvsB' => $listaApvsB,
            'apvcs' => $listaApvcs,
            'ccafs' => $listaCCAFs,
            'salud' => $listaSalud,
            'centrosCostos' => $listaCentrosCostos
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
        $errores = Aporte::errores($datos);      
        
        if(!$errores){
            $aporte = new Aporte();
            $aporte->sid = Funciones::generarSID();
            $aporte->nombre = $datos['nombre'];
            $aporte->cuenta_id = $datos['cuenta_id'];
            $aporte->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $aporte->sid
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
        $aporte = Aporte::whereSid($sid)->first();
        $cuentas = Cuenta::listaCuentas();
        
        if($aporte->tipo_aporte==2){
            $nombre = 'SIS AFP ' . $aporte->afp();
        }else if($aporte->tipo_aporte==4){
            $nombre = 'Aporte Trabajador AFP ' . $aporte->afp();
        }else if($aporte->tipo_aporte==5){
            $nombre = 'Seguro Cesantía Trabajador AFP ' . $aporte->afp();
        }else if($aporte->tipo_aporte==6){
            $nombre = 'Seguro Cesantía Empleador AFP ' . $aporte->afp();
        }else{
            $nombre = $aporte->nombre;
        }
        $datosAporte=array(
            'id' => $aporte->id,
            'sid' => $aporte->sid,
            'nombre' => $nombre,
            'cuenta' => $aporte->cuenta($cuentas)
        );
        
        $datos = array(
            'datos' => $datosAporte,
            'cuentas' => array_values($cuentas)
        );
        
        return Response::json($datos);
    }
    
    public function cuentaAporteCentroCosto($sid)
    {
        $aporte = Aporte::whereSid($sid)->first();
        $cuentas = Cuenta::listaCuentas();
        
        if($aporte->tipo_aporte==2){
            $nombre = 'SIS AFP ' . $aporte->afp();
        }else if($aporte->tipo_aporte==4){
            $nombre = 'Aporte Trabajador AFP ' . $aporte->afp();
        }else if($aporte->tipo_aporte==5){
            $nombre = 'Seguro Cesantía Trabajador AFP ' . $aporte->afp();
        }else if($aporte->tipo_aporte==6){
            $nombre = 'Seguro Cesantía Empleador AFP ' . $aporte->afp();
        }else{
            $nombre = $aporte->nombre;
        }
        
        $listaCentrosCostos = CentroCosto::listaCentrosCostoCuentas($aporte->id, 'aporte', true, $cuentas);
        
        $datosAporte=array(
            'id' => $aporte->id,
            'sid' => $aporte->sid,
            'nombre' => $nombre
        );
        
        $datos = array(
            'datos' => $datosAporte,
            'cuentas' => array_values($cuentas),
            'centrosCostos' => $listaCentrosCostos
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
        $aporte = Aporte::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Aporte::errores($datos);       
        
        if(!$errores and $aporte){
            $aporte->cuenta_id = $datos['cuenta_id'];
            $aporte->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $aporte->sid
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
    
    public function updateCuenta()
    {
        $datos = Input::all();
        $aporte = Aporte::whereSid($datos['sid'])->first();
        $cuenta = NULL;
        if(isset($datos['cuenta'])){
            $cuenta = $datos['cuenta']['id'];
        }
        $aporte->cuenta_id = $cuenta;      
        $aporte->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $aporte->sid
        );
        
        return Response::json($respuesta);
    }
    
    public function updateCuentaCentroCosto()
    {
        $datos = Input::all();
        
        $ccc = CuentaCentroCosto::where('concepto_id', $datos['idConcepto'])->where('concepto', $datos['concepto'])->get();
        
        if($ccc->count()){
            foreach($ccc as $c){
                $c->delete();
            }
        }
        
        foreach($datos['centrosCosto'] as $dato){
            if($dato['cuenta']){
                $cuentaCentroCosto = new CuentaCentroCosto();
                $cuentaCentroCosto->centro_costo_id = $dato['id'];
                $cuentaCentroCosto->cuenta_id = $dato['cuenta']['id'];
                $cuentaCentroCosto->concepto_id = $datos['idConcepto'];
                $cuentaCentroCosto->concepto = $datos['concepto'];
                $cuentaCentroCosto->save();
            }
        }

        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'ccc' => $ccc
        );
        
        return Response::json($respuesta);
    }
    
    public function updateCuentaMasivo()
    {
        $datos = Input::all();
        $sid = $datos['sid'];
        $idCuenta = $datos['idCuenta'];
        
        $aportes = Aporte::whereIn('sid', $sid)->get();
        
        if($aportes->count()){
            foreach($aportes as $aporte){
                $aporte->cuenta_id = $idCuenta;
                $aporte->save();
            }
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
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
        Aporte::whereSid($sid)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'nombre' => Input::get('nombre'),
            'cuenta_id' => Input::get('cuenta')['id']
        );
        return $datos;
    }

}
<?php

class ApvsController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $apvs = Apv::all();
        $listaApvs=array();
        if( $apvs->count() ){
            foreach( $apvs as $apv ){
                $listaApvs[]=array(
                    'id' => $apv->id,
                    'sid' => $apv->sid,
                    'idTrabajador' => $apv->trabajador_id,
                    'idAfp' => $apv->afp_id,
                    'moneda' => $apv->moneda,
                    'monto' => $apv->monto
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaApvs
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
        $errores = Apv::errores($datos);      
        
        if(!$errores){
            $apv = new Apv();
            $apv->sid = Funciones::generarSID();
            $apv->trabajador_id = $datos['trabajador_id'];
            $apv->afp_id = $datos['afp_id'];
            $apv->forma_pago = $datos['forma_pago_id'];
            $apv->regimen = $datos['regimen'];
            $apv->fecha_pago_desde = $datos['fecha_pago_desde'];
            $apv->fecha_pago_hasta = $datos['fecha_pago_hasta'];
            $apv->moneda = $datos['moneda'];
            $apv->monto = $datos['monto'];
            $apv->save();
            
            if($apv->moneda=='$'){
                $monto = $apv->moneda . $apv->monto;
            }else{
                $monto = $apv->monto . $apv->moneda;
            }
            $trabajador = $apv->trabajador;
            $ficha = $trabajador->ficha();

            /// se tiene que ingresar a la tabla de descuentos (si no existe) para asignar cuenta contable
            if(strtolower($apv->regimen)=='a'){
                $descuento = TipoDescuento::where('estructura_descuento_id', 4)->where('nombre', $apv->afp_id)->first();
            }else{
                $descuento = TipoDescuento::where('estructura_descuento_id', 5)->where('nombre', $apv->afp_id)->first();
            }
            if( !$descuento ){
                $codigo = (DB::table('tipos_descuento')->max('codigo') + 1);
                $descuento = new TipoDescuento();
                $descuento->estructura_descuento_id = strtolower($apv->regimen)=='a'? 4 : 5;
                $descuento->nombre = $apv->afp_id;
                $descuento->sid = Funciones::generarSID();
                $descuento->codigo = $codigo;
                $descuento->caja=0;
                $descuento->descripcion = "APV Régimen " . strtoupper($apv->regimen) . " AFP ".$apv->afp->glosa;
                $descuento->save();
            }

            Logs::crearLog('#apvs', $apv->trabajador_id, $ficha->nombreCompleto(), 'Create', $apv->id, $monto, NULL);
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $apv->sid
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#apvs');
        $datosApv = null;
        $afps = Glosa::listaAfpsApvs();
        $formasPago = Glosa::listaFormasPago();
        $trabajadores = array();
        
        if($sid){
            $apv = Apv::whereSid($sid)->first();
            $datosApv = array(
                'id' => $apv->id,
                'sid' => $apv->sid,
                'numeroContrato' => $apv->numero_contrato,
                'afp' => array(
                    'id' => $apv->afp ? $apv->afp->id : '',
                    'nombre' => $apv->afp ? $apv->afp->nombre : ''
                ),
                'formaPago' => $apv->forma_pago,
                'monto' => $apv->monto,
                'regimen' => strtoupper($apv->regimen),
                'moneda' => $apv->moneda,
                'fechaPagoDesde' => $apv->fecha_pago_desde,
                'fechaPagoHasta' => $apv->fecha_pago_hasta,
                'trabajador' => $apv->trabajador
            );
        }else{
            $trabajadores = Trabajador::activosFiniquitados();
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosApv,
            'afps' => $afps,
            'trabajadores' => $trabajadores,
            'formasPago' => $formasPago
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
        $apv = Apv::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Apv::errores($datos);       
        
        if(!$errores and $apv){
            $apv->afp_id = $datos['afp_id'];
            $apv->forma_pago = $datos['forma_pago_id'];
            $apv->regimen = $datos['regimen'];
            $apv->fecha_pago_desde = $datos['fecha_pago_desde'];
            $apv->fecha_pago_hasta = $datos['fecha_pago_hasta'];
            $apv->moneda = $datos['moneda'];
            $apv->monto = $datos['monto'];
            $apv->save();

            if($apv->moneda=='$'){
                $monto = $apv->moneda . $apv->monto;
            }else{
                $monto = $apv->monto . $apv->moneda;
            }
            $trabajador = $apv->trabajador;
            $ficha = $trabajador->ficha();

            /// se tiene que ingresar a la tabla de descuentos (si no existe) para asignar cuenta contable
            if(strtolower($apv->regimen)=='a'){
                $descuento = TipoDescuento::where('estructura_descuento_id', 4)->where('nombre', $apv->afp_id)->first();
            }else{
                $descuento = TipoDescuento::where('estructura_descuento_id', 5)->where('nombre', $apv->afp_id)->first();
            }
            if( !$descuento ){
                $codigo = (DB::table('tipos_descuento')->max('codigo') + 1);
                $descuento = new TipoDescuento();
                $descuento->estructura_descuento_id = strtolower($apv->regimen)=='a'? 4 : 5;
                $descuento->nombre = $apv->afp_id;
                $descuento->sid = Funciones::generarSID();
                $descuento->codigo = $codigo;
                $descuento->caja=0;
                $descuento->descripcion = "APV Régimen " . strtoupper($apv->regimen) . " AFP ".$apv->afp->glosa;
                $descuento->save();
            }

            Logs::crearLog('#apvs', $apv->trabajador_id, $ficha->nombreCompleto(), 'Update', $apv->id, $monto, NULL);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $apv->sid
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
        $apv = Apv::whereSid($sid)->first();
        
        if($apv['moneda']=='$'){
            $monto = $apv['moneda'] . $apv['monto'];
        }else{
            $monto = $apv['monto'] . $apv['moneda'];
        }
        $trabajador = $apv->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#apvs', $apv['trabajador_id'], $ficha->nombreCompleto(), 'Delete', $apv['id'], $monto, NULL);
        
        $apv->delete();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'trabajador_id' => Input::get('idTrabajador'),
            'afp_id' => Input::get('afp')['id'],
            'regimen' => Input::get('regimen'),
            'numero_contrato' => Input::get('numeroContrato'),
            'forma_pago_id' => Input::get('formaPago')['id'],
            'trabajador_id' => Input::get('trabajador')['id'],
            'fecha_pago_desde' => Input::get('fechaPagoDesde'),
            'fecha_pago_hasta' => Input::get('fechaPagoHasta'),
            'moneda' => Input::get('moneda'),
            'monto' => Input::get('monto')
        );
        return $datos;
    }

}
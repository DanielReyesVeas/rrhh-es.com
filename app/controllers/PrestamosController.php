<?php

class PrestamosController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $prestamos = Prestamo::all();
        $listaPrestamos=array();
        if( $prestamos->count() ){
            foreach( $prestamos as $prestamo ){
                $listaPrestamos[]=array(
                    'id' => $prestamo->id,
                    'sid' => $prestamo->sid,
                    'idTrabajador' => $prestamo->trabajador_id,
                    //'fecha' => $prestamo->fecha,
                    'glosa' => $prestamo->glosa,
                    'nombreLiquidacion' => $prestamo->nombre_liquidacion,
                    //'prestamoCaja' => $prestamo->prestamo_caja ? true : false,
                    //'leassingCaja' => $prestamo->leassing_caja ? true : false,
                    'moneda' => $prestamo->moneda,
                    'monto' => $prestamo->monto,
                    'cuotas' => $prestamo->cuotas,
                    'primeraCuota' => $prestamo->primera_cuota,
                    'ultimaCuota' => $prestamo->ultima_cuota
                );
            }
        }
        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaPrestamos
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
        $errores = Prestamo::errores($datos);      
        
        if(!$errores){
            $prestamo = new Prestamo();
            $prestamo->sid = Funciones::generarSID();
            $prestamo->trabajador_id = $datos['trabajador_id'];
            //$prestamo->fecha = $datos['fecha'];
            $prestamo->glosa = $datos['glosa'];
            $prestamo->codigo = $datos['codigo'];
            $prestamo->nombre_liquidacion = $datos['nombre_liquidacion'];
            $prestamo->prestamo_caja = $datos['prestamo_caja'];
            $prestamo->leassing_caja = $datos['leassing_caja'];
            $prestamo->moneda = $datos['moneda'];
            $prestamo->monto = $datos['monto'];
            $prestamo->cuotas = $datos['cuotas'];
            $prestamo->primera_cuota = $datos['primera_cuota'];
            $prestamo->ultima_cuota = $datos['ultima_cuota'];
            $prestamo->save();
            
            if($prestamo->moneda=='$'){
                $monto = $prestamo->moneda . $prestamo->monto;
            }else{
                $monto = $prestamo->monto . $prestamo->moneda;
            }
            
            $trabajador = $prestamo->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#ingreso-prestamos', $trabajador->id, $ficha->nombreCompleto(), 'Create', $prestamo->id, $monto, NULL);
            
            foreach($datos['detalle_cuotas'] as $cuota)
            {
                $nuevaCuota = new Cuota();
                $nuevaCuota->sid = Funciones::generarSID();
                $nuevaCuota->prestamo_id = $prestamo->id;
                $nuevaCuota->monto = $cuota['monto'];
                $nuevaCuota->moneda = $prestamo->moneda;
                $nuevaCuota->mes = $cuota['mes'];
                $nuevaCuota->numero = $cuota['numero'];
                $nuevaCuota->save();
            }
            
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $prestamo->sid
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
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-prestamos');
        $datosPrestamo = null;
        $trabajadores = array();
        
        if($sid){
            $prestamo = Prestamo::whereSid($sid)->first();
            $datosPrestamo=array(
                'id' => $prestamo->id,
                'sid' => $prestamo->sid,
                //'fecha' => $prestamo->fecha,
                'primeraCuota' => $prestamo->primera_cuota,
                'glosa' => $prestamo->glosa,
                'codigo' => $prestamo->codigo,
                'nombreLiquidacion' => $prestamo->nombre_liquidacion,
                'prestamoCaja' => $prestamo->prestamo_caja ? true : false,
                'leassingCaja' => $prestamo->leassing_caja ? true : false,
                'moneda' => $prestamo->moneda,
                'monto' => $prestamo->monto,
                'cuotas' => $prestamo->cuotas,
                'primeraCuota' => $prestamo->primera_cuota,
                'ultimaCuota' => $prestamo->ultima_cuota,
                'trabajador' => $prestamo->trabajadorPrestamo(),
                'detalleCuotas' => $prestamo->cuotasPrestamo()
            );
        }else{
            $trabajadores = Trabajador::activosFiniquitados();
        }
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosPrestamo,
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
        $prestamo = Prestamo::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = Prestamo::errores($datos);       
        
        if(!$errores and $prestamo){
            $prestamo->trabajador_id = $datos['trabajador_id'];
            //$prestamo->fecha = $datos['fecha'];
            $prestamo->glosa = $datos['glosa'];
            $prestamo->codigo = $datos['codigo'];
            $prestamo->nombre_liquidacion = $datos['nombre_liquidacion'];
            $prestamo->prestamo_caja = $datos['prestamo_caja'];
            $prestamo->leassing_caja = $datos['leassing_caja'];
            $prestamo->moneda = $datos['moneda'];
            $prestamo->monto = $datos['monto'];
            $prestamo->cuotas = $datos['cuotas'];
            $prestamo->primera_cuota = $datos['primera_cuota'];
            $prestamo->ultima_cuota = $datos['ultima_cuota'];
            $prestamo->save();
            
            if($prestamo->moneda=='$'){
                $monto = $prestamo->moneda . $prestamo->monto;
            }else{
                $monto = $prestamo->monto . $prestamo->moneda;
            }
            
            $misCuotas = Cuota::where('prestamo_id', $prestamo->id)->get();
            
            if($misCuotas){
                foreach($misCuotas as $miCuota){
                    $miCuota->delete();
                }
            }
                
            foreach($datos['detalle_cuotas'] as $cuota){
                $nuevaCuota = new Cuota();
                $nuevaCuota->sid = Funciones::generarSID();
                $nuevaCuota->prestamo_id = $prestamo->id;
                $nuevaCuota->monto = $cuota['monto'];
                $nuevaCuota->moneda = $prestamo->moneda;
                $nuevaCuota->mes = $cuota['mes'];
                $nuevaCuota->numero = $cuota['numero'];
                $nuevaCuota->save();
            }
        
            $trabajador = $prestamo->trabajador;
            $ficha = $trabajador->ficha();
            Logs::crearLog('#ingreso-prestamos', $trabajador->id, $ficha->nombreCompleto(), 'Update', $prestamo->id, $monto, NULL);
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $prestamo->sid
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
        
        $prestamo = Prestamo::whereSid($sid)->first();
        
        if($prestamo->moneda=='$'){
            $monto = $prestamo->moneda . $prestamo->monto;
        }else{
            $monto = $prestamo->monto . $prestamo->moneda;
        }
        
        $trabajador = $prestamo->trabajador;
        $ficha = $trabajador->ficha();
        Logs::crearLog('#ingreso-prestamos', $trabajador->id, $ficha->nombreCompleto(), 'Delete', $prestamo['id'], $monto, NULL);
                
        $prestamo->eliminarPrestamo();
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'id' => Input::get('id'),
            'trabajador_id' => Input::get('idTrabajador'),
            //'fecha' => Input::get('fecha'),
            'glosa' => Input::get('glosa'),
            'codigo' => Input::get('codigo'),
            'nombre_liquidacion' => Input::get('nombreLiquidacion'),
            'prestamo_caja' => Input::get('prestamoCaja'),
            'leassing_caja' => Input::get('leassingCaja'),
            'moneda' => Input::get('moneda'),
            'monto' => Input::get('monto'),
            'cuotas' => Input::get('cuotas'),
            'detalle_cuotas' => Input::get('detalleCuotas'),
            'primera_cuota' => Input::get('primeraCuota'),
            'ultima_cuota' => Input::get('ultimaCuota')
        );
        return $datos;
    }

}
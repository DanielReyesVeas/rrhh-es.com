<?php

class TiposHoraExtraController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function index()
    {
        $tiposHoraExtra = TipoHoraExtra::all();
        $listaTiposHoraExtra=array();
        
        if( $tiposHoraExtra->count() ){
            foreach( $tiposHoraExtra as $tipoHoraExtra ){
                $listaTiposHoraExtra[]=array(
                    'id' => $tipoHoraExtra->id,
                    'sid' => $tipoHoraExtra->sid,
                    'codigo' => $tipoHoraExtra->codigo,
                    'nombre' => $tipoHoraExtra->nombre
                );
            }
        }
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $listaTiposHoraExtra
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
        $errores = TipoHoraExtra::errores($datos);      
        
        if(!$errores){
            $tipoHoraExtra = new TipoHoraExtra();
            $tipoHoraExtra->sid = Funciones::generarSID();
            $tipoHoraExtra->nombre = $datos['nombre'];
            $tipoHoraExtra->codigo = $datos['codigo'];
            $tipoHoraExtra->tributable = $datos['tributable'];
            $tipoHoraExtra->proporcional_dias_trabajados = $datos['proporcional_dias_trabajados'];
            $tipoHoraExtra->calcula_semana_corrida = $datos['calcula_semana_corrida'];
            $tipoHoraExtra->imponible = $datos['imponible'];
            $tipoHoraExtra->gratificacion = $datos['gratificacion'];
            $tipoHoraExtra->save();
            $respuesta=array(
            	'success' => true,
            	'mensaje' => "La Información fue almacenada correctamente",
            	'sid' => $tipoHoraExtra->sid
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
    
    
    public function cuentaHoraExtra($sid)
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-horas-extra');
        $datosHoraExtra = null;
        $cuentas = Cuenta::listaCuentas();
        
        if($sid){
            $tipoHoraExtra = TipoHoraExtra::whereSid($sid)->first();
            $datosHoraExtra=array(
                'id' => $tipoHoraExtra->id,
                'sid' => $tipoHoraExtra->sid,
                'codigo' => $tipoHoraExtra->codigo,
                'nombre' => $tipoHoraExtra->nombre,
                'tributable' => $tipoHoraExtra->tributable ? true : false,
                'calculaHorasExtras' => 3,
                'proporcionalDiasTrabajados' => $tipoHoraExtra->proporcional_dias_trabajados ? true : false,
                'calculaSemanaCorrida' => $tipoHoraExtra->calcula_semana_corrida ? true : false,
                'imponible' => $tipoHoraExtra->imponible ? true : false,
                'gratificacion' => $tipoHoraExtra->gratificacion ? true : false,
                'cuenta' => $tipoHoraExtra->cuenta(),
                'isHoraExtra' => true
            );
        }
        
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosHoraExtra,
            'cuentas' => array_values($cuentas)
        );
        
        return Response::json($datos);
    }
    
    public function cuentaHoraExtraCentroCosto($sid)
    {
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-horas-extra');
        $datosHoraExtra = null;
        $cuentas = Cuenta::listaCuentas();        
        
        if($sid){
            $tipoHoraExtra = TipoHoraExtra::whereSid($sid)->first();
            $datosHoraExtra=array(
                'id' => $tipoHoraExtra->id,
                'sid' => $tipoHoraExtra->sid,
                'codigo' => $tipoHoraExtra->codigo,
                'nombre' => $tipoHoraExtra->nombre,
                'tributable' => $tipoHoraExtra->tributable ? true : false,
                'calculaHorasExtras' => 3,
                'proporcionalDiasTrabajados' => $tipoHoraExtra->proporcional_dias_trabajados ? true : false,
                'calculaSemanaCorrida' => $tipoHoraExtra->calcula_semana_corrida ? true : false,
                'imponible' => $tipoHoraExtra->imponible ? true : false,
                'gratificacion' => $tipoHoraExtra->gratificacion ? true : false,
                'cuenta' => $tipoHoraExtra->cuenta(),
                'isHoraExtra' => true
            );
        }
        
        $centrosCostos = CentroCosto::listaCentrosCostoCuentas($tipoHoraExtra->id, 'horaExtra', true);        
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $datosHoraExtra,
            'cuentas' => array_values($cuentas),
            'centrosCostos' => $centrosCostos
        );
        
        return Response::json($datos);
    }
        
    public function updateCuenta()
    {
        $datos = Input::all();
        $horaExtra = TipoHoraExtra::whereSid($datos['sid'])->first();
        $cuenta = NULL;
        if(isset($datos['cuenta'])){
            $cuenta = $datos['cuenta']['id'];
        }
        $horaExtra->cuenta_id = $cuenta;      
        $horaExtra->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $horaExtra
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
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function updateCuentaMasivo()
    {
        $datos = Input::all();
        $sid = $datos['sid'];
        $idCuenta = $datos['idCuenta'];
        
        $horasExtra = TipoHoraExtra::whereIn('sid', $sid)->get();
        
        if($horasExtra->count()){
            foreach($horasExtra as $horaExtra){
                $horaExtra->cuenta_id = $idCuenta;
                $horaExtra->save();
            }
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
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
        $tipoHoraExtra = TipoHoraExtra::whereSid($sid)->first();

        $datosTipoHoraExtra=array(
            'id' => $tipoHoraExtra->id,
            'sid' => $tipoHoraExtra->sid,
            'codigo' => $tipoHoraExtra->codigo,
            'nombre' => $tipoHoraExtra->nombre,
            'tributable' => $tipoHoraExtra->tributable ? true : false,
            'calculaHorasExtras' => 3,
            'proporcionalDiasTrabajados' => $tipoHoraExtra->proporcional_dias_trabajados ? true : false,
            'calculaSemanaCorrida' => $tipoHoraExtra->calcula_semana_corrida ? true : false,
            'imponible' => $tipoHoraExtra->imponible ? true : false,
            'gratificacion' => $tipoHoraExtra->gratificacion ? true : false,
            'cuenta' => array(),
            'isEdit' => true,
            'isEditNombre' => false,
            'isHoraExtra' => true
        );        
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $datosTipoHoraExtra
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
        $tipoHoraExtra = TipoHoraExtra::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $errores = TipoHoraExtra::errores($datos);       
        
        if(!$errores and $tipoHoraExtra){
            $tipoHoraExtra->nombre = $datos['nombre'];
            $tipoHoraExtra->codigo = $datos['codigo'];
            $tipoHoraExtra->tributable = $datos['tributable'];
            $tipoHoraExtra->proporcional_dias_trabajados = $datos['proporcional_dias_trabajados'];
            $tipoHoraExtra->calcula_semana_corrida = $datos['calcula_semana_corrida'];
            $tipoHoraExtra->imponible = $datos['imponible'];
            $tipoHoraExtra->gratificacion = $datos['gratificacion'];
            $tipoHoraExtra->save();
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'sid' => $tipoHoraExtra->sid
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
        $tipoHoraExtra = TipoHoraExtra::whereSid($sid)->first();
        
        $errores = $tipoHoraExtra->comprobarDependencias();
        
        if(!$errores){
            Logs::crearLog('#ingreso-horas-extra', $tipoHoraExtra->id, $tipoHoraExtra->nombre, 'Delete', $tipoHoraExtra->codigo, NULL);       
            $tipoHoraExtra->delete();
            $datos = array(
                'success' => true,
                'mensaje' => "La Información fue eliminada correctamente"
            );
        }else{
            $datos = array(
                'success' => false,
                'errores' => $errores,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada"
            );
        }
        
        return Response::json($datos);
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'id' => Input::get('id'),
            'codigo' => Input::get('codigo'),
            'nombre' => Input::get('nombre'),
            'tributable' => Input::get('tributable'),
            'proporcional_dias_trabajados' => Input::get('proporcionalDiasTrabajados'),
            'calcula_semana_corrida' => Input::get('calculaSemanaCorrida'),
            'imponible' => Input::get('imponible'),
            'gratificacion' => Input::get('gratificacion')
        );
        return $datos;
    }

}
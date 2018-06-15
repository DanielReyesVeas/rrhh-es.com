<?php

class FichasTrabajadoresController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $listaSecciones=array();
        Seccion::listaSecciones($listaSecciones, 0, 1);
        $datosFicha = array();
        
		$datosFormulario=array(
			'nacionalidades' => Glosa::listaNacionalidades(),
			'estadosCiviles' => EstadoCivil::listaEstadosCiviles(),
			'cargos' => Cargo::listaCargos(),
			'tiendas' => Tienda::listaTiendas(),
			'centros' => CentroCosto::listaCentrosCosto(),
			'secciones' => $listaSecciones,
			'titulos' => Titulo::listaTitulos(),
			'tipos' => Glosa::listaTiposTrabajador(),
			'tiposCuentas' => TipoCuenta::listaTiposCuenta(),
			'bancos' => Banco::listaBancos(),
			'tiposContratos' => TipoContrato::listaTiposContrato(),
			'tiposJornadas' => Jornada::listaJornadas(),			
			'previsiones' => Glosa::listaPrevisiones(),
			'exCajas' => Glosa::listaExCajas(),
			'afps' => Glosa::listaAfps(),
			'afpsSeguro' => Glosa::listaAfpsSeguro(),
			'isapres' => Glosa::listaIsapres(),
            'zonas' => ZonaImpuestoUnico::listaZonas()
		);
        
        $ficha = FichaTrabajador::find($id);
        $trabajador = $ficha->trabajador;

        $datosFicha = array(
            'id' => $ficha->id,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombres' => $ficha->nombres,
            'apellidos' => $ficha->apellidos,
            'nombreCompleto' => $ficha->nombreCompleto(),
            'nacionalidad' => array(
                'id' => $ficha->nacionalidad ? $ficha->nacionalidad->id : "",
                'nombre' => $ficha->nacionalidad ? $ficha->nacionalidad->glosa : ""
            ),
            'sexo' => $ficha->sexo,
            'estadoCivil' => array(
                'id' => $ficha->estadoCivil ? $ficha->estadoCivil->id : "",
                'nombre' => $ficha->estadoCivil ? $ficha->estadoCivil->nombre : ""
            ),
            'fechaNacimiento' => $ficha->fecha_nacimiento,
            'direccion' => $ficha->direccion,
            'comuna' => array(
                'id' => $ficha->comuna ? $ficha->comuna->id : "",
                'nombre' => $ficha->comuna ? $ficha->comuna->localidad() : "",
                'comuna' => $ficha->comuna ? $ficha->comuna->comuna : "",
                'provincia' => $ficha->comuna ? $ficha->comuna->provincia->provincia : ""
            ), 
            'telefono' => $ficha->telefono,
            'celular' => $ficha->celular,
            'celularEmpresa' => $ficha->celular_empresa,
            'email' => $ficha->email,
            'emailEmpresa' => $ficha->email_empresa,
            'tipo' => array(
                'id' => $ficha->tipo_id,
                'nombre' => $ficha->tipo ? $ficha->tipo->nombre : ""
            ),
            'cargo' => array(
                'id' => $ficha->cargo ? $ficha->cargo->id : "",
                'nombre' => $ficha->cargo ? $ficha->cargo->nombre : ""
            ),
            'titulo' => array(
                'id' => $ficha->titulo ? $ficha->titulo->id : "",
                'nombre' => $ficha->titulo ? $ficha->titulo->nombre : ""
            ),
            'seccion' => array(
                'id' => $ficha->seccion ? $ficha->seccion->id : "",
                'nombre' => $ficha->seccion ? $ficha->seccion->nombre : ""
            ),
            'tienda' => array(
                'id' => $ficha->tienda ? $ficha->tienda->id : "",
                'nombre' => $ficha->tienda ? $ficha->tienda->nombre : ""
            ),
            'centroCosto' => array(
                'id' => $ficha->centroCosto ? $ficha->centroCosto->id : "",
                'nombre' => $ficha->centroCosto ? $ficha->centroCosto->nombre : ""
            ),
            'tipoCuenta' => array(
                'id' => $ficha->tipoCuenta ? $ficha->tipoCuenta->id : "",
                'nombre' => $ficha->tipoCuenta ? $ficha->tipoCuenta->nombre : ""
            ),
            'banco' => array(
                'id' => $ficha->banco ? $ficha->banco->id : "",
                'nombre' => $ficha->banco ? $ficha->banco->nombre : ""
            ),
            'numeroCuenta' => $ficha->numero_cuenta,                
            'fechaIngreso' => $ficha->fecha_ingreso,
            'fechaReconocimiento' => $ficha->fecha_reconocimiento,
            'fechaReconocimientoCesantia' => $ficha->fecha_reconocimiento_cesantia,
            'fechaFiniquito' => $ficha->fecha_finiquito,
            'tipoContrato' => array(
                'id' => $ficha->tipoContrato ? $ficha->tipoContrato->id : "",
                'nombre' => $ficha->tipoContrato ? $ficha->tipoContrato->nombre : ""
            ),
            'fechaVencimiento' => $ficha->fecha_vencimiento ? $ficha->fecha_vencimiento : "",
            'tipoJornada' => array(
                'id' => $ficha->tipoJornada ? $ficha->tipoJornada->id : "",
                'nombre' => $ficha->tipoJornada ? $ficha->tipoJornada->nombre : ""
            ),
            'semanaCorrida' => $ficha->semana_corrida ? true : false,
            'monedaSueldo' => $ficha->moneda_sueldo,
            'gratificacion' => $ficha->gratificacion,
            'gratificacionEspecial' => $ficha->gratificacion_especial ? true : false,
            'monedaGratificacion' => $ficha->moneda_gratificacion,
            'montoGratificacion' => $ficha->monto_gratificacion,
            'proporcionalInasistencias' => $ficha->gratificacion_proporcional_inasistencias ? true : false,
            'proporcionalLicencias' => $ficha->gratificacion_proporcional_licencias ? true : false,
            'sueldoBase' => $ficha->sueldo_base,
            'tipoTrabajador' => $ficha->tipo_trabajador,
            'excesoRetiro' => $ficha->exceso_retiro,
            'proporcionalColacion' => $ficha->proporcional_colacion ? true : false,
            'monedaColacion' => $ficha->moneda_colacion,
            'montoColacion' => $ficha->monto_colacion,
            'proporcionalMovilizacion' => $ficha->proporcional_movilizacion ? true : false,
            'monedaMovilizacion' => $ficha->moneda_movilizacion,
            'montoMovilizacion' => $ficha->monto_movilizacion,
            'proporcionalViatico' => $ficha->proporcional_viatico ? true : false,
            'monedaViatico' => $ficha->moneda_viatico,
            'montoViatico' => $ficha->monto_viatico,
            'prevision' => array(
                'id' => $ficha->prevision ? $ficha->prevision->id : "",
                'nombre' => $ficha->prevision ? $ficha->prevision->glosa : ""
            ),
            'afp' => array(
                'id' => $ficha->afp ? $ficha->afp->id : "",
                'nombre' => $ficha->afp ? $ficha->afp->glosa : ""
            ),
            'seguroDesempleo' => $ficha->seguro_desempleo ? true : false,
            'afpSeguro' => array(
                'id' => $ficha->afpSeguro ? $ficha->afpSeguro->id : "",
                'nombre' => $ficha->afpSeguro ? $ficha->afpSeguro->glosa : ""
            ),
            'isapre' => array(
                'id' => $ficha->isapre ? $ficha->isapre->id : "",
                'nombre' => $ficha->isapre ? $ficha->isapre->glosa : ""
            ),
            'cotizacionIsapre' => $ficha->cotizacion_isapre,
            'montoIsapre' => $ficha->monto_isapre,
            'sindicato' => $ficha->sindicato ? true : false,
            'monedaSindicato' => $ficha->moneda_sindicato,
            'montoSindicato' => $ficha->monto_sindicato,
            'estado' => $ficha->estado,
            'haberes' => $trabajador->misHaberesPermanentes(),
            'descuentos' => $trabajador->misDescuentosPermanentes(),
            'zonaImpuestoUnico' => array(
                'id' => $ficha->zonaImpuestoUnico ? $ficha->zonaImpuestoUnico->id : "",
                'nombre' => $ficha->zonaImpuestoUnico ? $ficha->zonaImpuestoUnico->nombre : "",
                'porcentaje' => $ficha->zonaImpuestoUnico ? $ficha->zonaImpuestoUnico->porcentaje : ""
            )
        );
        
        $datos = array(
            'trabajador' => $datosFicha,
            'formulario' => $datosFormulario
        );
        
        return Response::json($datos);
    }
    
    
    public function unificar()
    {
        $datos = Input::all();
        $fichas = $datos['fichas'];
        $unificar = $datos['unificar'];
        $desde = $fichas[0]['fechaDesde'];
        $idFicha = $datos['unificar']['id'];
        $fichaUnificar = FichaTrabajador::find($idFicha);
        $fichaUnificar->fecha = $desde;
        $fichaUnificar->save();
        
        $trabajador = $fichaUnificar->trabajador;
        Logs::crearLog('#trabajadores', $trabajador->id, $trabajador->rut_formato(), 'Unificación', $idFicha, $fichaUnificar->nombreCompleto(), 'Gestión Fichas'); 
        
        foreach($fichas as $ficha){
            if($ficha['id']!=$fichaUnificar->id){
                $eliminar = FichaTrabajador::find($ficha['id']);
                $eliminar->delete();
            }
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $ficha = FichaTrabajador::find($id);
        $datos = $this->get_datos_formulario();
        $errores = FichaTrabajador::errores($datos);       
        
        if(!$errores and $ficha){
            
            $ficha->nombres = $datos['nombres'];
            $ficha->apellidos = $datos['apellidos'];
            $ficha->nacionalidad_id = $datos['nacionalidad_id'];
            $ficha->sexo = $datos['sexo'];
            $ficha->estado_civil_id = $datos['estado_civil_id'];
            $ficha->fecha_nacimiento = $datos['fecha_nacimiento'];
            $ficha->direccion = $datos['direccion'];
            $ficha->comuna_id =  $datos['comuna_id'];
            $ficha->telefono =  $datos['telefono'];
            $ficha->celular =  $datos['celular'];
            $ficha->celular_empresa =  $datos['celular_empresa'];
            $ficha->email =  $datos['email'];
            $ficha->email_empresa =  $datos['email_empresa'];
            $ficha->tipo_id =  $datos['tipo_id'];
            $ficha->cargo_id = $datos['cargo_id'];
            $ficha->titulo_id = $datos['titulo_id'];
            $ficha->gratificacion = $datos['gratificacion'];
            $ficha->gratificacion_especial = $datos['gratificacion_especial'];
            $ficha->moneda_gratificacion = $datos['moneda_gratificacion'];
            $ficha->monto_gratificacion = $datos['monto_gratificacion'];
            $ficha->gratificacion_proporcional_inasistencias =  $datos['gratificacion_proporcional_inasistencias'];
            $ficha->gratificacion_proporcional_licencias =  $datos['gratificacion_proporcional_licencias'];
            $ficha->tienda_id = $datos['tienda_id'];
            $ficha->centro_costo_id = $datos['centro_costo_id'];
            $ficha->seccion_id = $datos['seccion_id'];
            $ficha->tipo_cuenta_id = $datos['tipo_cuenta_id'];
            $ficha->banco_id = $datos['banco_id'];
            $ficha->numero_cuenta = $datos['numero_cuenta'];
            $ficha->fecha_ingreso = $datos['fecha_ingreso'];
            $ficha->fecha_reconocimiento = $datos['fecha_reconocimiento'];
            $ficha->fecha_reconocimiento_cesantia = $datos['fecha_reconocimiento_cesantia'];
            $ficha->tipo_contrato_id = $datos['tipo_contrato_id'];
            $ficha->fecha_vencimiento = $datos['fecha_vencimiento'];
            $ficha->tipo_jornada_id = $datos['tipo_jornada_id'];
            $ficha->semana_corrida = $datos['semana_corrida'];
            $ficha->moneda_sueldo = $datos['moneda_sueldo'];
            $ficha->sueldo_base = $datos['sueldo_base'];
            $ficha->tipo_trabajador = $datos['tipo_trabajador'];
            $ficha->exceso_retiro = $datos['exceso_retiro'];
            $ficha->moneda_colacion = $datos['moneda_colacion'];
            $ficha->proporcional_colacion = $datos['proporcional_colacion'];
            $ficha->monto_colacion = $datos['monto_colacion'];
            $ficha->moneda_movilizacion = $datos['moneda_movilizacion'];
            $ficha->proporcional_movilizacion = $datos['proporcional_movilizacion'];
            $ficha->monto_movilizacion = $datos['monto_movilizacion'];
            $ficha->moneda_viatico = $datos['moneda_viatico'];
            $ficha->proporcional_viatico = $datos['proporcional_viatico'];
            $ficha->monto_viatico = $datos['monto_viatico'];
            $ficha->prevision_id = $datos['prevision_id'];
            $ficha->afp_id = $datos['afp_id'];
            $ficha->seguro_desempleo = $datos['seguro_desempleo'];
            $ficha->afp_seguro_id = $datos['afp_seguro_id'];
            $ficha->isapre_id = $datos['isapre_id'];
            $ficha->cotizacion_isapre = $datos['cotizacion_isapre'];
            $ficha->monto_isapre = $datos['monto_isapre'];
            $ficha->sindicato = $datos['sindicato'];
            $ficha->moneda_sindicato = $datos['moneda_sindicato'];
            $ficha->monto_sindicato = $datos['monto_sindicato'];
            $ficha->save();   
            
            $trabajador = $ficha->trabajador;
            Logs::crearLog('#trabajadores', $trabajador->id, $trabajador->rut_formato(), 'Update', $ficha->id, $ficha->nombreCompleto(), 'Gestión Fichas'); 
            
            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'trabajador' => $ficha->trabajador,
                'id' => $ficha->id
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
    public function destroy($id)
    {
        $mensaje="La Información fue eliminada correctamente";
        FichaTrabajador::find($id)->delete();
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario(){
        $datos = array(
            'id' => Input::get('id'),
            'nombres' => Input::get('nombres'),
            'apellidos' => Input::get('apellidos'),
            'nacionalidad_id' => Input::get('nacionalidad')['id'],
            'sexo' => Input::get('sexo'),
            'estado_civil_id' => Input::get('estadoCivil')['id'],
            'fecha_nacimiento' => Input::get('fechaNacimiento'),
            'direccion' => Input::get('direccion'),
            'comuna_id' => Input::get('comuna')['id'],
            'telefono' => Input::get('telefono'),
            'celular' => Input::get('celular'),
            'celular_empresa' => Input::get('celularEmpresa'),
            'email' => Input::get('email'),
            'email_empresa' => Input::get('emailEmpresa'),
            'tipo_id' => Input::get('tipo')['id'],
            'cargo_id' => Input::get('cargo')['id'],
            'titulo_id' => Input::get('titulo')['id'],
            'seccion_id' => Input::get('seccion')['id'],
            'tipo_cuenta_id' => Input::get('tipoCuenta')['id'],
            'banco_id' => Input::get('banco')['id'],
            'numero_cuenta' => Input::get('numeroCuenta'),
            'fecha_ingreso' => Input::get('fechaIngreso'),
            'fecha_reconocimiento' => Input::get('fechaReconocimiento'),
            'fecha_reconocimiento_cesantia' => Input::get('fechaReconocimientoCesantia'),
            'tipo_contrato_id' => Input::get('tipoContrato')['id'],
            'fecha_vencimiento' => Input::get('fechaVencimiento'),
            'tipo_jornada_id' => Input::get('tipoJornada')['id'],
            'semana_corrida' => Input::get('semanaCorrida'),
            'moneda_sueldo' => Input::get('monedaSueldo'),
            'sueldo_base' => Input::get('sueldoBase'),
            'tipo_trabajador' => Input::get('tipoTrabajador'),
            'exceso_retiro' => Input::get('excesoRetiro'),
            'moneda_colacion' => Input::get('monedaColacion'),
            'proporcional_colacion' => Input::get('proporcionalColacion'),
            'monto_colacion' => Input::get('montoColacion'),
            'moneda_movilizacion' => Input::get('monedaMovilizacion'),
            'proporcional_movilizacion' => Input::get('proporcionalMovilizacion'),
            'monto_movilizacion' => Input::get('montoMovilizacion'),
            'moneda_viatico' => Input::get('monedaViatico'),
            'proporcional_viatico' => Input::get('proporcionalViatico'),
            'monto_viatico' => Input::get('montoViatico'),
            'prevision_id' => Input::get('prevision')['id'],
            'afp_id' => Input::get('afp')['id'],
            'seguro_desempleo' => Input::get('seguroDesempleo'),
            'afp_seguro_id' => Input::get('afpSeguro')['id'],
            'tienda_id' => Input::get('tienda')['id'],
            'centro_costo_id' => Input::get('centroCosto')['id'],
            'isapre_id' => Input::get('isapre')['id'],
            'cotizacion_isapre' => Input::get('cotizacionIsapre'),
            'monto_isapre' => Input::get('montoIsapre'),
            'sindicato' => Input::get('sindicato'),
            'moneda_sindicato' => Input::get('monedaSindicato'),
            'monto_sindicato' => Input::get('montoSindicato'),
            'gratificacion' => Input::get('gratificacion'),
            'gratificacion_especial' => Input::get('gratificacionEspecial'),
            'moneda_gratificacion' => Input::get('monedaGratificacion'),
            'monto_gratificacion' => Input::get('montoGratificacion'),
            'gratificacion_proporcional_inasistencias' => Input::get('proporcionalInasistencias'),
            'gratificacion_proporcional_licencias' => Input::get('proporcionalLicencias')
        );
        return $datos;
    }

}
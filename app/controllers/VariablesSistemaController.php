<?php

class VariablesSistemaController extends \BaseController {
    
    public function configuracion()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#configuracion');
        
        $configuracion = Empresa::configuracion();
        $configuraciones = array(
            array(
                'valor' => 'e',
                'nombre' => 'Por Empresa'
            ), 
            array(
                'valor' => 'g',
                'nombre' => 'Global'
            )
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $configuracion,
            'configuraciones' => $configuraciones
        );
        
        return Response::json($datos);
    }
    
    public function obtener_anio_inicial_registros(){
        $variable = VariableSistema::where('variable', 'ANIO_INICIAL')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable = "ANIO_INICIAL";
            $variable->valor1 = date("Y");
            $variable->save();
        }
        $anioInicial = $variable->valor1;

        $variable = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable = "ANIO_CONTABLE";
            $variable->valor1 = date("Y");
            $variable->save();
        }
        $anioFinal = $variable->valor1;

        return Response::json(array(
            'anioInicial' => $anioInicial,
            'anioFinal' => $anioFinal
        ));
    }
    
    public function obtenerConfiguracionDepreciacion(){
        $variable = VariableSistema::where('variable', 'CONFIGURACION_DEPRECIACION_PERIODO')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable = "CONFIGURACION_DEPRECIACION_PERIODO";
            $variable->valor1 = 1;
            $variable->save();
        }
        $periodo = $variable->valor1;

        $variable = VariableSistema::where('variable', 'CONFIGURACION_DEPRECIACION_METODO')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable = "CONFIGURACION_DEPRECIACION_METODO";
            $variable->valor1 = 1;
            $variable->save();
        }
        $metodo = $variable->valor1;

        return array(
            'periodo' => $periodo,
            'metodo' => $metodo
        );
    }

    public function obtener_indicadores_economicos_ipc_mensual($anio){

        $meses = Config::get('constants.meses');
        $tablaIPC = IpcMensual::where('anio',$anio)->orderBy('mes', 'ASC')->get();
        if( $tablaIPC->count() ){
            $valorIPCMensual = array();
            foreach( $tablaIPC as $mesTabla ) {
                for ($a = 1; $a <= 12; $a++) {
                    $mes = strtolower($meses[$a - 1]['value']);
                    $valorIPCMensual[$mesTabla->mes][$a] = $mesTabla->$mes;
                }
            }
        }else{
            $valorIPCMensual=array(
                0 => array()
            );
        }

        return Response::json($valorIPCMensual);
    }

	public function index_apertura_cierre_anio(){
		$variable = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
		$anioActual = $variable->valor1;
		$editarAnterior = $variable->valor2;

		// si existe anio anterior
		$anioAnterior="";
		$asientos = ContAsiento::where( DB::raw('YEAR(fecha)'), '=', intval($anioActual)-1 )->first();
		if( $asientos ){
			$anioAnterior = $anioActual-1;
		}

		$variable = VariableSistema::where('variable', 'CUENTAS_AJUSTES_ESTADO_RESULTADO')->first();
		$cuenta=null;
		if( $variable->valor1 ){
			$cuenta = CuentaSubCuenta::find($variable->valor1);
		}

		$datos=array(
			'anioActual' => $anioActual,
			'anioAnterior' => $anioAnterior,
			'actual' => $anioActual + 1,
			'cambiar' => $anioActual != date("Y") ? true : false,
			'editarAnioAnterior' => $editarAnterior ? true : false,
			'cuentaAjusteEstadoResultado' => $cuenta? array(
					'id' => $cuenta->id,
					'cuenta' => $cuenta->nombre,
					'codigo' => $cuenta->cuenta
							->clasificacion?
							(
									$cuenta->cuenta
											->clasificacion->obtenerCodigoClasificacion().".".
									$cuenta->cuenta->codigo.".".
									$cuenta->codigo) : ""
			) : ""
		);
		return Response::json($datos);
	}

	public function store_apertura_cierre_anio_cambiar(){
		$actual = Input::get('actual');
		$variable = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
		$variable->valor1 = $actual;
		$variable->save();

		Input::merge(array('tipo' => '3', 'anioinicio' => Input::get('anioActual'), 'nivel' => '5' ));

		$contAsientosCtrl = new ContAsientosController();
		$balance = $contAsientosCtrl->generar_balance_tributario();

		$estadoResultado = $balance['totalActivos'] - $balance['totalPasivos'];

		$variable = VariableSistema::where('variable', 'ANIO_CONTABLE_ESTADO_RESULTADO')->first();
		$variable->valor1 = Input::get('anioActual');
		$variable->valor2 = $estadoResultado;
		$variable->save();

		// se genera el comprobante contable

		$variable = VariableSistema::where('variable', 'CUENTAS_AJUSTES_ESTADO_RESULTADO')->first();
		$cuenta=null;
		if( $variable->valor1 ){
			$cuenta = CuentaSubCuenta::find($variable->valor1);
		}

		$tipoCom = TipoComprobante::where('nombre', 'APERTURA')->first();
		if( $tipoCom ){
			$comp = new ContAsiento();
			$comp->tipo_comprobante_id = $tipoCom->id;
			$comp->fecha = $actual."-01-01";
			$comp->numero = 1;
			$comp->glosa = "APERTURA AÑO ".$actual;
			$comp->debe = 0;
			$comp->haber = 0;
			$comp->responsable_id = Auth::usuario()->user()->id;
			$comp->estado = 1;
			$comp->save();

			$contDet = new ContAsientoDetalle();
			$contDet->asiento_id = $comp->id;
			$contDet->cuenta_id = $cuenta? $cuenta->id : 0;
            $contDet->glosa = "APERTURA AÑO ".$actual;
			$contDet->debe = $estadoResultado < 0 ? ($estadoResultado * -1) : 0;
			$contDet->haber = $estadoResultado >= 0 ? $estadoResultado : 0;
			$contDet->save();

            $sumaDebe=$contDet->debe;
            $sumaHaber=$contDet->haber;
			if( count($balance['balance']) ){
				foreach( $balance['balance'] as $cuenta ){
                    if( in_array( $cuenta['tipoCuenta'], array(1,2,3)) ) {
                        // solo cuentas de activo, pasivo y patrimonio
						if( $cuenta['saldoDeudor'] > 0 or $cuenta['saldoAcreedor'] > 0) {
							$contDet = new ContAsientoDetalle();
							$contDet->asiento_id = $comp->id;
							$contDet->cuenta_id = $cuenta['id'];
                            $contDet->glosa = "APERTURA AÑO ".$actual;
							$contDet->debe = $cuenta['saldoDeudor'];
							$contDet->haber = $cuenta['saldoAcreedor'];
							$contDet->save();
							$sumaDebe += $cuenta['saldoDeudor'];
							$sumaHaber += $cuenta['saldoAcreedor'];
						}
                    }
				}
			}

            $comp->debe = $sumaDebe;
            $comp->haber = $sumaHaber;
            $comp->save();
		}



		$resultado = array(
			'success' => true,
			'mensaje' => Config::get('constants.mensajes.update.ok')
		);

		return Response::json($resultado);

	}

	public function store_apertura_cierre_anio(){
		$variable = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
		$variable->valor2 = Input::get('editarAnioAnterior')? 1 : 0;
		$variable->save();
		$resultado = array(
			'success' => true,
			'mensaje' => Config::get('constants.mensajes.update.ok')
		);

		return Response::json($resultado);
	}

    public function cargar_meses_anio_control_cierre($anio){
        $datos=array('meses' => array());
        $meses = Config::get('constants.meses');
        $controlCierre = ControlCierreMensual::where('anio', $anio)->first();

        if( $controlCierre ){
            foreach( $meses as $mes ){
                $mesStr = mb_strtolower($mes['value']);
                $datos['meses'][$mes['id']]=$controlCierre->$mesStr ? true : false;
            }
        }else{
            foreach( $meses as $mes ){
                $datos['meses'][$mes['id']]= false;
            }
        }


        return Response::json($datos);
    }

    public function comprobar_control_cierre_mes($anio, $mes){
        $meses = Config::get('constants.meses');
        $controlCierre = ControlCierreMensual::where('anio', $anio)->first();
        if( $controlCierre ){
            $mesStr = mb_strtolower( $meses[$mes-1]['value'] );
            if( $controlCierre->$mesStr ){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function obtener_meses_activos_control_cierre(array $anio = null){
        $listaMeses=array();
        $meses = Config::get('constants.meses');
        $registros = ControlCierreMensual::where('id', '>', 0);
        if( $anio ) {
            $registros->whereIn('anio', $anio);
        }
        $controlCierre = $registros->get();
        if( $controlCierre->count() ){
            foreach($controlCierre as $control ) {

                foreach ($meses as $mes) {
                    $mesStr = mb_strtolower($mes['value']);
                    if ($control->$mesStr) {
                        $listaMeses[]=array(
                            'id' => $mes['id'],
                            'anio' => $control->anio
                        );
                    }
                }
            }
        }
        return $listaMeses;
    }

    public function obtener_anios_activos_control_cierre(){
        $variable = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
        $anioActual = $variable->valor1;
        $datos=array('anios' => array());
        $datos[0] = $anioActual;
        if( $variable->valor2 ){
            $datos[1]=$anioActual-1;
        }
        return $datos;
    }

	public function index()
	{
        $meses = Config::get('constants.meses');
        $tablaIPC = IpcMensual::where('anio', date("Y"))->orderBy('mes', 'ASC')->get();
        if( $tablaIPC->count() ){
            $valorIPCMensual = array();
            foreach( $tablaIPC as $mesTabla ) {
                for ($a = 1; $a <= 12; $a++) {
                    $mes = strtolower($meses[$a - 1]['value']);
                    $valorIPCMensual[$mesTabla->mes][$a] = $mesTabla->$mes;
                }
            }
        }else{
            $valorIPCMensual=array(
                0 => array()
            );
        }


        $variable = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
        $anioActual = $variable->valor1;


		$datos=array(
			'plan' => array(),
			'cuentasAjustes' => array(),
			'cuentasLibroCompra' => array(),
			'cuentasLibroVenta' => array(),
			'cuentasLibroBoleta' => array(),
			'cuentasClienteProveedor' => array(),
			'cuentasBoletaHonorarios' => array(),
            'cuentasPagoClientes' => array(),
            'cuentasPagoProveedores' => array(),
			'cuentasDepositoPagoClientes' => array(),
            'cuentasBienesFijos' => array(),
            'cuentasDefectoActivosFijos' => array(),
            'indicadoresEconomicos' => array(
                'ipc' => $valorIPCMensual,
                'anio' => intval( $anioActual )
            ),
            'configuracionActivosFijos' => array(
                'periodo' => 1,
                'metodo' => 1
            ),
            'controlCierres' => array(
                'anio' => intval( $anioActual ),
                'meses' => array()
            )
        );


        $variable = VariableSistema::where('variable', 'ANIO_CONTABLE')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable = "ANIO_CONTABLE";
            $variable->valor1 = $anioActual;
            $variable->valor2 = 0;
            $variable->save();
        }

        $datos['anioContable']=array(
            'actual' => $variable->valor1,
            'anterior' => $variable->valor2 ? true : false
        );

        $controlCierre = ControlCierreMensual::where('anio', $anioActual)->first();
        if( $controlCierre ){
            foreach( $meses as $mes ){
                $mesStr = mb_strtolower($mes['value']);
                $datos['controlCierres']['meses'][$mes['id']]=$controlCierre->$mesStr ? true : false;
            }
        }else{
            foreach( $meses as $mes ){
                $datos['controlCierres']['meses'][$mes['id']]= false;
            }
        }



        $variables = VariableSistema::where('variable', 'LIKE', 'CUENTAS_PAGO_CLIENTES_%')->orderBy('id', 'ASC')->get();
        if( $variables->count() ) {
            foreach ($variables as $variable) {
                $cuenta=null;
                if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                $datos['cuentasPagoClientes'][]['cuenta']=$cuenta? $cuenta->resumenCuenta() : "";
            }
        }

        $variables = VariableSistema::where('variable', 'LIKE', 'CUENTAS_PAGO_PROVEEDORES_%')->orderBy('id', 'ASC')->get();
        if( $variables->count() ) {
            foreach ($variables as $variable) {
                $cuenta=null;
                if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                $datos['cuentasPagoProveedores'][]['cuenta']=$cuenta? $cuenta->resumenCuenta() : "";
            }
        }


        $variables = VariableSistema::where('variable', 'LIKE', 'CUENTAS_DEPOSITO_PAGO_CLIENTES_%')->orderBy('id', 'ASC')->get();
        if( $variables->count() ) {
            foreach ($variables as $variable) {
                $cuenta=null;
                if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                $datos['cuentasDepositoPagoClientes'][]=array(
                    'cuenta' => $cuenta? $cuenta->resumenCuenta() : ""
                );
            }
        }


        $variables = VariableSistema::where('variable', 'LIKE', 'CUENTAS_ACTIVOS_FIJOS_DEFECTO_%')->orderBy('id', 'ASC')->get();
        if( $variables->count() ) {
            foreach ($variables as $variable) {
                $activo=null;
                $cuenta=null;
                $cuentaDepAcum=null;
                $cuentaDepEjercicio=null;
                if( $variable->valor1 ){
                    $activoFijoTabla = ActivoFijoTabla::find($variable->valor1);
                    if( $activoFijoTabla ) {
                        $activo = array(
                            'id' => $activoFijoTabla->id,
                            'activoFijo' => $activoFijoTabla->activo,
                            'vidaUtilNormal' => $activoFijoTabla->vida_util_normal,
                            'depreciacionAcelerada' => $activoFijoTabla->depreciacion_acelerada
                        );
                    }
                }
                if( $variable->valor2 ) $cuenta = CuentaSubCuenta::find($variable->valor2);
                if( $variable->valor3 ) $cuentaDepAcum = CuentaSubCuenta::find($variable->valor3);
                if( $variable->valor4 ) $cuentaDepEjercicio = CuentaSubCuenta::find($variable->valor4);
                $datos['cuentasDefectoActivosFijos'][]=array(
                    'activo' => $activo,
                    'cuenta' => $cuenta? $cuenta->resumenCuenta() : "",
                    'cuentaDepAcum' => $cuentaDepAcum? $cuentaDepAcum->resumenCuenta() : "",
                    'cuentaDepEjercicio' => $cuentaDepEjercicio? $cuentaDepEjercicio->resumenCuenta() : ""
                );
            }
        }


        $variables = VariableSistema::orderBy('id', 'ASC')->get();
		if( $variables->count() ){
			foreach( $variables as $variable ){
				switch( $variable->variable ){
					default:
						for($a=1; $a <= 10; $a++){
							$var ="PLAN_NIVEL_".$a;
							if( $variable->variable == $var ){
								$datos['plan']['codigos'][]=array(
									'nivel' => $a,
									'digitos' => intval($variable->valor1)
								);
							}
						}
					break;
					case 'PLAN_NIVELES':
						$datos['plan']['niveles']=intval($variable->valor1);
						break;

                    case 'CONFIGURACION_DEPRECIACION_PERIODO':
                        $datos['configuracionActivosFijos']['periodo']=$variable->valor1;
                        break;
                    case 'CONFIGURACION_DEPRECIACION_METODO':
                        $datos['configuracionActivosFijos']['metodo']=$variable->valor1;
                        break;

					case 'CUENTAS_AJUSTES_ACTIVO_FIJO':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasAjustes']['activoFijo']=$cuenta? $cuenta->resumenCuenta() : "";
						break;
					case 'CUENTAS_AJUSTES_RETIROS':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasAjustes']['retiros']=$cuenta? $cuenta->resumenCuenta() : "";
						break;
					case 'CUENTAS_AJUSTES_ESTADO_RESULTADO':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasAjustes']['estadoResultado']=$cuenta? $cuenta->resumenCuenta(): "";
						break;


                    case 'CUENTAS_LIBRO_COMPRA_NETO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroCompra']['neto']=$cuenta? $cuenta->resumenCuenta() : "";
                    break;
                    case 'CUENTAS_LIBRO_COMPRA_EXENTO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroCompra']['exento']=$cuenta? $cuenta->resumenCuenta() : "";
                    break;
                    case 'CUENTAS_LIBRO_COMPRA_IVA':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroCompra']['iva']=$cuenta? $cuenta->resumenCuenta() : "";
                    break;
                    case 'CUENTAS_LIBRO_COMPRA_OTROS_IMP':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroCompra']['otrosImp']=$cuenta? $cuenta->resumenCuenta() : "";
                    break;
                    case 'CUENTAS_LIBRO_COMPRA_SIN_CREDITO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroCompra']['sinCredito']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;
                    case 'CUENTAS_LIBRO_COMPRA_TOTAL':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroCompra']['total']=$cuenta? $cuenta->resumenCuenta() : "";
                    break;

                    case 'CUENTAS_LIBRO_COMPRA_AJUSTE':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroCompra']['ajuste']=$cuenta? $cuenta->resumenCuenta() : "";
                    break;


					case 'CUENTAS_LIBRO_VENTA_NETO':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasLibroVenta']['neto']=$cuenta? $cuenta->resumenCuenta() : "";
						break;
					case 'CUENTAS_LIBRO_VENTA_EXENTO':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasLibroVenta']['exento']=$cuenta? $cuenta->resumenCuenta() : "";
						break;
					case 'CUENTAS_LIBRO_VENTA_IVA':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasLibroVenta']['iva']=$cuenta? $cuenta->resumenCuenta() : "";
						break;
					case 'CUENTAS_LIBRO_VENTA_OTROS_IMP':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasLibroVenta']['otrosImp']=$cuenta? $cuenta->resumenCuenta() : "";
						break;
					case 'CUENTAS_LIBRO_VENTA_SIN_CREDITO':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasLibroVenta']['sinCredito']=$cuenta? $cuenta->resumenCuenta() : "";
						break;
					case 'CUENTAS_LIBRO_VENTA_TOTAL':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasLibroVenta']['total']=$cuenta? $cuenta->resumenCuenta() : "";
						break;

					case 'CUENTAS_LIBRO_VENTA_AJUSTE':
						$cuenta=null;
						if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
						$datos['cuentasLibroVenta']['ajuste']=$cuenta? $cuenta->resumenCuenta() : "";
						break;


                    case 'CUENTAS_LIBRO_BOLETA_NETO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroBoleta']['neto']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;
                    case 'CUENTAS_LIBRO_BOLETA_EXENTO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroBoleta']['exento']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;
                    case 'CUENTAS_LIBRO_BOLETA_IVA':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroBoleta']['iva']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;
                    case 'CUENTAS_LIBRO_BOLETA_TOTAL':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroBoleta']['total']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;
                    case 'CUENTAS_LIBRO_BOLETA_AJUSTE':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasLibroBoleta']['ajuste']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;


                    case 'CUENTAS_DEFECTO_PROVEEDORES':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasClienteProveedor']['proveedores']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;

                    case 'CUENTAS_DEFECTO_CLIENTES':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasClienteProveedor']['clientes']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;

                    case 'CUENTAS_DEFECTO_PROVEEDORES_ANTICIPO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasClienteProveedor']['proveedoresAnticipo']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;

                    case 'CUENTAS_DEFECTO_CLIENTES_ANTICIPO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasClienteProveedor']['clientesAnticipo']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;


                    case 'CUENTAS_BOLETA_HONORARIOS_TOTAL':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasBoletaHonorarios']['total']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;

                    case 'CUENTAS_BOLETA_HONORARIOS_RETENCION':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasBoletaHonorarios']['retencion']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;

                    case 'CUENTAS_BOLETA_HONORARIOS_LIQUIDO':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasBoletaHonorarios']['liquido']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;

                    case 'CUENTAS_ACTIVOS_FIJOS_CORRECCION_MONETARIA_DEUDOR':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasActivosFijos']['correccionMonetariaDeudor']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;

                    case 'CUENTAS_ACTIVOS_FIJOS_CORRECCION_MONETARIA_ACREEDOR':
                        $cuenta=null;
                        if( $variable->valor1 ) $cuenta = CuentaSubCuenta::find($variable->valor1);
                        $datos['cuentasActivosFijos']['correccionMonetariaAcreedor']=$cuenta? $cuenta->resumenCuenta() : "";
                        break;
				}
			}
		}

        $listaAnios=array();
        $anioActual = intval(date("Y"));
        for( $anioIn = $anioActual; $anioIn >= 2010; $anioIn-- ) {
            $listaAnios[] = $anioIn;
        }

        $respuesta = array(
            'datos' => $datos,
            'anios' => $listaAnios
        );

		return Response::json($respuesta);
	}



	public function store_plan_cuentas()
	{
		$niveles = Input::get('niveles');
		$codigos = Input::get('codigos');

        $variable = VariableSistema::where('variable', 'PLAN_NIVELES')->first();
		$variable->valor1=$niveles;
        $variable->save();

		if( count($codigos) ){
			foreach( $codigos as $codigo ){
				$variable = VariableSistema::where('variable', 'PLAN_NIVEL_'.$codigo['nivel'])->first();
				$variable->valor1 = $codigo['digitos'];
				$variable->save();
			}
		}

        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);
    }

    public function store_cuentas_cliente_proveedor(){
        $datos = $this->get_datos_formulario_cuentas_clientes_proveedores();

        $variable = VariableSistema::where('variable', 'CUENTAS_DEFECTO_PROVEEDORES')->first();
        $variable->valor1=array_key_exists('id', $datos['proveedores'])? $datos['proveedores']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_DEFECTO_CLIENTES')->first();
        $variable->valor1=array_key_exists('id', $datos['clientes'])? $datos['clientes']['id'] : '';
        $variable->save();


        $variable = VariableSistema::where('variable', 'CUENTAS_DEFECTO_PROVEEDORES_ANTICIPO')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable = "CUENTAS_DEFECTO_PROVEEDORES_ANTICIPO";
        }
        $variable->valor1=array_key_exists('id', $datos['proveedoresAnticipo'])? $datos['proveedoresAnticipo']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_DEFECTO_CLIENTES_ANTICIPO')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable = "CUENTAS_DEFECTO_CLIENTES_ANTICIPO";
        }
        $variable->valor1=array_key_exists('id', $datos['clientesAnticipo'])? $datos['clientesAnticipo']['id'] : '';
        $variable->save();


        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);

    }

	public function store_cuentas_ajustes(){
		$datos = $this->get_datos_formulario_cuentas_ajustes();

		$variable = VariableSistema::where('variable', 'CUENTAS_AJUSTES_ACTIVO_FIJO')->first();
		$variable->valor1=array_key_exists('id', $datos['activoFijo'])? $datos['activoFijo']['id'] : '';
		$variable->save();

		$variable = VariableSistema::where('variable', 'CUENTAS_AJUSTES_RETIROS')->first();
		$variable->valor1=array_key_exists('id', $datos['retiros'])? $datos['retiros']['id'] : '';
		$variable->save();

		$variable = VariableSistema::where('variable', 'CUENTAS_AJUSTES_ESTADO_RESULTADO')->first();
		$variable->valor1=array_key_exists('id', $datos['estadoResultado'])? $datos['estadoResultado']['id'] : '';
		$variable->save();

		$resultado = array(
				'success' => true,
				'mensaje' => Config::get('constants.mensajes.update.ok')
		);

		return Response::json($resultado);

	}




    public function store_control_cierre_mensual($anio){
        $meses = Config::get('constants.meses');
        $mesesCierres = Input::get('meses');
        ControlCierreMensual::where('anio', $anio)->delete();
        $controlCierre = new ControlCierreMensual();
        $controlCierre->anio = $anio;
        foreach( $meses as $mes ){
            $mesStr = mb_strtolower($mes['value']);
            $controlCierre->$mesStr=$mesesCierres[$mes['id']];
        }
        $controlCierre->save();

        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);
    }




	public function store_cuentas_libro_compra(){
		$datos = $this->get_datos_formulario_cuentas_libro_compra();

		$variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_COMPRA_NETO')->first();
		$variable->valor1=array_key_exists('id', $datos['neto'])? $datos['neto']['id'] : '';
		$variable->save();

		$variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_COMPRA_EXENTO')->first();
		$variable->valor1=array_key_exists('id', $datos['exento'])? $datos['exento']['id'] : '';
		$variable->save();

		$variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_COMPRA_IVA')->first();
		$variable->valor1=array_key_exists('id', $datos['iva'])? $datos['iva']['id'] : '';
		$variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_COMPRA_OTROS_IMP')->first();
        $variable->valor1=array_key_exists('id', $datos['otrosImp'])? $datos['otrosImp']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_COMPRA_SIN_CREDITO')->first();
        $variable->valor1=array_key_exists('id', $datos['sinCredito'])? $datos['sinCredito']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_COMPRA_TOTAL')->first();
        $variable->valor1=array_key_exists('id', $datos['total'])? $datos['total']['id'] : '';
        $variable->save();

		$variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_COMPRA_AJUSTE')->first();
		$variable->valor1=array_key_exists('id', $datos['ajuste'])? $datos['ajuste']['id'] : '';
		$variable->save();

		$resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
		);

		return Response::json($resultado);

	}

    public function store_cuentas_libro_venta(){
        $datos = $this->get_datos_formulario_cuentas_libro_venta();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_VENTA_NETO')->first();
        $variable->valor1=array_key_exists('id', $datos['neto'])? $datos['neto']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_VENTA_EXENTO')->first();
        $variable->valor1=array_key_exists('id', $datos['exento'])? $datos['exento']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_VENTA_IVA')->first();
        $variable->valor1=array_key_exists('id', $datos['iva'])? $datos['iva']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_VENTA_OTROS_IMP')->first();
        $variable->valor1=array_key_exists('id', $datos['otrosImp'])? $datos['otrosImp']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_VENTA_SIN_CREDITO')->first();
        $variable->valor1=array_key_exists('id', $datos['sinCredito'])? $datos['sinCredito']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_VENTA_TOTAL')->first();
        $variable->valor1=array_key_exists('id', $datos['total'])? $datos['total']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_VENTA_AJUSTE')->first();
        $variable->valor1=array_key_exists('id', $datos['ajuste'])? $datos['ajuste']['id'] : '';
        $variable->save();

        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);

    }

    public function store_cuentas_libro_boleta(){
        $datos = $this->get_datos_formulario_cuentas_libro_boleta();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_BOLETA_NETO')->first();
        if(!$variable){
            $variable = new VariableSistema();
            $variable->variable = "CUENTAS_LIBRO_BOLETA_NETO";
        }
        $variable->valor1=array_key_exists('id', $datos['neto'])? $datos['neto']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_BOLETA_EXENTO')->first();
        if(!$variable){
            $variable = new VariableSistema();
            $variable->variable = "CUENTAS_LIBRO_BOLETA_EXENTO";
        }
        $variable->valor1=array_key_exists('id', $datos['exento'])? $datos['exento']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_BOLETA_IVA')->first();
        if(!$variable){
            $variable = new VariableSistema();
            $variable->variable = "CUENTAS_LIBRO_BOLETA_IVA";
        }
        $variable->valor1=array_key_exists('id', $datos['iva'])? $datos['iva']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_BOLETA_TOTAL')->first();
        if(!$variable){
            $variable = new VariableSistema();
            $variable->variable = "CUENTAS_LIBRO_BOLETA_TOTAL";
        }
        $variable->valor1=array_key_exists('id', $datos['total'])? $datos['total']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_LIBRO_BOLETA_AJUSTE')->first();
        if(!$variable){
            $variable = new VariableSistema();
            $variable->variable = "CUENTAS_LIBRO_BOLETA_AJUSTE";
        }
        $variable->valor1=array_key_exists('id', $datos['ajuste'])? $datos['ajuste']['id'] : '';
        $variable->save();


        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);

    }

    public function store_cuentas_boleta_honorarios(){
        $datos = $this->get_datos_formulario_cuentas_boleta_honorarios();

        $variable = VariableSistema::where('variable', 'CUENTAS_BOLETA_HONORARIOS_TOTAL')->first();
        $variable->valor1=array_key_exists('id', $datos['total'])? $datos['total']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_BOLETA_HONORARIOS_RETENCION')->first();
        $variable->valor1=array_key_exists('id', $datos['retencion'])? $datos['retencion']['id'] : '';
        $variable->save();

        $variable = VariableSistema::where('variable', 'CUENTAS_BOLETA_HONORARIOS_LIQUIDO')->first();
        $variable->valor1=array_key_exists('id', $datos['liquido'])? $datos['liquido']['id'] : '';
        $variable->save();

        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);

    }

    public function store_cuentas_pago_clientes_proveedores(){
        VariableSistema::where('variable', 'LIKE', 'CUENTAS_PAGO_CLIENTES_%')->delete();
        VariableSistema::where('variable', 'LIKE', 'CUENTAS_PAGO_PROVEEDORES_%')->delete();

        $ctasClientes = Input::get('clientes');
        $ctaProveedores = Input::get('proveedores');
        if( count($ctasClientes) ){
            foreach( $ctasClientes as $indice => $cuentaCli ){
                if( array_key_exists('cuenta', $cuentaCli) ) {
                    if (array_key_exists('id', $cuentaCli['cuenta'])) {
                        $variable = new VariableSistema();
                        $variable->variable = "CUENTAS_PAGO_CLIENTES_" . $indice;
                        $variable->valor1 = $cuentaCli['cuenta']['id'];
                        $variable->save();
                    }
                }
            }
        }

        if( count($ctaProveedores) ){
            foreach( $ctaProveedores as $indice => $cuentaProv ){
                if( array_key_exists('cuenta', $cuentaProv) ) {
                    if (array_key_exists('id', $cuentaProv['cuenta'])) {
                        $variable = new VariableSistema();
                        $variable->variable = "CUENTAS_PAGO_PROVEEDORES_" . $indice;
                        $variable->valor1 = $cuentaProv['cuenta']['id'];
                        $variable->save();
                    }
                }
            }
        }

        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);
    }

    public function store_cuentas_deposito_pago_clientes(){
        VariableSistema::where('variable', 'LIKE', 'CUENTAS_DEPOSITO_PAGO_CLIENTES_%')->delete();

        $ctasCtes = Input::get('cuentas');
        if( count($ctasCtes) ){
            foreach( $ctasCtes as $indice => $cuentaCte ){
                if( array_key_exists('cuenta', $cuentaCte) ) {
                    if (array_key_exists('id', $cuentaCte['cuenta'])) {
                        $variable = new VariableSistema();
                        $variable->variable = "CUENTAS_DEPOSITO_PAGO_CLIENTES_" . $indice;
                        $variable->valor1 = $cuentaCte['cuenta']['id'];
                        $variable->save();
                    }
                }
            }
        }

        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);
    }

    public function store_cuentas_activos_fijos(){
        $datos = $this->get_datos_formulario_cuentas_activos_fijos();

        $variable = VariableSistema::where('variable', 'CUENTAS_ACTIVOS_FIJOS_CORRECCION_MONETARIA_DEUDOR')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable="CUENTAS_ACTIVOS_FIJOS_CORRECCION_MONETARIA_DEUDOR";
        }
        $variable->valor1=array_key_exists('id', $datos['correccionMonetariaDeudor'])? $datos['correccionMonetariaDeudor']['id'] : '';
        $variable->save();


        $variable = VariableSistema::where('variable', 'CUENTAS_ACTIVOS_FIJOS_CORRECCION_MONETARIA_ACREEDOR')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable="CUENTAS_ACTIVOS_FIJOS_CORRECCION_MONETARIA_ACREEDOR";
        }
        $variable->valor1=array_key_exists('id', $datos['correccionMonetariaAcreedor'])? $datos['correccionMonetariaAcreedor']['id'] : '';
        $variable->save();


        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);

    }

    public function store_cuentas_defecto_activos_fijos(){
        VariableSistema::where('variable', 'LIKE', 'CUENTAS_ACTIVOS_FIJOS_DEFECTO_%')->delete();

        $cuentas = Input::get('cuentas');
        if( count($cuentas) ){
            foreach( $cuentas as $indice => $cuenta ){
                if( array_key_exists('activo', $cuenta) ) {
                    if (array_key_exists('id', $cuenta['activo'])) {
                        $variable = new VariableSistema();
                        $variable->variable = "CUENTAS_ACTIVOS_FIJOS_DEFECTO_" . $indice;
                        $variable->valor1 = $cuenta['activo']['id'];
                        $variable->valor2 = $cuenta['cuenta']['id'];
                        $variable->valor3 = $cuenta['cuentaDepAcum']['id'];
                        $variable->valor4 = $cuenta['cuentaDepEjercicio']['id'];
                        $variable->save();
                    }
                }
            }
        }

        $resultado = array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.update.ok')
        );

        return Response::json($resultado);
    }

    public function store_indicadores_economicos_ipc_mensual(){
        $meses = Config::get('constants.meses');
        $tablaIPC = Input::get('ipc');
        $anio = Input::get('anio');
        $tabla=IpcMensual::where('anio', $anio)->delete();
        if( count($tablaIPC) ){
            foreach( $tablaIPC as $mesId => $matrizMes ){
                if( is_array($matrizMes) ) {
                    $tabla = new IpcMensual();
                    $tabla->anio = $anio;
                    $tabla->mes = $mesId;

                    foreach ($matrizMes as $mesId2 => $valor) {
                        if ($mesId2 > 0) {
                            $mes = strtolower($meses[$mesId2 - 1]['value']);
                            $tabla->$mes = $valor;
                        }
                    }
                    $tabla->save();
                }
            }
        }
        $respuesta=array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.store.ok')
        );
        return Response::json($respuesta);
    }

    public function store_configuracion_depreciacion(){

        $variable = VariableSistema::where('variable', 'CONFIGURACION_DEPRECIACION_PERIODO')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable="CONFIGURACION_DEPRECIACION_PERIODO";
        }
        $variable->valor1=Input::get('periodo');
        $variable->save();


        $variable = VariableSistema::where('variable', 'CONFIGURACION_DEPRECIACION_METODO')->first();
        if( !$variable ){
            $variable = new VariableSistema();
            $variable->variable="CONFIGURACION_DEPRECIACION_METODO";
        }
        $variable->valor1=Input::get('metodo');
        $variable->save();

        $respuesta=array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.store.ok')
        );
        return Response::json($respuesta);

    }

    public function get_datos_formulario_cuentas_clientes_proveedores(){
        $datos=array(
            'proveedores' => Input::get('proveedores')? Input::get('proveedores') : array(),
            'clientes' => Input::get('clientes')? Input::get('clientes') : array(),
            'proveedoresAnticipo' => Input::get('proveedoresAnticipo')? Input::get('proveedoresAnticipo') : array(),
            'clientesAnticipo' => Input::get('clientesAnticipo')? Input::get('clientesAnticipo') : array()
        );
        return $datos;
    }

	public function get_datos_formulario_cuentas_ajustes(){
		$datos=array(
			'activoFijo' => Input::get('activoFijo')? Input::get('activoFijo') : array(),
			'retiros' => Input::get('retiros')? Input::get('retiros') : array(),
			'estadoResultado' => Input::get('estadoResultado')? Input::get('estadoResultado') : array()
		);
		return $datos;
	}

    public function get_datos_formulario_cuentas_activos_fijos(){
        $datos=array(
            'correccionMonetariaDeudor' => Input::get('correccionMonetariaDeudor')? Input::get('correccionMonetariaDeudor') : array(),
            'correccionMonetariaAcreedor' => Input::get('correccionMonetariaAcreedor')? Input::get('correccionMonetariaAcreedor') : array()
        );
        return $datos;
    }



    public function get_datos_formulario_cuentas_libro_compra(){
        $datos=array(
            'neto' => Input::get('neto')? Input::get('neto') : array(),
            'exento' => Input::get('exento')? Input::get('exento') : array(),
            'iva' => Input::get('iva')? Input::get('iva') : array(),
            'otrosImp' => Input::get('otrosImp')? Input::get('otrosImp') : array(),
            'sinCredito' => Input::get('sinCredito')? Input::get('sinCredito') : array(),
            'total' => Input::get('total')? Input::get('total') : array(),
            'ajuste' => Input::get('ajuste')? Input::get('ajuste') : array()
        );
        return $datos;
    }

    public function get_datos_formulario_cuentas_libro_venta(){
        $datos=array(
            'neto' => Input::get('neto')? Input::get('neto') : array(),
            'exento' => Input::get('exento')? Input::get('exento') : array(),
            'iva' => Input::get('iva')? Input::get('iva') : array(),
            'otrosImp' => Input::get('otrosImp')? Input::get('otrosImp') : array(),
            'sinCredito' => Input::get('sinCredito')? Input::get('sinCredito') : array(),
            'total' => Input::get('total')? Input::get('total') : array(),
            'ajuste' => Input::get('ajuste')? Input::get('ajuste') : array()
        );
        return $datos;
    }

    public function get_datos_formulario_cuentas_libro_boleta(){
        $datos=array(
            'neto' => Input::get('neto')? Input::get('neto') : array(),
            'exento' => Input::get('exento')? Input::get('exento') : array(),
            'iva' => Input::get('iva')? Input::get('iva') : array(),
            'total' => Input::get('total')? Input::get('total') : array(),
            'ajuste' => Input::get('ajuste')? Input::get('ajuste') : array()
        );
        return $datos;
    }

    public function get_datos_formulario_cuentas_boleta_honorarios(){
        $datos=array(
            'total' => Input::get('total')? Input::get('total') : array(),
            'retencion' => Input::get('retencion')? Input::get('retencion') : array(),
            'liquido' => Input::get('liquido')? Input::get('liquido') : array()
        );
        return $datos;
    }

}
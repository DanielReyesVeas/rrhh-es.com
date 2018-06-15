<?php

class TablasEstructurantesController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function tablas()
    {        
		$datos=array(
			'sexos' => Sexo::tablaSexos(),
			'nacionalidades' => Nacionalidad::tablaNacionalidades(),
			'tiposDeNomina' => TipoDeNomina::tablaTiposDeNomina(),
			'regimenesPrevisionalesTrabajador' => RegimenPrevisionalTrabajador::tablaRegimenesPrevisionalesTrabajador(),
			'tiposDeTrabajador' => TipoDeTrabajador::tablaTiposDeTrabajador(),
			'tiposDeLinea' => TipoDeLinea::tablaTiposDeLinea(),
			'movimientosDePersonal' => MovimientoDePersonal::tablaMovimientosDePersonal(),
			'tramosAsignacionFamiliar' => TramoAsignacionFamiliar::tablaTramosAsignacionFamiliar(),
			'codigosDeAfp' => CodigoDeAfp::tablaCodigosDeAfp(),
			'nombresInstitucionesApvAutorizadas' => NombreInstitucionApvAutorizada::tablaNombresInstitucionesApvAutorizadas(),
			'formasDePagoApviApvc' => FormaDePagoApviApvc::tablaFormasDePagoApviApvc(),
			'movimientosDePersonalAfiliadoVoluntario' => MovimientoDePersonalAfiliadoVoluntario::tablaMovimientosDePersonalAfiliadoVoluntario(),
			'codigosDeCajasExRegimen' => CodigoDeCajaExRegimen::tablaCodigosDeCajasExRegimen(),
			'codigosDeCajasExRegimenDesahucio' => CodigoDeCajaExRegimenDesahucio::tablaCodigosDeCajasExRegimenDesahucio(),
			'codigosDeInstitucionesDeSalud' => CodigoDeInstitucionDeSalud::tablaCodigosDeInstitucionesDeSalud(),
			'tiposMonedaDelPlanPactadoIsapre' => TipoMonedaDelPlanPactadoIsapre::tablaTiposMonedaDelPlanPactadoIsapre(),
			'codigosCcaf' => CodigoCcaf::tablaCodigosCcaf(),
			'codigosMutualidad' => CodigoMutualidad::tablaCodigosMutualidad(),
			'rutPagadoresDeSubsidio' => RutPagadorDeSubsidio::tablaRutPagadoresDeSubsidio()
		);
        
		return Response::json($datos);
	}

}
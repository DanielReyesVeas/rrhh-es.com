<?php

class TablaGlobalMensualController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    
    public function tablas()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#tabla-global-mensual');
		$listaTablas=array(
			'rentasTopeImponibles' => RentaTopeImponible::listaRentasTopeImponibles(),
			'rentasMinimasImponibles' => RentaMinimaImponible::listaRentasMinimasImponibles(),
			'ahorroPrevisionalVoluntario' => AhorroPrevisionalVoluntario::listaAhorroPrevisionalVoluntario(),
			'depositoConvenido' => DepositoConvenido::listaDepositoConvenido(),
			'seguroDeCesantia' => SeguroDeCesantia::listaSeguroDeCesantia(),
			'tasaCotizacionObligatorioAfp' => TasaCotizacionObligatorioAfp::listaTasaCotizacionObligatorioAfp(),
			'asignacionFamiliar' => AsignacionFamiliar::listaAsignacionFamiliar(),
			'cotizacionTrabajosPesados' => CotizacionTrabajoPesado::listaCotizacionTrabajosPesados()
		);
        
        $datos = array(
            'accesos' => $permisos,
            'ufAnterior' => ValorIndicador::ufAnterior(),
            'datos' => $listaTablas
        );
		return Response::json($datos);
	}
    
    public function modificar(){
        $datos = Input::all();
        
        if($datos){
            foreach($datos['rentasTopeImponibles'] as $dato){
                $id = $dato['id'];
                $tabla = RentaTopeImponible::find($id);
                $tabla->valor = $dato['valor'];
                $tabla->save();                     
            }
            foreach($datos['rentasMinimasImponibles'] as $dato){
                $id = $dato['id'];
                $tabla = RentaMinimaImponible::find($id);
                $tabla->valor = $dato['valor'];
                $tabla->save();                     
            }
            foreach($datos['ahorroPrevisionalVoluntario'] as $dato){
                $id = $dato['id'];
                $tabla = AhorroPrevisionalVoluntario::find($id);
                $tabla->valor = $dato['valor'];
                $tabla->save();                     
            }
            foreach($datos['depositoConvenido'] as $dato){
                $id = $dato['id'];
                $tabla = DepositoConvenido::find($id);
                $tabla->valor = $dato['valor'];
                $tabla->save();                     
            }
            foreach($datos['seguroDeCesantia'] as $dato){
                $id = $dato['id'];
                $tabla = SeguroDeCesantia::find($id);
                $tabla->financiamiento_empleador = $dato['financiamientoEmpleador'];
                $tabla->financiamiento_trabajador = $dato['financiamientoTrabajador'];
                $tabla->save();                     
            }
            foreach($datos['tasaCotizacionObligatorioAfp'] as $dato){
                $id = $dato['id'];
                $tabla = TasaCotizacionObligatorioAfp::find($id);
                $tabla->sis = $dato['sis'];
                $tabla->tasa_afp = $dato['tasaAfp'];
                $tabla->tasa_afp_independientes = ($dato['tasaAfp'] + $dato['sis']);
                $tabla->save();                     
            }
            foreach($datos['asignacionFamiliar'] as $dato){
                $id = $dato['id'];
                $tabla = AsignacionFamiliar::find($id);
                $tabla->monto = $dato['monto'];
                $tabla->renta_menor = $dato['rentaMenor'];
                $tabla->renta_mayor = $dato['rentaMayor'];
                $tabla->save();                     
            }
            foreach($datos['cotizacionTrabajosPesados'] as $dato){
                $id = $dato['id'];
                $tabla = CotizacionTrabajoPesado::find($id);
                $tabla->financiamiento_empleador = $dato['financiamientoEmpleador'];
                $tabla->financiamiento_trabajador = $dato['financiamientoTrabajador'];
                $tabla->valor = ($dato['financiamientoEmpleador'] + $dato['financiamientoTrabajador']); 
                $tabla->save();                     
            }
        }
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue modificada correctamente"
        );
        
        return Response::json($respuesta);
    }

}
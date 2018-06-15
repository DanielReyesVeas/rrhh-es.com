<?php

class DeclaracionTrabajador extends \Eloquent {
	protected  $table = "declaraciones_trabajadores";
    
    public function trabajador(){
        return $this->belongsTo('Trabajador','trabajador_id');
    }
    
    static function obtenerUltimoFolio()
    {
        $ultimoFolio = (DB::table('declaraciones_trabajadores')->max('folio') + 1);        
        return $ultimoFolio;
    }
    
    static function obtenerSiguienteFolio($folio)
    {
        $len = strlen($folio);
        for($i=$len; $i<7; $i++){
            $folio = '0' . $folio;
        }
        
        return $folio;
    }
    
    static function obtenerDeclaraciones($idAnio)
    {
        $declaraciones = DeclaracionTrabajador::where('anio_id', $idAnio)->orderBy('folio')->get();
        $lista = array();
        
        $totalSueldo = 0;
        $totalCotizacionPrevisional = 0;
        $totalRentaImponible = 0;
        $totalImpuestoUnico = 0;
        $totalMayorRetencion = 0;
        $totalRentaTotal = 0;
        $totalRentaNoGravada = 0;
        $totalRebaja = 0;
        $totalFactor = 0;
        $totalRentaAfecta = 0;
        $totalImpuestoUnicoRetenido = 0;
        $totalMayorRetencionImpuesto = 0;
        $totalRentaTotalExenta = 0;
        $totalRentaTotalNoGravada = 0;
        $totalRebajaZonasExtremas = 0;
        $totalRentaImponibleActualizada = 0;
                
        foreach($declaraciones as $declaracion){
            $actividad = str_split($declaracion->actividad);
            $lista[] = array(
                'rut' => $declaracion->trabajador->rut,
                /*'sueldo' => $declaracion->sueldo,
                'cotizacionPrevisional' => $declaracion->cotizacion_previsional,
                'rentaImponible' => $declaracion->renta_imponible,
                'impuestoUnico' => $declaracion->impuesto_unico,
                'mayorRetencion' => $declaracion->mayor_retencion,
                'rentaTotal' => $declaracion->renta_total,
                'rentaNoGravada' => $declaracion->renta_no_gravada,
                'rebaja' => $declaracion->rebaja,
                'factor' => $declaracion->factor,*/
                'rentaAfecta' => $declaracion->renta_afecta,
                'impuestoUnicoRetenido' => $declaracion->impuesto_unico_retenido,
                'mayorRetencionImpuesto' => $declaracion->mayor_retencion_impuesto,
                'rentaTotalNoGravada' => $declaracion->renta_total_no_gravada,
                'rentaTotalExenta' => $declaracion->renta_total_exenta,
                'rebajaZonasExtremas' => $declaracion->rebaja_zonas_extremas,
                'enero' => $actividad[0] ? 'X' : '',
                'febrero' => $actividad[1] ? 'X' : '',
                'marzo' => $actividad[2] ? 'X' : '',
                'abril' => $actividad[3] ? 'X' : '',
                'mayo' => $actividad[4] ? 'X' : '',
                'junio' => $actividad[5] ? 'X' : '',
                'julio' => $actividad[6] ? 'X' : '',
                'agosto' => $actividad[7] ? 'X' : '',
                'septiembre' => $actividad[8] ? 'X' : '',
                'octubre' => $actividad[9] ? 'X' : '',
                'noviembre' => $actividad[10] ? 'X' : '',
                'diciembre' => $actividad[11] ? 'X' : '',
                'folio' => $declaracion->folio
            );
            
            $totalSueldo += $declaracion->sueldo;
            $totalCotizacionPrevisional += $declaracion->cotizacion_previsional;
            $totalRentaImponible += $declaracion->renta_imponible;
            $totalImpuestoUnico += $declaracion->impuesto_unico;
            $totalMayorRetencion += $declaracion->mayor_retencion;
            $totalRentaTotal += $declaracion->renta_total;
            $totalRentaNoGravada += $declaracion->renta_no_gravada;
            $totalRebaja += $declaracion->rebaja;
            $totalFactor += $declaracion->factor;
            $totalRentaAfecta += $declaracion->renta_afecta;
            $totalImpuestoUnicoRetenido += $declaracion->impuesto_unico_retenido;
            $totalMayorRetencionImpuesto += $declaracion->mayor_retencion_impuesto;
            $totalRentaTotalNoGravada += $declaracion->renta_total_no_gravada;
            $totalRentaTotalExenta += $declaracion->renta_total_exenta;
            $totalRebajaZonasExtremas += $declaracion->rebaja_zonas_extremas;  
            $totalRentaImponibleActualizada += $declaracion->renta_imponible_actualizada;  
        }
        
        $totales = array(
            'sueldo' => $totalSueldo,
            'cotizacionPrevisional' => $totalCotizacionPrevisional,
            'rentaImponible' => $totalRentaImponible,
            'impuestoUnico' => $totalImpuestoUnico,
            'mayorRetencion' => $totalMayorRetencion,
            'rentaTotal' => $totalRentaTotal,
            'rentaNoGravada' => $totalRentaNoGravada,
            'rebaja' => $totalRebaja,
            'factor' => $totalFactor,
            'rentaAfecta' => $totalRentaAfecta,
            'impuestoUnicoRetenido' => $totalImpuestoUnicoRetenido,
            'mayorRetencionImpuesto' => $totalMayorRetencionImpuesto,
            'rentaTotalNoGravada' => $totalRentaTotalNoGravada,
            'rentaTotalExenta' => $totalRentaTotalExenta,
            'rebajaZonasExtremas' => $totalRebajaZonasExtremas,
            'rentaImponibleActualizada' => $totalRentaImponibleActualizada
        );
        
        $datos = array(
            'declaraciones' => $lista,
            'totales' => $totales
        );
        
        return $datos;
    }
    
}
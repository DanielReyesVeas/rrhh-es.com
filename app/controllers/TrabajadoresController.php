<?php
class TrabajadoresController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function miLiquidacionObservaciones_store()
    {
        $mes = \Session::get('mesActivo');
        $sidTrabajador = Input::get('sidTrabajador');
        $observaciones = Input::get('observaciones');
        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        $liquidacionObservacion = LiquidacionObservacion::where('periodo', $mes->mes)
            ->where('trabajador_id', $trabajador->id)->first();
        if(!$liquidacionObservacion){
            $liquidacionObservacion = new LiquidacionObservacion();
            $liquidacionObservacion->periodo = $mes->mes;
            $liquidacionObservacion->trabajador_id = $trabajador->id;
        }
        $liquidacionObservacion->observaciones = $observaciones;
        $liquidacionObservacion->save();
        $datos=array(
            'success' => true,
            'mensaje' => Config::get('constants.mensajes.store.ok')
        );
        return Response::json($datos);
    }

    public function miLiquidacionObservaciones_show()
    {

    }
    
    public function descargarLibroExcel($name)
    {
        $destination = public_path() . '/stories/' . $name . '.xls';
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="Libro.xls"'
        ]);    
    }  
    
    /*public function descargarLibroExcel($name)
    {
        $destination = public_path() . '/stories/' . $name . '.pdf';
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Libro.pdf"'
        ]);    
    } */
    
    public function descargarNominaExcel($name)
    {
        $destination = public_path() . '/stories/' . $name . '.xls';
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="Nomina.xls"'
        ]);    
    }
    
    public function descargarPlanillaExcel($name)
    {
        $destination = public_path() . '/stories/' . $name . '.xls';
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="PlanillaCostoEmpresa.xls"'
        ]);    
    }
    
    public function generarLibroExcel()
    {
        $datos = Input::all();
        $trabajadores = (array) $datos['trabajadores'];
        $conceptos = $datos['conceptos'];
        $tipo = $datos['tipo'];
        $excel = $datos['excel'];
        $reverse = 'ASC';
        $libroRemuneraciones = array();
        $haberes = array('imponibles' => array(), 'noImponibles' => array());
        $descuentos = array();
        $apvs = array();
        $empresa = \Session::get('empresa');
        $empresa->domicilio = $empresa->domicilio();
        $empresa->rutFormato = $empresa->rut_formato();
        $centroCostoNombre = false;
        
        $sumaSueldoBase = 0;
        $sumaInasistenciasAtrasos = 0;
        $sumaDiasTrabajados = 0;
        $sumaHorasExtra = 0;
        $sumaSueldo = 0;
        $sumaSalud = 0;
        $sumaAfp = 0;
        $sumaApv = 0;
        $sumaGratificacion = 0;
        $sumaOtrosImponibles = 0;
        $sumaMutual = 0;
        $sumaImpuestoRenta = 0;
        $sumaAnticipos = 0;
        $sumaAsignacionFamiliar = 0;
        $sumaNoImponibles = 0;
        $sumaHaberes = 0;
        $sumaImponibles = 0;
        $sumaTotalImponibles = 0;
        $sumaSeguroCesantia = 0;
        $sumaTotalDescuentos = 0;
        $sumaOtrosDescuentos = 0;
        $sumaSueldoLiquido = 0;
        $sumas = array('imponibles' => array(), 'noImponibles' => array(), 'descuentos' => array(), 'apvs' => array());  
        $centro = '';
        
        if($datos['reverse']){
            $reverse = 'DESC';
        }
        switch($datos['orden']){
            case 'rut':
                $orden = 'trabajador_rut';
                break;
            case 'apellidosOrden':
                $orden = 'trabajador_apellidos';
                break;
            case 'cargoOrden':
                $orden = 'trabajador_cargo';
                break;
            case 'seccionOrden':
                $orden = 'trabajador_seccion';
                break;
            case 'centroCostoOrden':
                $orden = 'trabajador_centro_costo';
                break;
            default:
                $orden = 'trabajador_apellidos';
                break;
        }
        
        $mes = \Session::get('mesActivo');
        $liquidaciones = Liquidacion::where('mes', $mes->mes)->orderBy($orden, $reverse)->get();        
        
        foreach($liquidaciones as $liquidacion){            
            if(in_array($liquidacion->trabajador_id, $trabajadores)){
                $detalles = $liquidacion->detalles;
                $apvis = $liquidacion->detalleApvi;                
                if($detalles->count()){
                    foreach($detalles as $detalle){
                        if($detalle->tipo_id==1){
                            if($detalle->tipo=='imponible'){
                                $index = $detalle->tipo_id . '-' . $detalle->detalle_id;
                                $haberes['imponibles'][$index] = $detalle->nombre;   
                                $liquidacion->$index = isset($liquidacion->$index) ? ($liquidacion->$index + $detalle->valor) : $detalle->valor;
                                $sumas['imponibles'][$index] = isset($sumas['imponibles'][$index]) ? ($sumas['imponibles'][$index] + $detalle->valor) : $detalle->valor;
                            }else{                                
                                $index = $detalle->tipo_id . '-' . $detalle->detalle_id;
                                $haberes['noImponibles'][$index] = $detalle->nombre;                        
                                $liquidacion->$index = isset($liquidacion->$index) ? ($liquidacion->$index + $detalle->valor) : $detalle->valor;
                                $sumas['noImponibles'][$index] = isset($sumas['noImponibles'][$index]) ? ($sumas['noImponibles'][$index] + $detalle->valor) : $detalle->valor;
                            }
                        }else{
                            $index = $detalle->tipo_id . '-' . $detalle->detalle_id;
                            $descuentos[$index] = $detalle->nombre;                                                    
                            $liquidacion->$index = isset($liquidacion->$index) ? ($liquidacion->$index + $detalle->valor) : $detalle->valor;
                            $sumas['descuentos'][$index] = isset($sumas['descuentos'][$index]) ? ($sumas['descuentos'][$index] + $detalle->valor) : $detalle->valor;
                        }
                    }
                }
                if($apvis->count()){
                    foreach($apvis as $apvi){
                        $index = $apvi->afp_id . $apvi->regimen;
                        $apvs[$index] = 'APV régimen ' . strtoupper($apvi->regimen) . ' AFP ' . $apvi->afp->glosa;   
                        $liquidacion->$index = isset($liquidacion->$index) ? ($liquidacion->$index + $apvi->monto) : $apvi->monto;
                        $sumas['apvs'][$index] = isset($sumas['apvs'][$index]) ? ($sumas['apvs'][$index] + $apvi->monto) : $apvi->monto;
                    }
                }
                if($liquidacion->colacion && false){                    
                    $haberes['noImponibles']['colacion_permanente'] = 'Colación Permanente';
                    $liquidacion->$index = isset($liquidacion->colacion_permanente) ? ($liquidacion->colacion_permanente + $liquidacion->colacion) : $liquidacion->colacion;
                    $sumas['noImponibles']['colacion_permanente'] = isset($sumas['noImponibles']['colacion_permanente']) ? ($sumas['noImponibles']['colacion_permanente'] + $liquidacion->colacion) : $liquidacion->colacion;
                }
                if($conceptos['centro_costo']){
                    if($liquidacion->trabajador_centro_costo){
                        $centro = $liquidacion->trabajador_centro_costo;
                    }else{
                        if($liquidacion->centroCosto){
                            if($centroCostoNombre){
                                $centro = $liquidacion->centroCosto->nombre;
                            }else{
                                $centro = $liquidacion->centroCosto->codigo;                            
                            }
                        }else{
                            $empleado = $liquidacion->trabajador->ficha();
                            if($empleado->centroCosto){
                                if($centroCostoNombre){
                                    $centro = $empleado->centroCosto->nombre;
                                }else{
                                    $centro = $empleado->centroCosto->codigo;
                                }
                            }else{
                                $centro = '';
                            }
                        }
                    }                    
                }
                $sis = 0;
                $cotizacionSalud = $liquidacion->totalSalud();
                $liquidacion->totalApvs = $liquidacion->totalApvs();
                $liquidacion->centro = $centro;
                $liquidacion->totalSalud = $cotizacionSalud;
                $liquidacion->total_otros_descuentos = ($liquidacion->total_otros_descuentos - $liquidacion->totalApvs() - $liquidacion->total_anticipos);
                if($liquidacion->detalleAfp){
                    if($liquidacion->detalleAfp->paga_sis=='empleado'){
                        $sis = $liquidacion->detalleAfp ? $liquidacion->detalleAfp->sis : 0;
                    }
                }
                $cotizacion = $liquidacion->detalleAfp ? $liquidacion->detalleAfp->cotizacion : 0;
                $liquidacion->totalAfp = ($cotizacion + $sis);
                $liquidacion->totalSeguroCesantia = $liquidacion->detalleSeguroCesantia ? $liquidacion->detalleSeguroCesantia->aporte_trabajador : 0;
                $libroRemuneraciones[] = $liquidacion;
                
                $sumaSueldoBase += $liquidacion->sueldo_base;
                $sumaDiasTrabajados += $liquidacion->dias_trabajados;
                $sumaInasistenciasAtrasos += $liquidacion->inasistencias;
                $sumaHorasExtra += $liquidacion->total_horas_extra;
                $sumaSueldo += $liquidacion->sueldo;
                $sumaSalud += $liquidacion->totalSalud;
                $sumaAfp += $cotizacion;
                $sumaApv += $liquidacion->totalApvs;
                $sumaGratificacion += $liquidacion->gratificacion;
                $sumaOtrosImponibles += $liquidacion->otros_imponibles;
                $sumaMutual += $liquidacion->total_mutual;
                $sumaImpuestoRenta += $liquidacion->impuesto_determinado;
                $sumaAnticipos += $liquidacion->total_anticipos;
                $sumaAsignacionFamiliar += $liquidacion->total_cargas;
                $sumaNoImponibles += $liquidacion->no_imponibles;
                $sumaHaberes += $liquidacion->total_haberes;
                $sumaImponibles += $liquidacion->imponibles;
                $sumaTotalImponibles += $liquidacion->renta_imponible;
                $sumaSeguroCesantia += $liquidacion->detalleSeguroCesantia ? $liquidacion->detalleSeguroCesantia->aporte_trabajador : 0;
                $sumaTotalDescuentos += $liquidacion->total_descuentos;
                $sumaOtrosDescuentos += $liquidacion->total_otros_descuentos;
                $sumaSueldoLiquido += $liquidacion->sueldo_liquido;
            }
        }
        $totales = array(
            'totalSueldoBase' => $sumaSueldoBase,
            //'totalMutual' => $sumaMutual,
            'totalDiasTrabajados' => $sumaDiasTrabajados,
            'totalInasistenciasAtrasos' => $sumaInasistenciasAtrasos,
            'totalHorasExtra' => $sumaHorasExtra,
            'totalSueldo' => $sumaSueldo,
            'totalSalud' => $sumaSalud,
            'totalAfp' => $sumaAfp,
            'totalApv' => $sumaApv,
            'totalGratificacion' => $sumaGratificacion,
            'totalOtrosImponibles' => $sumaOtrosImponibles,
            'totalImpuestoRenta' => $sumaImpuestoRenta,
            'totalAnticipos' => $sumaAnticipos,
            'totalAsignacionFamiliar' => $sumaAsignacionFamiliar,
            'totalSeguroCesantia' => $sumaSeguroCesantia,
            'totalNoImponibles' => $sumaNoImponibles,
            'totalHaberes' => $sumaHaberes,
            'totalImponibles' => $sumaImponibles,
            'totalTotalImponibles' => $sumaTotalImponibles,
            'totalOtrosDescuentos' => $sumaOtrosDescuentos,
            'totalTotalDescuentos' => $sumaTotalDescuentos,
            'totalSueldoLiquido' => $sumaSueldoLiquido,
            'sumas' => $sumas
        );            
        
        $haberes['imponibles'] = array_unique($haberes['imponibles']);
        $haberes['noImponibles'] = array_unique($haberes['noImponibles']);
        $descuentos = array_unique($descuentos);
        $apvs = array_unique($apvs);
        array_multisort($apvs, SORT_STRING);
        
        $datos = new stdClass();
        $datos->liquidaciones = $libroRemuneraciones;
        $datos->conceptos = $conceptos;
        $datos->haberes = $haberes;
        $datos->descuentos = $descuentos;
        $datos->apvs = $apvs;
        $datos->totales = $totales;
        $datos->empresa = $empresa;
        $datos->mes = strtoupper($mes->mesActivo);
        $filename = 'Libro';
        
        if($tipo==3){
            Excel::create("Libro", function($excel) use($datos) {
                $excel->sheet("Libro", function($sheet) use($datos) {
                    $sheet->loadView('excel.libroDetallado')->with('datos', $datos);
                });
            })->store('xls', public_path('stories'));
        }else if($tipo==1){
            if($excel){
                Excel::create("Libro", function($excel) use($datos) {
                    $excel->sheet("Libro", function($sheet) use($datos) {
                        $sheet->loadView('excel.libroLarge')->with('datos', $datos);
                    });
                })->store('xls', public_path('stories'));
            }else{
                $local = Config::get('cliente.LOCAL');
                $filename = 'LibroPDF.pdf';
                $destination = public_path() . '/stories/' . $filename;
                $pdf = new \Thujohn\Pdf\Pdf();
                $content = $pdf->load(View::make('pdf.libroLarge', array('datos' => $datos, 'local' => $local)), 'A4', 'landscape')->output();               
                File::put($destination, $content); 
            }
        }else{
            if($excel){
                Excel::create('Libro', function($excel) use($liquidaciones, $empresa, $mes, $totales) {
                    $excel->sheet('Libro', function($sheet) use($liquidaciones, $empresa, $mes, $totales) {

                        $i = 1;
                        $j = 'A';
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':L'.$i);
                        $sheet->setCellValue($a, $empresa->razon_social); 
                        $i++;  
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':L'.$i);
                        $sheet->setCellValue($a, 'RUT: ' . $empresa->rut_formato()); 
                        $i++;   
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':L'.$i);
                        $sheet->setCellValue($a, $empresa->actividad_economica); 
                        $i++;
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':L'.$i);
                        $sheet->setCellValue($a, $empresa->domicilio()); 
                        $i++;
                        $i++;
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':L'.$i);                        
                        $sheet->cell($a, function($cell) {
                            $cell->setValue('LIBRO DE REMUNERACIONES');
                            $cell->setFontFamily('Arial');
                            $cell->setFontSize(10);
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                        $i++;
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':L'.$i);
                        $sheet->cell($a, function($cell) use($mes) {
                            $cell->setValue($mes->nombre . ' ' . $mes->anio);
                            $cell->setFontFamily('Arial');
                            $cell->setFontSize(10);
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                        $i++;
                        $i++;
                        
                        $sheet->setFontBold(true);
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':'.$j.($i + 1));
                        $sheet->cell($a, function($cell) {
                            $cell->setValue('RUT');
                            $cell->setFontFamily('Arial');
                            $cell->setFontSize(10);
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                        $sheet->setBorder($a.':'.$j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':'.$j.($i + 1));
                        $sheet->cell($a, function($cell) {
                            $cell->setValue('NOMBRE');
                            $cell->setFontFamily('Arial');
                            $cell->setFontSize(10);
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                        $sheet->setBorder($a.':'.$j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'SUELDO BASE');                         
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'DÍAS TRAB'); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'INASIST Y ATRASO'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'H. EXTRA'); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'SUELDO'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'GRATIFIC.');                         
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'TOTAL IMPONIBLES'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'ASIGNA. FAMILIAR'); 
                        $sheet->setBorder($j.($i + 1), 'thin');                                                
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'TOTAL NO IMP'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'TOTAL HABERES'); 
                        $sheet->setBorder($j.($i + 1), 'thin');                        
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'AFP'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'APV'); 
                        $sheet->setBorder($j.($i + 1), 'thin');                                                                        
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'SALUD'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'SEGURO CESANTÍA'); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        
                        $sheet->setCellValue($a, 'ANTICIPOS'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'IMPUESTO'); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, 'OTROS DESC.'); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), 'TOTAL DESC.'); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->mergeCells($a.':'.$j.($i + 1));
                        $sheet->cell($a, function($cell) {
                            $cell->setValue('ALCANCE LÍQUIDO');
                            $cell->setFontFamily('Arial');
                            $cell->setFontSize(10);
                            $cell->setAlignment( 'center');
                        });
                        $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                        $sheet->setBorder($a.':'.$j.($i + 1), 'thin');
                        $j++;
                        
                        $sheet->setFontBold(false);
                        
                        
                        $i = 11;
                        
                        foreach($liquidaciones as $liquidacion){
                            $liquidacion->ap = $liquidacion->totalApvi();
                            $j = 'A';   
                            
                            $a = $j.$i;                            
                            $sheet->cell($a, function($cell) use($liquidacion) {
                                $cell->setValue(Funciones::formatear_rut($liquidacion->trabajador_rut));
                                $cell->setBorder('none', 'thin', 'none', 'none');
                            });
                            $sheet->cell($j.($i + 1), function($cell) {
                                $cell->setBorder('none', 'thin', 'thin', 'none');
                            });
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->cell($a, function($cell) use($liquidacion) {
                                $cell->setValue($liquidacion->trabajador_nombres . ' ' . $liquidacion->trabajador_apellidos);
                                $cell->setBorder('none', 'thin', 'none', 'none');
                            });
                            $sheet->cell($j.($i + 1), function($cell) {
                                $cell->setBorder('none', 'thin', 'thin', 'none');
                            });
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->sueldo_base)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), $liquidacion->dias_trabajados); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, $liquidacion->inasistencias); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), $liquidacion->horas_extra); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->sueldo)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($liquidacion->gratificacion)); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->imponibles)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($liquidacion->total_cargas)); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->no_imponibles)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($liquidacion->total_haberes)); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->totalAfp)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($liquidacion->totalApvs)); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->totalSalud)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($liquidacion->totalSeguroCesantia)); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;                                                                                                                                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->total_anticipos)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($liquidacion->impuesto_determinado)); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++;
                            
                            $a = $j.$i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($liquidacion->total_otros_descuentos)); 
                            $sheet->setBorder($a, 'thin');
                            $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($liquidacion->total_descuentos)); 
                            $sheet->setBorder($j.($i + 1), 'thin');
                            $j++; 
                            
                            $a = $j.$i;
                            $sheet->cell($a, function($cell) use($liquidacion) {
                                $cell->setValue(Funciones::formatoPesos($liquidacion->sueldo_liquido));
                                $cell->setBorder('none', 'thin', 'none', 'none');
                            });
                            $sheet->cell($j.($i + 1), function($cell) {
                                $cell->setBorder('none', 'thin', 'thin', 'none');
                            });
                            $j++;
                            
                            $i++;
                            $i++;
                        }
                        
                        $sheet->setFontBold(true);
                        $j = 'A';   
                        
                        $a = $j.$i;                            
                        $sheet->cell($a, function($cell) use($liquidacion) {            
                            $cell->setBorder('none', 'thin', 'none', 'none');
                        });
                        $sheet->cell($j.($i + 1), function($cell) {            
                            $cell->setBorder('none', 'thin', 'thin', 'none');
                        });
                        $j++;

                        $a = $j.$i;
                        $sheet->cell($a, function($cell) use($liquidacion) {
                            $cell->setBorder('none', 'thin', 'none', 'none');
                        });
                        $sheet->cell($j.($i + 1), function($cell) {
                            $cell->setValue('TOTAL GENERAL');
                            $cell->setBorder('none', 'thin', 'thin', 'none');
                        });
                        $j++;

                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalSueldoBase'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), $totales['totalDiasTrabajados']); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;

                        $a = $j.$i;
                        $sheet->setCellValue($a, $totales['totalInasistenciasAtrasos']); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), $totales['totalHorasExtra']); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;

                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalSueldo'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($totales['totalGratificacion'])); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalImponibles'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($totales['totalAsignacionFamiliar'])); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalNoImponibles'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($totales['totalHaberes'])); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                        
                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalAfp'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($totales['totalApv'])); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;

                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalSalud'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($totales['totalSeguroCesantia'])); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;
                                            
                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalAnticipos'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($totales['totalImpuestoRenta'])); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++;

                        $a = $j.$i;
                        $sheet->setCellValue($a, Funciones::formatoPesos($totales['totalOtrosDescuentos'])); 
                        $sheet->setBorder($a, 'thin');
                        $sheet->setCellValue($j.($i + 1), Funciones::formatoPesos($totales['totalTotalDescuentos'])); 
                        $sheet->setBorder($j.($i + 1), 'thin');
                        $j++; 

                        $a = $j.$i;
                        $sheet->cell($a, function($cell) use($totales) {
                            $cell->setValue(Funciones::formatoPesos($totales['totalSueldoLiquido']));
                            $cell->setBorder('none', 'thin', 'none', 'none');
                        });
                        $sheet->cell($j.($i + 1), function($cell) {
                            $cell->setBorder('none', 'thin', 'thin', 'none');
                        });
                        $j++;
                        
                    });

                })->store('xls', public_path('stories')); 
            }else{
                /*$filename = 'LibroPDF.pdf';
                $destination = public_path() . '/stories/' . $filename;
                $pdf = new \Thujohn\Pdf\Pdf();
                $content = $pdf->load(View::make('pdf.libroShort', array('datos' => $datos)))->output();                
                File::put($destination, $content); */
            }
        }
        
        $datos = array(
            'success' => true,
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $filename,
            'nombre' => $filename,
            'libro' => $libroRemuneraciones,
            'excel' => $excel,
            'haberes' => $haberes,
            'descuentos' => $descuentos,
            'apvs' => $apvs,
            'tipo' => $tipo,
            'totales' => $totales,
            'liq' => $liquidaciones
        );
        
        return Response::json($datos);
    }
    
    public function generarNominaExcel()
    {
        $datos = Input::all();
        $ids = (array) $datos['trabajadores'];
        $trabajadores = array();
        $mes = \Session::get('mesActivo');
        
        foreach($ids as $id){
            $trabajador = Trabajador::find($id);
            $empleado = $trabajador->ficha();
            $liquidacion = Liquidacion::where('trabajador_id', $trabajador->id)->where('mes', $mes->mes)->first();
            $trabajadores[] = array(
                'rut' => $trabajador->rut_formato(),
                'nombreCompleto' => $empleado->nombreCompleto(),
                'cargo' => $empleado->cargo ? $empleado->cargo->nombre : "",
                'codigoBanco' => $empleado->banco ? $empleado->banco->codigo : "",
                'nombreBanco' => $empleado->banco ? $empleado->banco->nombre : "",
                'tipoCuenta' => $empleado->tipoCuenta ? $empleado->tipoCuenta->nombre : "",
                'numeroCuenta' => $empleado->numero_cuenta ? $empleado->numero_cuenta : "",
                'monto' => $liquidacion->sueldo_liquido
            );
        }
        
        $filename = date("d-m-Y-H-i-s") . "_Nomina_" . $mes->nombre . "_" . $mes->anio . ".xls";
        
        Excel::create("Nomina", function($excel) use($trabajadores) {
            $excel->sheet("Nomina", function($sheet) use($trabajadores) {
                $sheet->loadView('excel.nomina')->with('trabajadores', $trabajadores)->getStyle('A1')->getAlignment();
            });
        })->store('xls', public_path('stories'));
        
        $destination = public_path('stories/' . $filename);
        
        $datos = array(
            'success' => true,
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $filename,
            'nombre' => 'Nomina',
            'nomina' => $trabajadores
        );
        
        return Response::json($datos);
    }
    
    /*public function generarNominaExcel()
    {
        $mes = \Session::get('mesActivo');
        
        $comunas = Glosa::where('tipo_estructura_id', 18)->orderBy('id', 'ASC')->get();
        $datos = array();
        foreach($comunas as $comuna){
            $datos[] = array(
                'id' => $comuna->id,
                'nombre' => $comuna->glosa
            );
        }
        
        $filename = date("d-m-Y-H-i-s") . "_Nomina_" . $mes->nombre . "_" . $mes->anio . ".xls";
        
        Excel::create("Nomina", function($excel) use($datos) {
            $excel->sheet("Nomina", function($sheet) use($datos) {
                $sheet->loadView('excel.comuna')->with('datos', $datos)->getStyle('A1')->getAlignment();
            });
        })->store('xls', public_path('stories'));
        
        $destination = public_path('stories/' . $filename);
        
        $datos = array(
            'success' => true,
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $filename,
            'nombre' => 'Nomina',
            'nomina' => $datos
        );
        
        return Response::json($datos);
    }*/
    
    public function descargarPlantillaTrabajadores(){
        
        $listaActivos = array();
        
        $destination = public_path('planillas/trabajadores.xlsx');
        
        Excel::load($destination, function($reader) {
            $reader->sheet('Códigos', function($sheet) {
                //Nacionalidades
                $i = 1;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 1'); 
                $i++;       
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Nacionalidades');
                $i++;                
                $nacionalidades = Glosa::codigosNacionalidades();
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;                 
                foreach($nacionalidades as $nacionalidad){                
                    $sheet->setCellValue('A'.$i, $nacionalidad['codigo']);
                    $sheet->setCellValue('B'.$i, $nacionalidad['glosa']);
                    $i++;
                }
                
                //Sexos
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 2'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Sexos');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');                
                $i++;  
                $sheet->setCellValue('A'.$i, 'F');
                $sheet->setCellValue('B'.$i, 'Femenino');
                $i++;  
                $sheet->setCellValue('A'.$i, 'M');
                $sheet->setCellValue('B'.$i, 'Masculino');
                $i++;
                
                //Estados Civiles
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 3'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Estados Civiles');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, 1);
                $sheet->setCellValue('B'.$i, 'Soltero/a');
                $i++;  
                $sheet->setCellValue('A'.$i, 2);
                $sheet->setCellValue('B'.$i, 'Casado/a');
                $i++;  
                $sheet->setCellValue('A'.$i, 3);
                $sheet->setCellValue('B'.$i, 'Divorciado/a');
                $i++;  
                $sheet->setCellValue('A'.$i, 4);
                $sheet->setCellValue('B'.$i, 'Viudo/a');
                $i++;  
                
                //Tipos Empleado
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 4'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Tipos de Empleado');
                $tipos = Glosa::codigosTiposEmpleado();
                $i++;                
                foreach($tipos as $tipo){                
                    $sheet->setCellValue('A'.$i, $tipo['codigo']);
                    $sheet->setCellValue('B'.$i, $tipo['glosa']);
                    $i++;
                }
                
                //Cargos
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 5'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Cargos');
                $cargos = Cargo::codigosCargos();
                $i++;                
                foreach($cargos as $cargo){                
                    $sheet->setCellValue('A'.$i, $cargo['codigo']);
                    $sheet->setCellValue('B'.$i, $cargo['glosa']);
                    $i++;
                }
                
                //Títulos
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 6'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Títulos');
                $titulos = Titulo::codigosTitulos();
                $i++;                
                foreach($titulos as $titulo){                
                    $sheet->setCellValue('A'.$i, $titulo['codigo']);
                    $sheet->setCellValue('B'.$i, $titulo['glosa']);
                    $i++;
                }
                
                //Secciones
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 7'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Secciones');
                $secciones = Seccion::codigosSecciones();
                $i++;                
                foreach($secciones as $seccion){                
                    $sheet->setCellValue('A'.$i, $seccion['codigo']);
                    $sheet->setCellValue('B'.$i, $seccion['glosa']);
                    $i++;
                }
                
                //Centros de Costo
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 8'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Centros de Costo');
                $centrosCosto = CentroCosto::codigosCentrosCosto();
                $i++;                
                foreach($centrosCosto as $centroCosto){                
                    $sheet->setCellValue('A'.$i, $centroCosto['codigo']);
                    $sheet->setCellValue('B'.$i, $seccion['glosa']);
                    $i++;
                }
                
                //Tiendas
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 9'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Tiendas');
                $tiendas = Tienda::codigosTiendas();
                $i++;                
                foreach($tiendas as $tienda){                
                    $sheet->setCellValue('A'.$i, $tienda['codigo']);
                    $sheet->setCellValue('B'.$i, $tienda['glosa']);
                    $i++;
                }
                
                //Tipos de Cuenta
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 10'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Tipos de Cuenta');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, 1);
                $sheet->setCellValue('B'.$i, 'Cuenta Corriente');
                $i++;  
                $sheet->setCellValue('A'.$i, 2);
                $sheet->setCellValue('B'.$i, 'Cuenta Vista');
                $i++;  
                $sheet->setCellValue('A'.$i, 3);
                $sheet->setCellValue('B'.$i, 'Cuenta Ahorro');
                $i++;  
                $sheet->setCellValue('A'.$i, 4);
                $sheet->setCellValue('B'.$i, 'CuentaRut');
                $i++;  
                $sheet->setCellValue('A'.$i, 5);
                $sheet->setCellValue('B'.$i, 'Chequera Electróica');
                $i++;  
                $sheet->setCellValue('A'.$i, 6);
                $sheet->setCellValue('B'.$i, 'Cuenta Gastos');
                $i++;  
                
                //Bancos
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 11'); 
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Bancos');
                $i++;                
                $bancos = Banco::codigosBancos();
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;                 
                foreach($bancos as $banco){                
                    $sheet->setCellValue('A'.$i, $banco['codigo']);
                    $sheet->setCellValue('B'.$i, $banco['glosa']);
                    $i++;
                }
                
                //Tipos de Contrato
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 12');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Tipos de Contrato');
                $i++;                
                $contratos = TipoContrato::codigosTiposContrato();
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;                 
                foreach($contratos as $contrato){                
                    $sheet->setCellValue('A'.$i, $contrato['codigo']);
                    $sheet->setCellValue('B'.$i, $contrato['glosa']);
                    $i++;
                }
                
                //Tipos de Jornada
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 13');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Tipos de Jornada');
                $i++;                
                $jornadas = Jornada::codigosTiposJornada();
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;                 
                foreach($jornadas as $jornada){                
                    $sheet->setCellValue('A'.$i, $jornada['codigo']);
                    $sheet->setCellValue('B'.$i, $jornada['glosa']);
                    $i++;
                }
                
                //Semana Corrida
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 14');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Semana Corrida');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, 0);
                $sheet->setCellValue('B'.$i, 'No');
                $i++;  
                $sheet->setCellValue('A'.$i, 1);
                $sheet->setCellValue('B'.$i, 'Sí');
                $i++;
                
                //Tipos de Moneda
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 15');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Tipos de Moneda');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, '$');
                $sheet->setCellValue('B'.$i, 'Pesos');
                $i++;  
                $sheet->setCellValue('A'.$i, 'UF');
                $sheet->setCellValue('B'.$i, 'UF');
                $i++;  
                $sheet->setCellValue('A'.$i, 'UTM');
                $sheet->setCellValue('B'.$i, 'UTM');
                $i++;
                
                //Tipos de Trabajador
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 16');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Tipos de Trabajador');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, 'Normal');
                $sheet->setCellValue('B'.$i, 'Normal');
                $i++;  
                $sheet->setCellValue('A'.$i, 'Socio');
                $sheet->setCellValue('B'.$i, 'Socio');
                $i++;
                
                //Exceso de Retiro
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 17');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Exceso de Retiro');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, 0);
                $sheet->setCellValue('B'.$i, 'No');
                $i++;  
                $sheet->setCellValue('A'.$i, 1);
                $sheet->setCellValue('B'.$i, 'Sí');
                $i++;
                
                //Gratificación
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 18');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Gratificación');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, 'm');
                $sheet->setCellValue('B'.$i, 'Mensual');
                $i++;  
                $sheet->setCellValue('A'.$i, 'a');
                $sheet->setCellValue('B'.$i, 'Anual');
                $i++;
                
                //Proporcional Colación, Movilización y Viático
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 19');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Proporcional Colación, Movilización y Viático');
                $i++;                
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('A'.$i, 0);
                $sheet->setCellValue('B'.$i, 'No');
                $i++;  
                $sheet->setCellValue('A'.$i, 1);
                $sheet->setCellValue('B'.$i, 'Sí');
                $i++;
                
                //Previsión
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 20');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Previsión');
                $previsiones = Glosa::codigosPrevision();
                $i++;                
                foreach($previsiones as $prevision){                
                    $sheet->setCellValue('A'.$i, $prevision['codigo']);
                    $sheet->setCellValue('B'.$i, $prevision['glosa']);
                    $i++;
                }
                
                //AFPs
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 21');
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de AFPs');
                $afps = Glosa::codigosAfps();
                $i++;                
                foreach($afps as $afp){                
                    $sheet->setCellValue('A'.$i, $afp['codigo']);
                    $sheet->setCellValue('B'.$i, $afp['glosa']);
                    $i++;
                }
                
                //ExCajas
                $i=1;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Tabla N° 22');
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Códigos de Ex Cajas');
                $exCajas = Glosa::codigosExCajas();
                $i++;                
                foreach($exCajas as $exCaja){                
                    $sheet->setCellValue('D'.$i, $exCaja['codigo']);
                    $sheet->setCellValue('E'.$i, $exCaja['glosa']);
                    $i++;
                }
            
                //Seguro de Cesantía
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Tabla N° 23');
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Códigos de Seguro de Cesantía');
                $i++;                
                $sheet->setCellValue('D'.$i, 'Código');
                $sheet->setCellValue('E'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('D'.$i, 0);
                $sheet->setCellValue('E'.$i, 'No');
                $i++;  
                $sheet->setCellValue('D'.$i, 1);
                $sheet->setCellValue('E'.$i, 'Sí');
                $i++;
                
                //AFPs Seguro de Cesantía
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Tabla N° 24');
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Códigos de AFPs Seguro de Cesantía');
                $afps = Glosa::codigosAfpsSeguro();
                $i++;                
                foreach($afps as $afp){                
                    $sheet->setCellValue('D'.$i, $afp['codigo']);
                    $sheet->setCellValue('E'.$i, $afp['glosa']);
                    $i++;
                }
                
                //Isapres
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Tabla N° 25');
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Códigos de Isapres');
                $isapres = Glosa::codigosIsapres();
                $i++;                
                foreach($isapres as $isapre){                
                    $sheet->setCellValue('D'.$i, $isapre['codigo']);
                    $sheet->setCellValue('E'.$i, $isapre['glosa']);
                    $i++;
                }
                
                //Cotización Isapre
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Tabla N° 26');
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Códigos de Cotización Isapre (Sólo si es Isapre, no Fonasa)');
                $i++;                
                $sheet->setCellValue('D'.$i, 'Código');
                $sheet->setCellValue('E'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('D'.$i, '$');
                $sheet->setCellValue('E'.$i, 'Pesos');
                $i++;  
                $sheet->setCellValue('D'.$i, 'UF');
                $sheet->setCellValue('E'.$i, 'UF');
                $i++;
                
                //Sindicato
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Tabla N° 27');
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Códigos de Sindicato');
                $i++;                
                $sheet->setCellValue('D'.$i, 'Código');
                $sheet->setCellValue('E'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('D'.$i, 0);
                $sheet->setCellValue('E'.$i, 'No');
                $i++;  
                $sheet->setCellValue('D'.$i, 1);
                $sheet->setCellValue('E'.$i, 'Sí');
                $i++;
                
                //Tramos Asignación Familiar
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Tabla N° 28');
                $i++;
                $sheet->mergeCells('D'.$i.':E'.$i);
                $sheet->setCellValue('D'.$i, 'Códigos de Tramo Asignación Familiar');
                $i++;                
                $sheet->setCellValue('D'.$i, 'Código');
                $sheet->setCellValue('E'.$i, 'Glosa');
                $i++;  
                $sheet->setCellValue('D'.$i, 'a');
                $sheet->setCellValue('E'.$i, 'Primer Tramo');
                $i++;
                $sheet->setCellValue('D'.$i, 'b');
                $sheet->setCellValue('E'.$i, 'Segundo Tramo');
                $i++;
                $sheet->setCellValue('D'.$i, 'c');
                $sheet->setCellValue('E'.$i, 'Tercer Tramo');
                $i++;
                $sheet->setCellValue('D'.$i, 'd');
                $sheet->setCellValue('E'.$i, 'Sin Derecho');
                $i++;       
                                     
            });  
            
            
            $reader->sheet('Comunas', function($sheet) {
                //Comunas
                $i = 1;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Tabla N° 29');                
                $i++;
                $sheet->mergeCells('A'.$i.':B'.$i);
                $sheet->setCellValue('A'.$i, 'Códigos de Comunas');
                $comunas = Comuna::codigosComunas();
                $i++;
                $sheet->setCellValue('A'.$i, 'Código');
                $sheet->setCellValue('B'.$i, 'Glosa');
                $i++;                
                foreach($comunas as $comuna){                
                    $sheet->setCellValue('A'.$i, $comuna['id']);
                    $sheet->setCellValue('B'.$i, $comuna['glosa']);
                    $i++;
                }                                

            });
            
            
            
        })->setFilename('planilla-trabajadores')->export('xlsx');

        
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="planilla-trabajadores.xlsx"'
        ]);    
    }
    
    public function descargarPlantilla($tipo){
        
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;
        $mes = \Session::get('mesActivo')->mes;
        $trabajadores = Trabajador::all();
        
        $listaActivos = array();
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $listaActivos[] = array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rutFormato' => $trabajador->rut_formato(),
                            'rut' => $trabajador->rut
                        );
                    }
                }
            }
        }
        
        $destination = public_path('planillas/planilla.xlsx');
        
        Excel::load($destination, function($reader) {
            $i = 2;
            $trabajadores = Trabajador::all();
            $finMes = \Session::get('mesActivo')->fechaRemuneracion;
            $mes = \Session::get('mesActivo')->mes;
            $listaActivos = array();
            if($trabajadores){
                foreach($trabajadores as $trabajador){
                    $empleado = $trabajador->ficha();
                    if($empleado){
                        if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                            $sheet = $reader->getActiveSheet();
                            $a = 'A' . $i;
                            $sheet->setCellValue($a, $trabajador['rut']);
                            $sheet->appendRow('a');   
                            $i++;
                        }
                    }
                }
            }

        })->setFilename('planilla-masivo' . $tipo)->export('xlsx');
        
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="planillas.xlsx"'
        ]);    
    }
    
    public function descargarPlantillaMasivo($tipo)
    {                    
        Excel::create('planilla-masivo-' . $tipo, function($reader) use ($tipo) {
            $reader->sheet('Haberes', function($sheet) use ($tipo) {
                $finMes = \Session::get('mesActivo')->fechaRemuneracion;
                $mes = \Session::get('mesActivo')->mes;
                $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes)));
                $lista = array();
                $trabajadores = Trabajador::all();
                $i = 3;
                $letter = 'C';
                
                if($trabajadores){
                    foreach($trabajadores as $trabajador){
                        $empleado = $trabajador->ficha();
                        if($empleado){
                            if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito >= $mesAnterior){
                                $lista[] = array(
                                    'rut' => $trabajador->rut_formato(),
                                    'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                    'nombreCompleto' => $empleado->apellidos . ', ' . $empleado->nombres
                                );
                            }                            
                        }                        
                    }
                    $lista = Funciones::ordenar($lista, 'apellidos');   
                }
                
                if(count($lista)){
                    foreach($lista as $trab){        
                       $sheet->cell('A'.$i, function($cell) use ($i, $trab) {
                            $cell->setValue($trab['rut']);
                        });
                        $sheet->cell('B'.$i, function($cell) use ($i, $trab) {
                            $cell->setValue($trab['nombreCompleto']);
                        });
                        $i++;
                    }
                }
                
                if($tipo=='haberes'){
                    $haberes = TipoHaber::all();
                    if($haberes){
                        foreach($haberes as $haber){
                            if($haber->id>15 || $haber->id==10 || $haber->id==11){
                                $sheet->cell($letter.'1', function($cell) use ($letter, $haber) {
                                    $cell->setValue($haber->codigo);                       
                                });
                                $sheet->cell($letter.'2', function($cell) use ($letter, $haber) {
                                    $cell->setValue($haber->nombre);                       
                                });
                                $letter++;
                            }
                        }
                    }
                }else{
                    $descuentos = TipoDescuento::all();
                    if($descuentos){
                        foreach($descuentos as $tipoDescuento){
                            if($tipoDescuento->id!=3){
                                $nombre = '';
                                if($tipoDescuento->estructura_descuento_id<3){                   
                                    $nombre = $tipoDescuento->nombre;
                                    $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                        $cell->setValue($tipoDescuento->codigo);                       
                                    });
                                    $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                        $cell->setValue($nombre);                       
                                    });
                                    $letter++;
                                }else if($tipoDescuento->estructura_descuento_id==6){
                                    if($tipoDescuento->nombre!='Caja de Compensación'){
                                        $nombre = $tipoDescuento->nombre;
                                        $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                            $cell->setValue($tipoDescuento->codigo);                       
                                        });
                                        $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                            $cell->setValue($nombre);                       
                                        });
                                        $letter++;
                                    }
                                }else if($tipoDescuento->estructura_descuento_id==3){
                                    $nombre = 'APVC AFP ' . $tipoDescuento->nombreAfp();
                                    $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                        $cell->setValue($tipoDescuento->codigo);                       
                                    });
                                    $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                        $cell->setValue($nombre);                       
                                    });
                                    $letter++;
                                }else if($tipoDescuento->estructura_descuento_id==7){
                                    $nombre = 'Cuenta de Ahorro AFP ' . $tipoDescuento->nombreAfp();
                                    $sheet->cell($letter.'1', function($cell) use ($letter, $tipoDescuento) {
                                        $cell->setValue($tipoDescuento->codigo);                       
                                    });
                                    $sheet->cell($letter.'2', function($cell) use ($letter, $nombre) {
                                        $cell->setValue($nombre);                       
                                    });
                                    $letter++;
                                }
                            }                                
                        }
                    }
                }
                
            });

        })->export('xlsx');
         
    }
    
    public function generarPlanillaExcel()
    {
        $datos = Input::all();
        $trabajadores = (array) $datos['trabajadores'];
        $planilla = array();
        
        $mes = \Session::get('mesActivo');
        $liquidaciones = Liquidacion::where('mes', $mes->mes)->get();
        
        foreach($liquidaciones as $liquidacion){
            if(in_array($liquidacion->trabajador_id, $trabajadores)){
                $liq = array(
                    'id' => $liquidacion->id,
                    'idTrabajador' => $liquidacion->trabajador_id,
                    'sid' => $liquidacion->sid,
                    'rutFormato' => $liquidacion->trabajador->rut_formato(),
                    'apellidos' => $liquidacion->trabajador->ficha()->apellidos,
                    'nombreCompleto' => $liquidacion->trabajador->ficha()->nombreCompleto(),
                    'cargo' => $liquidacion->trabajador_cargo,
                    'sueldo' => $liquidacion->sueldo,
                    'sueldoLiquido' => $liquidacion->sueldo_liquido,
                    'imponibles' => $liquidacion->renta_imponible,
                    'noImponibles' => $liquidacion->no_imponibles,
                    'mutual' => $liquidacion->detalleMutual ? $liquidacion->detalleMutual->cotizacion_accidentes : 0,
                    'seguroCesantia' => $liquidacion->detalleSeguroCesantia ? $liquidacion->detalleSeguroCesantia->aporte_empleador : 0,
                    'sis' => $liquidacion->detalleAfp ? $liquidacion->detalleAfp->sis : 0,
                    'caja' => $liquidacion->detalleCaja ? $liquidacion->detalleCaja->cotizacion : 0,
                    'fonasa' => $liquidacion->detalleIpsIslFonasa ? $liquidacion->detalleIpsIslFonasa->cotizacion_fonasa : 0,
                    'aportes' => $liquidacion->total_aportes
                );
                $planilla[] = $liq;
            }
        }
        
        $filename = date("d-m-Y-H-i-s") . "_Planilla_" . $mes->nombre . "_" . $mes->anio . ".xls";
        
        Excel::create("Planilla", function($excel) use($planilla) {
            $excel->sheet("Planilla", function($sheet) use($planilla) {
                $sheet->loadView('excel.planilla')->with('planilla', $planilla)->getStyle('A1')->getAlignment();
            });
        })->store('xls', public_path('stories'));
        
        $destination = public_path('stories/' . $filename);
        
        $datos = array(
            'success' => true,
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'datos' => $filename,
            'nombre' => 'Planilla',
            'nomina' => $planilla
        );
        
        return Response::json($datos);
    }
    
    public function importarPlanilla()
    {
        $insert = array();
        $val;
        if(Input::hasFile('file')){            
            $file = Input::file('file')->getRealPath();
            $data = Excel::selectSheets('Trabajadores')->load($file, function($reader){                
            })->get();
            $val = $data;
            if(!empty($data) && $data->count()){
                foreach($data as $key => $value){
                    if(isset($value->rut)){
                        $insert[] = array(
                            'rut' => trim($value->rut),
                            'nombres' => $value->nombres ? trim($value->nombres) : $value->nombres,
                            'apellidos' => $value->apellidos ? trim($value->apellidos) : $value->apellidos,
                            'nacionalidad' => $value->nacionalidad ? trim($value->nacionalidad) : $value->nacionalidad,
                            'sexo' => $value->sexo ? trim($value->sexo) : $value->sexo,
                            'estadoCivil' => $value->estado_civil ? trim($value->estado_civil) : $value->estado_civil,
                            'fechaNacimiento' => $value->fecha_nacimiento ? trim($value->fecha_nacimiento) : $value->fecha_nacimiento,
                            'direccion' => $value->direccion ? trim($value->direccion) : $value->direccion,
                            'comuna' => $value->comuna ? trim($value->comuna) : $value->comuna,
                            'telefonoFijo' => $value->telefono_fijo ? trim($value->telefono_fijo) : $value->telefono_fijo,
                            'celular' => $value->celular ? trim($value->celular) : $value->celular,
                            'celularEmpresa' => $value->celular_empresa ? trim($value->celular_empresa) : $value->celular_empresa,
                            'email' => $value->email ? trim($value->email) : $value->email,
                            'emailEmpresa' => $value->email_empresa ? trim($value->email_empresa) : $value->email_empresa,
                            'cargo' => $value->cargo ? trim($value->cargo) : $value->cargo,
                            'titulo' => $value->titulo ? trim($value->titulo) : $value->titulo,
                            'seccion' => $value->seccion ? trim($value->seccion) : $value->seccion,
                            'tipoCuenta' => $value->tipo_cuenta ? trim($value->tipo_cuenta) : $value->tipo_cuenta,
                            'banco' => $value->banco ? trim($value->banco) : $value->banco,
                            'gratificacion' => $value->gratificacion ? trim($value->gratificacion) : $value->gratificacion,
                            'centroCosto' => $value->centro_de_costo ? trim($value->centro_de_costo) : $value->centro_de_costo,
                            'tienda' => $value->tienda ? trim($value->tienda) : $value->tienda,
                            'numeroCuenta' => $value->numero_cuenta ? trim($value->numero_cuenta) : $value->numero_cuenta,
                            'fechaIngreso' => $value->fecha_ingreso ? trim($value->fecha_ingreso) : $value->fecha_ingreso,
                            'fechaReconocimiento' => $value->fecha_reconocimiento ? trim($value->fecha_reconocimiento) : $value->fecha_reconocimiento,
                            'fechaReconocimientoSCesantia' => $value->fecha_reconocimiento_s_cesantia ? trim($value->fecha_reconocimiento_s_cesantia) : $value->fecha_reconocimiento_s_cesantia,
                            'tipoContrato' => $value->tipo_contrato ? trim($value->tipo_contrato) : $value->tipo_contrato,
                            'fechaVencimiento' => $value->fecha_vencimiento ? trim($value->fecha_vencimiento) : $value->fecha_vencimiento,
                            'fechaFiniquito' => $value->fecha_finiquito ? trim($value->fecha_finiquito) : $value->fecha_finiquito,
                            'tipoJornada' => $value->tipo_jornada ? trim($value->tipo_jornada) : $value->tipo_jornada,
                            'semanaCorrida' => $value->semana_corrida ? trim($value->semana_corrida) : $value->semana_corrida,
                            'monedaSueldo' => $value->moneda_sueldo ? trim($value->moneda_sueldo) : $value->moneda_sueldo,
                            'sueldoBase' => $value->sueldo_base ? trim($value->sueldo_base) : $value->sueldo_base,
                            'tipoTrabajador' => $value->tipo_trabajador ? trim($value->tipo_trabajador) : $value->tipo_trabajador,
                            'tipo' => $value->tipo_empleado ? trim($value->tipo_empleado) : $value->tipo_empleado,
                            'excesoRetiro' => $value->exceso_retiro ? trim($value->exceso_retiro) : $value->exceso_retiro,
                            'monedaColacion' => $value->moneda_colacion ? trim($value->moneda_colacion) : $value->moneda_colacion,
                            'proporcionalColacion' => $value->proporcional_colacion ? trim($value->proporcional_colacion) : $value->proporcional_colacion,
                            'montoColacion' => $value->monto_colacion ? trim($value->monto_colacion) : $value->monto_colacion,
                            'monedaMovilizacion' => $value->moneda_movilizacion ? trim($value->moneda_movilizacion) : $value->moneda_movilizacion,
                            'proporcionalMovilizacion' => $value->proporcional_movilizacion ? trim($value->proporcional_movilizacion) : $value->proporcional_movilizacion,
                            'montoMovilizacion' => $value->monto_movilizacion ? trim($value->monto_movilizacion) : $value->monto_movilizacion,
                            'monedaViatico' => $value->moneda_viatico ? trim($value->moneda_viatico) : $value->moneda_viatico,
                            'proporcionalViatico' => $value->proporcional_viatico ? trim($value->proporcional_viatico) : $value->proporcional_viatico,
                            'montoViatico' => $value->monto_viatico ? trim($value->monto_viatico) : $value->monto_viatico,
                            'prevision' => $value->prevision ? trim($value->prevision) : $value->prevision,
                            'afp_ips' => $value->afpips ? trim($value->afpips) : $value->afpips,
                            'seguroCesantia' => $value->seguro_cesantia ? trim($value->seguro_cesantia) : $value->seguro_cesantia,
                            'afpSeguroCesantia' => $value->afp_seguro_cesantia ? trim($value->afp_seguro_cesantia) : $value->afp_seguro_cesantia,
                            'isapre' => $value->isapre ? trim($value->isapre) : $value->isapre,
                            'cotizacionIsapre' => $value->cotizacion_isapre ? trim($value->cotizacion_isapre) : $value->cotizacion_isapre,
                            'planIsapre' => $value->plan_isapre ? trim($value->plan_isapre) : $value->plan_isapre,
                            'sindicato' => $value->sindicato ? trim($value->sindicato) : $value->sindicato,
                            'monedaSindicato' => $value->moneda_sindicato ? trim($value->moneda_sindicato) : $value->moneda_sindicato,
                            'montoSindicato' => $value->monto_sindicato ? trim($value->monto_sindicato) : $value->monto_sindicato,
                            'vacaciones' => $value->vacaciones ? trim($value->vacaciones) : $value->vacaciones,
                            'tramo' => $value->tramo ? trim($value->tramo) : $value->tramo
                        );
                    }else{
                        $errores = array();
                        $errores[] = $value;
                    }
                }
            }
        }
        
        if(!isset($errores)){
            $errores = $this->comprobarErrores($insert);
        }
        
        if(!$errores){            
            $tabla = array();
            foreach($insert as $dato){
                if($dato['rut']){
                    $tabla[] = array(
                        'trabajador' => array(
                            'rut' => Funciones::formatear_rut($dato['rut']),
                            'nombreCompleto' => $dato['nombres'] . ' ' . $dato['apellidos'],
                            'fechaIngreso' => Funciones::formatoFecha($dato['fechaIngreso']),
                            'sueldoBase' => $dato['sueldoBase'],
                            'monedaSueldo' => strtoupper($dato['monedaSueldo'])
                        )                        
                    );
                }
            }
            
            $respuesta=array(
                'success' => true,
                'mensaje' => "La Información fue almacenada correctamente",
                'datos' => $tabla,
                'trabajadores' => $insert,
                'val' => $val
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
    
    public function generarIngresoMasivo()
    {
        $datos = Input::get();
        $idMes = \Session::get('mesActivo')->id;  
        $cont = 0;
        
        foreach($datos as $dato){
            if($dato['rut']){
                $trabajador = new Trabajador();
                $trabajador->sid = Funciones::generarSID();
                $trabajador->rut = $dato['rut'];                        
                $trabajador->save();                                

                if($dato['fechaFiniquito']){
                    $estado = 'Finiquitado';
                }else{
                    $estado = 'Ingresado';
                    $dato['fechaFiniquito'] = NULL;
                }
                
                $fecha = Funciones::primerDia(Funciones::regularizarFecha($dato['fechaIngreso']));

                $ficha = new FichaTrabajador();
                $ficha->trabajador_id = $trabajador->id;
                $ficha->mes_id = $idMes;            
                $ficha->nombres = $dato['nombres'];
                $ficha->apellidos = $dato['apellidos'];
                $ficha->nacionalidad_id = $dato['nacionalidad'];
                $ficha->sexo = strtolower(trim($dato['sexo']));
                $ficha->estado_civil_id = $dato['estadoCivil'];
                $ficha->fecha_nacimiento = Funciones::regularizarFecha($dato['fechaNacimiento']);
                $ficha->direccion = $dato['direccion'];
                $ficha->comuna_id = $dato['comuna'];
                $ficha->telefono = $dato['telefonoFijo'];
                $ficha->celular = $dato['celular'];
                $ficha->celular_empresa = $dato['celularEmpresa'];
                $ficha->email = $dato['email'];
                $ficha->email_empresa = $dato['emailEmpresa'];
                $ficha->tipo_id = $dato['tipo'];
                $ficha->cargo_id = $dato['cargo'];
                $ficha->titulo_id = $dato['titulo'];
                $ficha->seccion_id = $dato['seccion'];
                $ficha->tienda_id = $dato['tienda'];
                $ficha->centro_costo_id = $dato['centroCosto'];
                $ficha->tipo_cuenta_id = $dato['tipoCuenta'];
                $ficha->banco_id = $dato['banco'];
                $ficha->numero_cuenta = $dato['numeroCuenta'];
                $ficha->fecha_ingreso = Funciones::regularizarFecha($dato['fechaIngreso']);
                $ficha->fecha = $fecha;
                $ficha->fecha_reconocimiento = Funciones::regularizarFecha($dato['fechaReconocimiento']);
                $ficha->fecha_reconocimiento_cesantia = $dato['fechaReconocimientoSCesantia'] ? Funciones::regularizarFecha($dato['fechaReconocimientoSCesantia']) : NULL;
                $ficha->tipo_contrato_id = $dato['tipoContrato'];
                $ficha->fecha_vencimiento = $dato['fechaVencimiento'] ? Funciones::regularizarFecha($dato['fechaVencimiento']) : NULL;
                $ficha->fecha_finiquito = $dato['fechaFiniquito'] ? Funciones::regularizarFecha($dato['fechaFiniquito']) : NULL;
                $ficha->tipo_jornada_id = $dato['tipoJornada'];
                $ficha->semana_corrida = $dato['semanaCorrida'];
                $ficha->moneda_sueldo = strtoupper($dato['monedaSueldo']);
                $ficha->sueldo_base = $dato['sueldoBase'];
                $ficha->tipo_trabajador = ucwords(strtolower($dato['tipoTrabajador']));
                $ficha->exceso_retiro = $dato['excesoRetiro'];
                $ficha->gratificacion = $dato['gratificacion'];
                $ficha->moneda_colacion = strtoupper($dato['monedaColacion']);
                $ficha->proporcional_colacion = $dato['proporcionalColacion'];
                $ficha->monto_colacion = $dato['montoColacion'];
                $ficha->moneda_movilizacion = strtoupper($dato['monedaMovilizacion']);
                $ficha->proporcional_movilizacion = $dato['proporcionalMovilizacion'];
                $ficha->monto_movilizacion = $dato['montoMovilizacion'];
                $ficha->moneda_viatico = strtoupper($dato['monedaViatico']);
                $ficha->proporcional_viatico = $dato['proporcionalViatico'];
                $ficha->monto_viatico = $dato['montoViatico'];
                $ficha->prevision_id = $dato['prevision'];
                $ficha->afp_id = $dato['afp_ips'];
                $ficha->seguro_desempleo = $dato['seguroCesantia'];
                $ficha->afp_seguro_id = $dato['afpSeguroCesantia'];
                $ficha->isapre_id = $dato['isapre'];
                if($ficha->isapre_id==246){
                    $dato['cotizacionIsapre'] = '%';
                    $dato['planIsapre'] = 7;
                }
                $ficha->cotizacion_isapre = strtoupper($dato['cotizacionIsapre']);
                $ficha->monto_isapre = $dato['planIsapre'];
                $ficha->sindicato = $dato['sindicato'];
                $ficha->moneda_sindicato = strtoupper($dato['monedaSindicato']);
                $ficha->monto_sindicato = $dato['montoSindicato'];
                $ficha->tramo_id = $dato['tramo'];
                $ficha->estado = $estado;

                if($ficha->estado=='Ingresado'){
                    $ficha->tramo_id = FichaTrabajador::calcularTramo(Funciones::convertir($dato['sueldoBase'], $dato['monedaSueldo']));
                    if($dato['vacaciones']){
                        $trabajador->asignarVacaciones($dato['vacaciones']);
                    }else{
                        $trabajador->asignarVacaciones(0);                        
                    }
                    if($ficha->semana_corrida==1){
                        $trabajador->crearSemanaCorrida();
                    }
                }
                $trabajador->save();
                //$trabajador->crearUser();
                $ficha->save();
                
                if($dato['montoColacion'] > 0){
                    $nuevoHaber = new Haber();
                    $nuevoHaber->sid = Funciones::generarSID();
                    $nuevoHaber->trabajador_id = $trabajador->id;
                    $nuevoHaber->tipo_haber_id = 3;
                    $nuevoHaber->mes_id = null;
                    $nuevoHaber->por_mes = 0;
                    $nuevoHaber->rango_meses = 0;
                    $nuevoHaber->permanente = 1;
                    $nuevoHaber->todos_anios = 0;
                    $nuevoHaber->mes = null;
                    $nuevoHaber->desde = null;
                    $nuevoHaber->hasta = null;
                    $nuevoHaber->moneda = strtoupper($dato['monedaColacion']);
                    $nuevoHaber->monto = $dato['montoColacion'];
                    $nuevoHaber->proporcional = $dato['proporcionalColacion'];
                    $nuevoHaber->save(); 
                }
                if($dato['montoMovilizacion'] > 0){
                    $nuevoHaber = new Haber();
                    $nuevoHaber->sid = Funciones::generarSID();
                    $nuevoHaber->trabajador_id = $trabajador->id;
                    $nuevoHaber->tipo_haber_id = 4;
                    $nuevoHaber->mes_id = null;
                    $nuevoHaber->por_mes = 0;
                    $nuevoHaber->rango_meses = 0;
                    $nuevoHaber->permanente = 1;
                    $nuevoHaber->todos_anios = 0;
                    $nuevoHaber->mes = null;
                    $nuevoHaber->desde = null;
                    $nuevoHaber->hasta = null;
                    $nuevoHaber->moneda = strtoupper($dato['monedaMovilizacion']);
                    $nuevoHaber->monto = $dato['montoMovilizacion'];
                    $nuevoHaber->proporcional = $dato['proporcionalMovilizacion'];
                    $nuevoHaber->save(); 
                }
                if($dato['montoViatico'] > 0){
                    $nuevoHaber = new Haber();
                    $nuevoHaber->sid = Funciones::generarSID();
                    $nuevoHaber->trabajador_id = $trabajador->id;
                    $nuevoHaber->tipo_haber_id = 5;
                    $nuevoHaber->mes_id = null;
                    $nuevoHaber->por_mes = 0;
                    $nuevoHaber->rango_meses = 0;
                    $nuevoHaber->permanente = 1;
                    $nuevoHaber->todos_anios = 0;
                    $nuevoHaber->mes = null;
                    $nuevoHaber->desde = null;
                    $nuevoHaber->hasta = null;
                    $nuevoHaber->moneda = strtoupper($dato['monedaViatico']);
                    $nuevoHaber->monto = $dato['montoViatico'];
                    $nuevoHaber->proporcional = $dato['proporcionalViatico'];
                    $nuevoHaber->save(); 
                }
                $cont++;
            }

        }
        
        $text = $cont . ' Trabajadores';
        Logs::crearLog('#trabajadores', $cont, $text, 'Create', NULL, NULL, 'Importación Masiva'); 
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue almacenada correctamente",
            'trabajadores' => $datos
        );
        
        return Response::json($respuesta);
    }
    
    public function comprobarErrores($datos)
    {
        $lista = array();    
        $listaSecciones=array();
        Seccion::listaSecciones($listaSecciones, 0, 1);
        
        $trabajadores = Trabajador::all()->lists('rut');
        $estadosCiviles = EstadoCivil::all()->lists('id');
        $tiposCuentas = TipoCuenta::all()->lists('id');
        $bancos = Banco::all()->lists('id');
        $tiendas = Tienda::all()->lists('id');
        $centrosCosto = CentroCosto::all()->lists('id');
        $tiposContratos = TipoContrato::all()->lists('id');
        $tiposTrabajador = Glosa::where('tipo_estructura_id', 5)->orderBy('id', 'ASC')->get()->lists('id');
        $tiposJornadas = Jornada::all()->lists('id');
        $previsiones = Glosa::where('tipo_estructura_id', 4)->orderBy('id', 'ASC')->get()->lists('id');
        $afps = Glosa::where('tipo_estructura_id', 9)->orderBy('id', 'ASC')->get()->lists('id');
        $exCajas = Glosa::where('tipo_estructura_id', 13)->orderBy('id', 'ASC')->get()->lists('id');
        $isapres = Glosa::where('tipo_estructura_id', 15)->orderBy('id', 'ASC')->get()->lists('id');
        $afpsSeguro = Glosa::where('tipo_estructura_id', 9)->orderBy('id', 'ASC')->get()->lists('id');
        $tiposEmpleado = Glosa::where('tipo_estructura_id', 5)->orderBy('id', 'ASC')->get()->lists('id');
        $comunas = Comuna::all()->lists('id');
        $cargos = Cargo::all()->lists('id');
        $titulos = Titulo::all()->lists('id');
        $secciones = Seccion::all()->lists('id');
        $i = 1;
        
        foreach($datos as $dato){            
            if($dato){
                $isRut = false;
                if($dato['rut']){
                    $isError = false;
                    $listaErrores = array();
                
                    if($dato['rut']){
                        if(strlen($dato['rut'])>=8){
                            if(strlen($dato['rut'])>9){
                                $listaErrores[] = 'El RUT ' . $dato['rut'] . ' es inválido, recuerde ingresar sólo dígitos, sin puntos ni guion.';
                                $isError = true;
                            }else{
                                if(!Funciones::comprobarRut($dato['rut'])){
                                    $listaErrores[] = 'El RUT ' . $dato['rut'] . ' es inválido.';
                                    $isError = true;
                                }else{     
                                    $isRut = true;
                                    if(in_array($dato['rut'], $trabajadores)){
                                        $listaErrores[] = 'El RUT ' . Funciones::formatear_rut($dato['rut']) . ' ya se encuentra registrado.';
                                        $isError = true;                                        
                                    }
                                }
                            }
                        }else{
                            $listaErrores[] = 'El RUT ' . $dato['rut'] . ' es inválido.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo RUT es obligatorio.';
                        $isError = true;
                    }
                    if(!$dato['nombres']){
                        $listaErrores[] = 'El campo Nombres es obligatorio.';
                        $isError = true;
                    }
                    if(!$dato['apellidos']){
                        $listaErrores[] = 'El campo Apellidos es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['nacionalidad'])){
                        if($dato['nacionalidad']!=3 && $dato['nacionalidad']!=4){
                            $listaErrores[] = 'El código de Nacionalidad "' . $dato['nacionalidad'] . '" no existe.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Nacionalidad es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['sexo'])){
                        if(strtolower(trim($dato['sexo']))!="f" && strtolower(trim($dato['sexo']))!="m"){
                            $listaErrores[] = 'El código de Sexo "' . $dato['sexo'] . '" es incorrecto, recuerda que los códigos son "F" o "M".';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Sexo es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['estadoCivil'])){
                        if(!in_array($dato['estadoCivil'], $estadosCiviles)){
                            $listaErrores[] = 'El código de Estado Civíl "' . $dato['estadoCivil'] . '" no existe.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Estado Civil es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['fechaNacimiento'])){
                        if(!Funciones::comprobarFecha($dato['fechaNacimiento'], 'Y-m-d')){
                           $listaErrores[] = 'El formato de Fecha de Nacimiento "' . $dato['fechaNacimiento'] . '" es incorrecto, recuerda que el formato es "DD-MM-AAAA".';
                            $isError = true; 
                        }
                    }else{
                        $listaErrores[] = 'El campo Fecha de Nacimiento es obligatorio.';
                        $isError = true;
                    }
                    if(!isset($dato['direccion'])){
                        $listaErrores[] = 'El campo Dirección es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['comuna'])){
                        if(!in_array($dato['comuna'], $comunas)){
                            $listaErrores[] = 'El código de Comuna "' . $dato['comuna'] . '" no existe.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Comuna es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['tipo'])){
                        if(!in_array($dato['tipo'], $tiposEmpleado)){
                            $listaErrores[] = 'El Tipo de Empleado "' . $dato['tipo'] . '" no existe.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Tipo de Empleado es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['cargo'])){
                        if(!in_array($dato['cargo'], $cargos)){
                            $listaErrores[] = 'El Cargo "' . $dato['cargo'] . '" no existe.';
                            $isError = true;
                        }
                    }                
                    if(isset($dato['titulo'])){
                        if(!in_array($dato['titulo'], $titulos)){
                            $listaErrores[] = 'El Título "' . $dato['titulo'] . '" no existe.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['seccion'])){
                        if(!in_array($dato['seccion'], $secciones)){
                            $listaErrores[] = 'La Sección "' . $dato['seccion'] . '" no existe.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['centroCosto'])){
                        if(!in_array($dato['centroCosto'], $centrosCosto)){
                            $listaErrores[] = 'El Centro de Costo "' . $dato['centroCosto'] . '" no existe.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['tienda'])){
                        if(!in_array($dato['tienda'], $tiendas)){
                            $listaErrores[] = 'La Tienda "' . $dato['tienda'] . '" no existe.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['gratificacion'])){
                        if(strtoupper($dato['gratificacion'])!='M' && strtoupper($dato['gratificacion'])!='A'){
                            $listaErrores[] = 'El código de Gratificación "' . $dato['gratificacion'] . '" no existe.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['telefonoFijo'])){
                        if(!is_numeric($dato['telefonoFijo'])){
                            $listaErrores[] =  'El formato de Teléfono Fijo "' . $dato['telefonoFijo'] . '" es incorrecto, recuerda deben ser sólo valores núméricos.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['celular'])){
                        if(!is_numeric($dato['celular'])){
                            $listaErrores[] =  'El formato de Celular "' . $dato['celular'] . '" es incorrecto, recuerda deben ser sólo valores núméricos.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Celular es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['celularEmpresa']) && !is_numeric($dato['celularEmpresa'])){
                        $listaErrores[] =  'El formato de Celular Empresa "' . $dato['celularEmpresa'] . '" es incorrecto, recuerda deben ser sólo valores núméricos.';
                        $isError = true;
                    }
                    if(isset($dato['email'])){
                        if(!filter_var($dato['email'], FILTER_VALIDATE_EMAIL)) {
                            /*$listaErrores[] =  'El formato de Email "' . $dato['email'] . '" es incorrecto, recuerda que el formato es "nombre@sitio.com".';
                            $isError = false;*/
                        }
                    }else{
                        $listaErrores[] = 'El campo Email es obligatorio.';
                        $isError = true;                    
                    }
                    if(isset($dato['emailEmpresa'])){
                        if(!filter_var($dato['emailEmpresa'], FILTER_VALIDATE_EMAIL)) {
                            $listaErrores[] =  'El formato de Email Empresa "' . $dato['emailEmpresa'] . '" es incorrecto, recuerda que el formato es "nombre@sitio.com".';
                            $isError = true;
                        }
                    }
                    if(isset($dato['tipoCuenta'])){
                        if(!in_array($dato['tipoCuenta'], $tiposCuentas)){
                            $listaErrores[] = 'El código de Tipo de Cuenta "' . $dato['tipoCuenta'] . '" no existe.';
                            $isError = true;
                        }
                    }else{
                        /*$listaErrores[] = 'El campo Tipo de Cuenta es obligatorio.';
                        $isError = true;*/
                    }
                    if(isset($dato['banco'])){
                        if(!in_array($dato['banco'], $bancos)){
                            $listaErrores[] = 'El código de Banco "' . $dato['banco'] . '" no existe.';
                            $isError = true;
                        }
                    }else{
                        /*$listaErrores[] = 'El campo Banco es obligatorio.';
                        $isError = true;*/
                    }
                    if(!isset($dato['numeroCuenta'])){
                        /*$listaErrores[] = 'El campo Número de Cuenta es obligatorio.';
                        $isError = true;*/
                    }
                    if(isset($dato['fechaIngreso'])){
                        if(!Funciones::comprobarFecha($dato['fechaIngreso'], 'Y-m-d')){
                           $listaErrores[] = 'El formato de Fecha de Ingreso "' . $dato['fechaIngreso']. '" es incorrecto, recuerda que el formato es "DD-MM-AAAA".';
                            $isError = true; 
                        }
                    }else{
                        $listaErrores[] = 'El campo Fecha de Ingreso es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['fechaReconocimiento'])){
                        if(!Funciones::comprobarFecha($dato['fechaReconocimiento'], 'Y-m-d')){
                           $listaErrores[] = 'El formato de Fecha de Reconocimiento "' . $dato['fechaReconocimiento'] . '" es incorrecto, recuerda que el formato es "DD-MM-AAAA".';
                            $isError = true; 
                        }
                    }else{
                        $listaErrores[] = 'El campo Fecha de Reconocimiento es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['fechaReconocimientoSCesantia'])){
                        if(!Funciones::comprobarFecha($dato['fechaReconocimientoSCesantia'], 'Y-m-d')){
                           $listaErrores[] = 'El formato de Fecha de Reconocimiento S. de Cesantía "' . $dato['fechaReconocimientoSCesantia'] . '" es incorrecto, recuerda que el formato es "DD-MM-AAAA".';
                            $isError = true; 
                        }
                    }else{
                        /*$listaErrores[] = 'El campo Fecha de Reconocimiento S. de Cesantía es obligatorio.';
                        $isError = true;*/
                    }
                    if(isset($dato['tipoContrato'])){
                        if(!in_array($dato['tipoContrato'], $tiposContratos)){
                            $listaErrores[] = 'El código de Tipo de Contrato "' . $dato['tipoContrato'] . '" no existe.';
                            $isError = true;
                        }else{
                            if($dato['tipoContrato']>1){
                                if(isset($dato['fechaVencimiento'])){
                                    if(!Funciones::comprobarFecha($dato['fechaVencimiento'], 'd-m-Y')){
                                       $listaErrores[] = 'El formato de Fecha de Vencimiento "' . $dato['fechaVencimiento'] . '" es incorrecto, recuerda que el formato es "DD-MM-AAAA".';
                                        $isError = true; 
                                    }
                                }else{
                                    $listaErrores[] = 'El campo Fecha de Vencimiento es obligatorio si el trabajador no posee contrato Indefinido.';
                                    $isError = true;
                                }
                            }
                        }
                    }else{
                        $listaErrores[] = 'El campo Tipo de Contrato es obligatorio.';
                        $isError = true;
                    }                    
                    if(isset($dato['fechaFiniquito'])){
                        if(!Funciones::comprobarFecha($dato['fechaFiniquito'], 'd-m-Y')){
                           $listaErrores[] = 'El formato de Fecha de Finiquito "' . $dato['fechaFiniquito'] . '" es incorrecto, recuerda que el formato es "DD-MM-AAAA".';
                            $isError = true; 
                        }
                    }
                    if(isset($dato['tipoJornada'])){
                        if(!in_array($dato['tipoJornada'], $tiposJornadas)){
                            $listaErrores[] = 'El código de Tipo de Jornada "' . $dato['tipoJornada'] . '" no existe.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Tipo de Jornada es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['semanaCorrida'])){
                        if($dato['semanaCorrida']!=0 && $dato['semanaCorrida']!=1){
                            $listaErrores[] = 'El código de Semana Corrida "' . $dato['nacionalidad'] . '" es incorrecto, recuerda que los códigos son 0 o 1.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Semana Corrida es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['monedaSueldo'])){
                        if($dato['monedaSueldo']!='$' && strtoupper($dato['monedaSueldo'])!='UF' && strtoupper($dato['monedaSueldo'])!='UTM'){
                            $listaErrores[] = 'El formato de Moneda de Sueldo Base "' . $dato['monedaSueldo'] . '" es incorrecto, recuerda que los formatos son "$", "UF" o "UTM".';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Moneda Sueldo Base es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['sueldoBase'])){
                        if(!is_numeric($dato['sueldoBase'])){
                            $listaErrores[] = 'El formato del Monto Sueldo Base"' . $dato['sueldoBase'] . '" es incorrecto, recuerda que este campo acepta sólo valores numéricos.';
                            $isError = true;
                        }
                    }else{
                        $listaErrores[] = 'El campo Sueldo Base es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['tipoTrabajador'])){
                        if(strtoupper($dato['tipoTrabajador'])!='NORMAL' && strtoupper($dato['tipoTrabajador'])!='SOCIO'){
                            $listaErrores[] = 'El código de Tipo de Trabajador "' . $dato['tipoTrabajador'] . '" es incorrecto, recuerda que los códigos son "Normal" o "Socio".';
                            $isError = true;
                        }else{
                            if(strtoupper($dato['tipoTrabajador'])=='SOCIO'){
                                if(isset($dato['excesoRetiro'])){
                                    if($dato['excesoRetiro']!=0 && $dato['excesoRetiro']!=1){
                                        $listaErrores[] = 'El código de Exceso de Retiro "' . $dato['excesoRetiro'] . '" es incorrecto, recuerda que los códigos son 0 o 1.';
                                        $isError = true;
                                    }
                                }
                            }
                        }
                    }else{
                        $listaErrores[] = 'El campo Tipo de Trabajador es obligatorio.';
                        $isError = true;
                    }
                    if(isset($dato['monedaColacion']) || isset($dato['montoColacion']) || isset($dato['proporcionalColacion'])){
                        if(isset($dato['monedaColacion'])){
                            if($dato['monedaColacion']!='$' && strtoupper($dato['monedaColacion'])!='UF' && strtoupper($dato['monedaColacion'])!='UTM'){
                                $listaErrores[] = 'El formato de Moneda Colación "' . $dato['monedaColacion'] . '" es incorrecto, recuerda que los formatos son "$", "UF" o "UTM".';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Moneda Colación es obligatorio si el trabajador posee Colación.';
                            $isError = true;
                        }
                        if(isset($dato['montoColacion'])){
                            if(!is_numeric($dato['montoColacion'])){
                                $listaErrores[] = 'El formato de Moneda de Colación "' . $dato['montoColacion'] . '" es incorrecto, recuerda que este campo acepta sólo valores numéricos.';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Monto Colación es obligatorio si el trabajador posee Colación.';
                            $isError = true;
                        }
                        if(isset($dato['proporcionalColacion'])){
                            if($dato['proporcionalColacion']!=0 && $dato['proporcionalColacion']!=1){
                                $listaErrores[] = 'El código de Proporcional Colación "' . $dato['proporcionalColacion'] . '" es incorrecto, recuerda que los códigos son 0 o 1.';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Proporcional Colación es obligatorio si el trabajador posee Colación.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['monedaMovilizacion']) || isset($dato['montoMovilizacion']) || isset($dato['proporcionalMovilizacion'])){
                        if(isset($dato['monedaMovilizacion'])){
                            if($dato['monedaMovilizacion']!='$' && strtoupper($dato['monedaMovilizacion'])!='UF' && strtoupper($dato['monedaMovilizacion'])!='UTM'){
                                $listaErrores[] = 'El formato de Moneda Movilización "' . $dato['monedaMovilizacion'] . '" es incorrecto, recuerda que los formatos son "$", "UF" o "UTM".';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Moneda Movilización es obligatorio si el trabajador posee Movilización.';
                            $isError = true;
                        }
                        if(isset($dato['montoMovilizacion'])){
                            if(!is_numeric($dato['montoMovilizacion'])){
                                $listaErrores[] = 'El formato de Moneda de Movilización "' . $dato['montoMovilizacion'] . '" es incorrecto, recuerda que este campo acepta sólo valores numéricos.';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Monto Movilización es obligatorio si el trabajador posee Movilización.';
                            $isError = true;
                        }
                        if(isset($dato['proporcionalMovilizacion'])){
                            if($dato['proporcionalMovilizacion']!=0 && $dato['proporcionalMovilizacion']!=1){
                                $listaErrores[] = 'El código de Proporcional Movilización "' . $dato['proporcionalMovilizacion'] . '" es incorrecto, recuerda que los códigos son 0 o 1.';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Proporcional Movilización es obligatorio si el trabajador posee Movilización.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['monedaViatico']) || isset($dato['montoViatico']) || isset($dato['proporcionalViatico'])){
                        if(isset($dato['monedaViatico'])){
                            if($dato['monedaViatico']!='$' && strtoupper($dato['monedaViatico'])!='UF' && strtoupper($dato['monedaViatico'])!='UTM'){
                                $listaErrores[] = 'El formato de Moneda Viático "' . $dato['monedaViatico'] . '" es incorrecto, recuerda que los formatos son "$", "UF" o "UTM".';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Moneda Viático es obligatorio si el trabajador posee Viático.';
                            $isError = true;
                        }
                        if(isset($dato['montoViatico'])){
                            if(!is_numeric($dato['montoViatico'])){
                                $listaErrores[] = 'El formato de Moneda de Viático "' . $dato['montoViatico'] . '" es incorrecto, recuerda que este campo acepta sólo valores numéricos.';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Monto Viático es obligatorio si el trabajador posee Viático.';
                            $isError = true;
                        }
                        if(isset($dato['proporcionalViatico'])){
                            if($dato['proporcionalViatico']!=0 && $dato['proporcionalViatico']!=1){
                                $listaErrores[] = 'El código de Proporcional Viático "' . $dato['proporcionalViatico'] . '" es incorrecto, recuerda que los códigos son 0 o 1.';
                                $isError = true;
                            }
                        }else{
                            $listaErrores[] = 'El campo Proporcional Viático es obligatorio si el trabajador posee Viático.';
                            $isError = true;
                        }
                    }
                    if(isset($dato['prevision'])){
                        if(!in_array($dato['prevision'], $previsiones)){
                            $listaErrores[] = 'El código de Previsión "' . $dato['prevision'] . '" no existe.';
                            $isError = true;
                        }else{
                            if($dato['prevision']==8){
                                if(isset($dato['afp_ips'])){
                                    if(!in_array($dato['afp_ips'], $afps)){
                                        $listaErrores[] = 'El código de AFP "' . $dato['afp_ips'] . '" no existe.';
                                        $isError = true;
                                    }
                                }else{
                                    $listaErrores[] = 'El campo AFP es obligatorio si la previsión es AFP.';
                                    $isError = true;
                                }  
                            }else if($dato['prevision']==9){
                                if(isset($dato['afp_ips'])){
                                    if(!in_array($dato['afp_ips'], $exCajas)){
                                        $listaErrores[] = 'El código de IPS "' . $dato['afp_ips'] . '" no existe.';
                                        $isError = true;
                                    }
                                }else{
                                    $listaErrores[] = 'El campo IPS es obligatorio si la previsión es IPS.';
                                    $isError = true;
                                }  
                            }
                        }
                    }else{
                        $listaErrores[] = 'El campo Previsión es obligatorio.';
                        $isError = true;
                    }                           
                    if(isset($dato['seguroCesantia'])){
                        if($dato['seguroCesantia']!=0 && $dato['seguroCesantia']!=1){
                            $listaErrores[] = 'El código de Seguro de Cesantía "' . $dato['seguroCesantia'] . '" es incorrecto, recuerda que los códigos son 0 o 1.';
                            $isError = true;
                        }else{
                            if($dato['seguroCesantia']==1){
                                if(isset($dato['afpSeguroCesantia'])){
                                    if(!in_array($dato['afpSeguroCesantia'], $afpsSeguro)){
                                        $listaErrores[] = 'El código de AFP Seguro de Cesantía "' . $dato['afpSeguroCesantia'] . '" no existe.';
                                        $isError = true;
                                    }
                                }else{
                                    $listaErrores[] = 'El campo AFP Seguro de Cesantía es obligatorio si el trabajador posee Seguro de Cesantía.';
                                    $isError = true;
                                }
                            }
                        }
                    }else{
                        $listaErrores[] = 'El campo Seguro de Cesantía es obligatorio.';
                        $isError = true;
                    }      
                    if(isset($dato['isapre'])){
                        if(!in_array($dato['isapre'], $isapres)){
                            $listaErrores[] = 'El código de Isapre "' . $dato['isapre'] . '" no existe.';
                            $isError = true;                    
                        }else{
                            if($dato['isapre']!=240 && $dato['isapre']!=246){
                                if(isset($dato['cotizacionIsapre'])){
                                    if($dato['cotizacionIsapre']!='$' && strtoupper($dato['cotizacionIsapre'])!='UF'){
                                        $listaErrores[] = 'El código de Cotización de Isapre "' . $dato['cotizacionIsapre'] . '" es incorrecto, recuerda que los códigos son "$" o "UF".';
                                        $isError = true;
                                    }
                                }else{
                                    $listaErrores[] = 'El campo Cotización Isapre es obligatorio si el trabajador posee Isapre.';
                                    $isError = true;
                                }
                                if(isset($dato['planIsapre'])){
                                    if(!is_numeric($dato['planIsapre'])){
                                        $listaErrores[] =  'El formato de Plan Isapre "' . $dato['planIsapre'] . '" es incorrecto, recuerda deben ser sólo valores núméricos.';
                                        $isError = true;
                                    }
                                }else{
                                    $listaErrores[] = 'El campo Plan Isapre es obligatorio si el trabajador posee Isapre.';
                                    $isError = true;
                                }
                            }
                        }
                    }else{
                        $listaErrores[] = 'El campo Isapre es obligatorio.';
                        $isError = true;
                    }     
                    if(isset($dato['sindicato'])){
                        if($dato['sindicato']!=0 && $dato['sindicato']!=1){
                            $listaErrores[] = 'El código de Sindicato "' . $dato['sindicato'] . '" es incorrecto, recuerda que los códigos son 0 o 1.';
                            $isError = true;
                        }else{
                            if($dato['sindicato']==1){
                                if(isset($dato['monedaSindicato'])){
                                    if($dato['monedaSindicato']!='$' && strtoupper($dato['monedaSindicato'])!='UF' && strtoupper($dato['monedaSindicato'])!='UTM'){
                                        $listaErrores[] = 'El formato de Moneda de Sindicato "' . $dato['monedaSindicato'] . '" es incorrecto, recuerda que los formatos son "$", "UF" o "UTM".';
                                        $isError = true;
                                    }
                                }else{
                                    $listaErrores[] = 'El campo Moneda Sindicato es obligatorio si el trabajador es parte del Sindicato.';
                                    $isError = true;
                                } 
                                if(isset($dato['montoSindicato'])){
                                    if(!is_numeric($dato['montoSindicato'])){
                                        $listaErrores[] = 'El formato del Monto Sindicato "' . $dato['montoSindicato'] . '" es incorrecto, recuerda que este campo acepta sólo valores numéricos.';
                                        $isError = true;
                                    }
                                }else{
                                    $listaErrores[] = 'El campo Monto Sindicato es obligatorio si el trabajador es parte del Sindicato.';
                                    $isError = true;
                                } 
                            }
                        }
                    }else{
                        /*$listaErrores[] = 'El campo Sindicato es obligatorio.';
                        $isError = true;*/
                    }                                
                    if(isset($dato['vacaciones'])){
                        if(!is_numeric($dato['vacaciones'])){
                            $listaErrores[] = 'El formato de Vacaciones "' . $dato['vacaciones'] . '" es incorrecto, recuerda que este campo acepta sólo valores numéricos.';
                            $isError = true;
                        }
                    }else{
                        /*$listaErrores[] = 'El campo Vacaciones es obligatorio.';
                        $isError = true;*/
                    }
                    if(isset($dato['tramo'])){
                        if(strtoupper($dato['tramo'])!='A' && strtoupper($dato['tramo'])!='B' && strtoupper($dato['tramo'])!='C' && strtoupper($dato['tramo'])!='D'){
                            $listaErrores[] = 'El código de Tramo de Asignación Familiar "' . $dato['tramo'] . '" es incorrecto, recuerda que los códigos son "a", "b", "c" o "d".';
                            $isError = true;
                        }
                    }
                    if($isError){
                        if($isRut){
                            $rut = Funciones::formatear_rut($dato['rut']);
                        }else{
                            $rut = $dato['rut'];
                        }
                        $lista[] = array(
                            'fila' => $i,
                            'trabajador' => $dato['rut'] ? $rut : '',
                            'errores' => $listaErrores
                        );
                    }
                    $i++;
                }
            }
        }
        
        return $lista;
    }
    
    public function generarArchivoPreviredExcel()
    {
        $datos = Input::all();
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        $sid = (array) $datos['trabajadores'];
        $trabajadores = Trabajador::whereIn('sid', $sid)->get();
        $listaTrabajadores = array();
        $empresa = Session::get('empresa');
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito >= $mesAnterior && $empleado->fecha_ingreso<=$finMes){
                        $liquidacion = Liquidacion::where('trabajador_id', $trabajador->id)->where('mes', $mes->mes)->first();
                        if($liquidacion){
                            $lineaAdicional = array();
                            $licenciasAdicional = false;
                            $movimientoPersonal = $liquidacion->movimiento_personal;
                            $detallesAfiliadoVoluntario = $liquidacion->detalleAfiliadoVoluntario;
                            $detallesAfp = $liquidacion->miDetalleAfp();
                            $detallesApvc = $liquidacion->miDetalleApvc($lineaAdicional);
                            $detallesApvi = $liquidacion->miDetalleApvi($lineaAdicional);
                            $detallesCaja = $liquidacion->miDetalleCaja();
                            $detallesIpsIslFonasa = $liquidacion->miDetalleIpsIslFonasa();
                            $detallesSalud = $liquidacion->miDetalleSalud();
                            $detallesMutual = $liquidacion->miDetalleMutual();
                            $detallesSeguroCesantia = $liquidacion->miDetalleSeguroCesantia();
                            $detallesPagadorSubsidio = $liquidacion->miDetallePagadorSubsidio();
                            $diasTrabajados = $liquidacion->diasTrabajados();

                            $listaTrabajadores[] = array(
                                /*'id' => $trabajador->id,
                                'sid' => $trabajador->sid,*/

                                //Datos del Trabajador
                                'rutSinDigito' => $trabajador->rut_sin_digito(),
                                'rutDigito' => $trabajador->rut_digito(),
                                'apellidoPaterno' => $empleado->apellidoPaterno(),
                                'apellidoMaterno' => $empleado->apellidoMaterno(),
                                'nombres' => $liquidacion->trabajador_nombres,
                                'sexo' => strtoupper($empleado->sexo),
                                'nacionalidad' => $empleado->codigoNacionalidad(),
                                'tipoPago' => '01',
                                'periodoDesde' => Funciones::obtenerMes($mes->nombre) . $mes->anio, 
                                'periodoHasta' => 0, 
                                'regimenPrevisional' => $liquidacion->regimenPrevisional(), 
                                'tipoTrabajador' => $empleado->tipoTrabajador(), 
                                'diasTrabajados' => $diasTrabajados, 
                                'tipoLinea' => '00', 
                                'movimientoPersonal' => $liquidacion->movimiento_personal, 
                                'movimientoPersonalDesde' => $liquidacion->movimientoPersonal()['desde'],
                                'movimientoPersonalHasta' => $liquidacion->movimientoPersonal()['hasta'], 
                                'tramo' => $empleado->tramo_id ? strtoupper($empleado->tramo_id) : 'D', 
                                'cargasSimples' => $liquidacion->cantidad_cargas_simples, 
                                'cargasMaternales' => $liquidacion->cantidad_cargas_maternales, 
                                'cargasInvalidas' => $liquidacion->cantidad_cargas_invalidas,
                                'asignacionFamiliar' => $liquidacion->total_cargas,
                                'asignacionFamiliarRetroactiva' => $liquidacion->asignacion_retroactiva,
                                'reintegroCargasFamiliares' => $liquidacion->reintegro_cargas,
                                'solicitudTrabajadorJoven' => $trabajador->solicitudTrabajadorJoven(),

                                //Datos de la AFP
                                'codigoAfp' => $detallesAfp['codigoAfp'] ? $detallesAfp['codigoAfp'] : $detallesSeguroCesantia['codigo'],
                                'nombreAfp' => $detallesAfp['nombreAfp'],
                                'rentaImponible' => $detallesAfp['rentaImponible'],
                                'cotizacionAfp' => $detallesAfp['cotizacionAfp'],
                                'sis' => $detallesAfp['sis'],
                                'cuentaAhorroVoluntario' => $detallesAfp['cuentaAhorroVoluntario'],
                                'rentaSustitutiva' => $detallesAfp['rentaSustitutiva'],
                                'tasaSustitutiva' => $detallesAfp['tasaSustitutiva'],
                                'aporteSustitutiva' => $detallesAfp['aporteSustitutiva'],
                                'numeroPeriodos' => $detallesAfp['numeroPeriodos'],
                                'periodoDesdeSustit' => $detallesAfp['periodoDesdeSustit'],
                                'periodoHastaSustit' => $detallesAfp['periodoHastaSustit'],
                                'puestoTrabajoPesado' => $detallesAfp['puestoTrabajoPesado'],
                                'porcentajeTrabajoPesado' => $detallesAfp['porcentajeTrabajoPesado'],
                                'cotizacionTrabajoPesado' => $detallesAfp['cotizacionTrabajoPesado'],

                                //Datos Ahorro Previsional Voluntario Individual    
                                'codigoAPVI' => $detallesApvi['codigoAPVI'],
                                'nombreAPVI' => $detallesApvi['nombreAPVI'],
                                'numeroContratoAPVI' => $detallesApvi['numeroContratoAPVI'],
                                'formaPagoAPVI' => $detallesApvi['formaPagoAPVI'],
                                'cotizacionAPVI' => $detallesApvi['cotizacionAPVI'],
                                'cotizacionDepositosConvenidos' => $detallesApvi['cotizacionDepositosConvenidosAPVI'],

                                //Datos Ahorro Previsional Voluntario Colectivo
                                'codigoAPVC' => $detallesApvc['codigoAPVC'],
                                'nombreAPVC' => $detallesApvc['nombreAPVC'],
                                'numeroContratoAPVC' => $detallesApvc['numeroContratoAPVC'],
                                'formaPagoAPVC' => $detallesApvc['formaPagoAPVC'],
                                'cotizacionTrabajadorAPVC' => $detallesApvc['cotizacionTrabajadorAPVC'],
                                'cotizacionEmpleadorAPVC' => $detallesApvc['cotizacionEmpleadorAPVC'],


                                //Datos Afiliado Voluntario
                                'rutAfiliadoVoluntario' => '',
                                'dvAfiliadoVoluntario' => '',
                                'apellidoPaternoAfiliadoVoluntario' => '',
                                'apellidoMaternoAfiliadoVoluntario' => '',
                                'nombresAfiliadoVoluntario' => '',
                                'codigoMovimientoPersonalAfiliadoVoluntario' => '0',
                                'fechaDesdeAfiliadoVoluntario' => '',
                                'fechaHastaAfiliadoVoluntario' => '',
                                'codigoAfpAfiliadoVoluntario' => '',
                                'montoCapitalizacionVoluntaria' => 0,
                                'montoAhorroVoluntario' => 0,
                                'numeroPeriodosCotizacion' => 0,

                                //Datos IPS-ISL-FONASA
                                'codigoExCaja' => $detallesIpsIslFonasa['codigoExCaja'],
                                'tasaCotizacionExCaja' => $detallesIpsIslFonasa['tasa'],
                                'rentaImponibleIps' => $detallesIpsIslFonasa['rentaImponible'],
                                'cotizacionObligatoriaIps' => $detallesIpsIslFonasa['cotizacionObligatoria'],
                                'rentaImponibleDesahucio' => $detallesIpsIslFonasa['rentaImponibleDesahucio'],
                                'codigoExCajaDesahucio' => $detallesIpsIslFonasa['codigoExCajaDesahucio'],
                                'tasaDesahucio' => $detallesIpsIslFonasa['tasaDesahucio'],
                                'cotizacionDesahucio' => $detallesIpsIslFonasa['cotizacionDesahucio'],
                                'cotizacionFonasa' => $detallesIpsIslFonasa['cotizacionFonasa'],
                                'cotizacionIsl' => $detallesIpsIslFonasa['cotizacionIsl'],
                                'bonificacion15386' => $detallesIpsIslFonasa['bonificacion'],
                                'descuentoCargasIsl' => $detallesIpsIslFonasa['descuentoCargasIsl'],
                                'bonosGobierno' => $detallesIpsIslFonasa['bonosGobierno'],                            

                                //Datos Salud
                                'codigoInstitucionSalud' => $detallesSalud['codigoSalud'],
                                'nombreSalud' => $detallesSalud['nombreSalud'],
                                'numeroFun' => '',
                                'rentaImponibleIsapre' => $detallesSalud['rentaImponible'],
                                'monedaPlanIsapre' => $detallesSalud['moneda'],
                                'cotizacionPactada' => $detallesSalud['cotizacionPactada'],
                                'cotizacionObligatoria' => $detallesSalud['cotizacionObligatoria'],
                                'cotizacionAdicional' => $detallesSalud['cotizacionAdicional'],
                                'montoGarantiaExplicita' => $detallesSalud['ges'],

                                //Datos Caja de Compensación
                                'codigoCcaf' => $detallesCaja['codigoCaja'],
                                'rentaImponibleCcaf' => $detallesCaja['rentaImponible'],
                                'creditosPersonalesCcaf' => $detallesCaja['creditosPersonales'],
                                'descuentoDentalCcaf' => $detallesCaja['descuentoDental'],
                                'descuentosLeasing' => $detallesCaja['descuentosLeasing'],
                                'descuentosSeguroCcaf' => $detallesCaja['descuentosSeguro'],
                                'otrosDescuentosCcaf' => $detallesCaja['otrosDescuentos'],
                                'cotizacionCcafNoAfiliadosIsapre' => $detallesCaja['cotizacion'],
                                'descuentoCargasFamiliaresCcaf' => $detallesCaja['descuentoCargas'],
                                'otrosDescuentosCcaf1' => $detallesCaja['otrosDescuentos1'],
                                'otrosDescuentosCcaf2' => $detallesCaja['otrosDescuentos2'],
                                'bonosGobiernoSalud' => $detallesCaja['bonosGobierno'],
                                'codigoSucursalSalud' => $detallesCaja['codigoSucursal'],

                                //Datos Mutualidad
                                'codigoMutualidad' => $detallesMutual['codigoMutual'],
                                'rentaImponibleMutual' => $detallesMutual['rentaImponible'],
                                'cotizacionAccidenteTrabajo' => $detallesMutual['cotizacionAccidentes'],
                                'sucursalPagoMutual' => $detallesMutual['codigoSucursal'],

                                //Datos Administradora de Seguro de Cesantía
                                'rentaImponibleSeguroCesantia' => $detallesSeguroCesantia['rentaImponible'],
                                'aporteTrabajadorSeguroCesantia' => $detallesSeguroCesantia['aporteTrabajador'],
                                'aporteEmpleadorSeguroCesantia' => $detallesSeguroCesantia['aporteEmpleador'],

                                //Datos Pagador de Subsidios
                                'rutPagadoraSubsidio' => $detallesPagadorSubsidio['rut'],
                                'dvPagadoraSubsidio' => $detallesPagadorSubsidio['digito'],

                                //Otros Datos de la Empresa
                                'centroCosto' => $liquidacion->centroCosto ? $liquidacion->centroCosto->codigo : ''              

                                //'liquidacion' => $liquidacion                           
                            );
                            //$lineaAdicional                        
                            if($liquidacion->movimiento_personal==3){
                                $licenciasAdicional = $trabajador->licenciasAdicional();
                            }

                            if($licenciasAdicional){
                                foreach($licenciasAdicional as $licencia){
                                    $listaTrabajadores[] = array(

                                        //Datos del Trabajador
                                        'rutSinDigito' => $trabajador->rut_sin_digito(),
                                        'rutDigito' => $trabajador->rut_digito(),
                                        'apellidoPaterno' => $empleado->apellidoPaterno(),
                                        'apellidoMaterno' => $empleado->apellidoMaterno(),
                                        'nombres' => $liquidacion->trabajador_nombres,
                                        'sexo' => strtoupper($empleado->sexo),
                                        'nacionalidad' => $empleado->codigoNacionalidad(),
                                        'tipoPago' => '01',
                                        'periodoDesde' => Funciones::obtenerMes($mes->nombre) . $mes->anio, 
                                        'periodoHasta' => 0, 
                                        'regimenPrevisional' => $liquidacion->regimenPrevisional(), 
                                        'tipoTrabajador' => $empleado->tipoTrabajador(), 
                                        'diasTrabajados' => $diasTrabajados,
                                        'tipoLinea' => '01', 
                                        'movimientoPersonal' => $liquidacion->movimiento_personal, 
                                        'movimientoPersonalDesde' => $licencia['desde'],
                                        'movimientoPersonalHasta' => $licencia['hasta'], 
                                        'tramo' => $empleado->tramo_id ? strtoupper($empleado->tramo_id) : 'D', 
                                        'cargasSimples' => $liquidacion->cantidad_cargas_simples, 
                                        'cargasMaternales' => $liquidacion->cantidad_cargas_maternales, 
                                        'cargasInvalidas' => $liquidacion->cantidad_cargas_invalidas,
                                        'asignacionFamiliar' => $liquidacion->total_cargas,
                                        'asignacionFamiliarRetroactiva' => $liquidacion->asignacion_retroactiva,
                                        'reintegroCargasFamiliares' => $liquidacion->reintegro_cargas,
                                        'solicitudTrabajadorJoven' => $trabajador->solicitudTrabajadorJoven(),

                                        //Datos de la AFP
                                        'codigoAfp' => $detallesAfp['codigoAfp'] ? $detallesAfp['codigoAfp'] : $detallesSeguroCesantia['codigo'],
                                        'nombreAfp' => $detallesAfp['nombreAfp'],
                                        'rentaImponible' => $detallesAfp['rentaImponible'],
                                        'cotizacionAfp' => $detallesAfp['cotizacionAfp'],
                                        'sis' => $detallesAfp['sis'],
                                        'cuentaAhorroVoluntario' => $detallesAfp['cuentaAhorroVoluntario'],
                                        'rentaSustitutiva' => $detallesAfp['rentaSustitutiva'],
                                        'tasaSustitutiva' => $detallesAfp['tasaSustitutiva'],
                                        'aporteSustitutiva' => $detallesAfp['aporteSustitutiva'],
                                        'numeroPeriodos' => $detallesAfp['numeroPeriodos'],
                                        'periodoDesdeSustit' => $detallesAfp['periodoDesdeSustit'],
                                        'periodoHastaSustit' => $detallesAfp['periodoHastaSustit'],
                                        'puestoTrabajoPesado' => $detallesAfp['puestoTrabajoPesado'],
                                        'porcentajeTrabajoPesado' => $detallesAfp['porcentajeTrabajoPesado'],
                                        'cotizacionTrabajoPesado' => $detallesAfp['cotizacionTrabajoPesado'],

                                        //Datos Ahorro Previsional Voluntario Individual    
                                        'codigoAPVI' => $detallesApvi['codigoAPVI'],
                                        'nombreAPVI' => $detallesApvi['nombreAPVI'],
                                        'numeroContratoAPVI' => $detallesApvi['numeroContratoAPVI'],
                                        'formaPagoAPVI' => $detallesApvi['formaPagoAPVI'],
                                        'cotizacionAPVI' => $detallesApvi['cotizacionAPVI'],
                                        'cotizacionDepositosConvenidos' => $detallesApvi['cotizacionDepositosConvenidosAPVI'],

                                        //Datos Ahorro Previsional Voluntario Colectivo
                                        'codigoAPVC' => $detallesApvc['codigoAPVC'],
                                        'nombreAPVC' => $detallesApvc['nombreAPVC'],
                                        'numeroContratoAPVC' => $detallesApvc['numeroContratoAPVC'],
                                        'formaPagoAPVC' => $detallesApvc['formaPagoAPVC'],
                                        'cotizacionTrabajadorAPVC' => $detallesApvc['cotizacionTrabajadorAPVC'],
                                        'cotizacionEmpleadorAPVC' => $detallesApvc['cotizacionEmpleadorAPVC'],


                                        //Datos Afiliado Voluntario
                                        'rutAfiliadoVoluntario' => '',
                                        'dvAfiliadoVoluntario' => '',
                                        'apellidoPaternoAfiliadoVoluntario' => '',
                                        'apellidoMaternoAfiliadoVoluntario' => '',
                                        'nombresAfiliadoVoluntario' => '',
                                        'codigoMovimientoPersonalAfiliadoVoluntario' => '0',
                                        'fechaDesdeAfiliadoVoluntario' => '',
                                        'fechaHastaAfiliadoVoluntario' => '',
                                        'codigoAfpAfiliadoVoluntario' => '',
                                        'montoCapitalizacionVoluntaria' => 0,
                                        'montoAhorroVoluntario' => 0,
                                        'numeroPeriodosCotizacion' => 0,

                                        //Datos IPS-ISL-FONASA
                                        'codigoExCaja' => $detallesIpsIslFonasa['codigoExCaja'],
                                        'tasaCotizacionExCaja' => $detallesIpsIslFonasa['tasa'],
                                        'rentaImponibleIps' => $detallesIpsIslFonasa['rentaImponible'],
                                        'cotizacionObligatoriaIps' => $detallesIpsIslFonasa['cotizacionObligatoria'],
                                        'rentaImponibleDesahucio' => $detallesIpsIslFonasa['rentaImponibleDesahucio'],
                                        'codigoExCajaDesahucio' => $detallesIpsIslFonasa['codigoExCajaDesahucio'],
                                        'tasaDesahucio' => $detallesIpsIslFonasa['tasaDesahucio'],
                                        'cotizacionDesahucio' => $detallesIpsIslFonasa['cotizacionDesahucio'],
                                        'cotizacionFonasa' => $detallesIpsIslFonasa['cotizacionFonasa'],
                                        'cotizacionIsl' => $detallesIpsIslFonasa['cotizacionIsl'],
                                        'bonificacion15386' => $detallesIpsIslFonasa['bonificacion'],
                                        'descuentoCargasIsl' => $detallesIpsIslFonasa['descuentoCargasIsl'],
                                        'bonosGobierno' => $detallesIpsIslFonasa['bonosGobierno'],                            

                                        //Datos Salud
                                        'codigoInstitucionSalud' => $detallesSalud['codigoSalud'],
                                        'nombreSalud' => $detallesSalud['nombreSalud'],
                                        'numeroFun' => '',
                                        'rentaImponibleIsapre' => $detallesSalud['rentaImponible'],
                                        'monedaPlanIsapre' => $detallesSalud['moneda'],
                                        'cotizacionPactada' => $detallesSalud['cotizacionPactada'],
                                        'cotizacionObligatoria' => $detallesSalud['cotizacionObligatoria'],
                                        'cotizacionAdicional' => $detallesSalud['cotizacionAdicional'],
                                        'montoGarantiaExplicita' => $detallesSalud['ges'],

                                        //Datos Caja de Compensación
                                        'codigoCcaf' => $detallesCaja['codigoCaja'],
                                        'rentaImponibleCcaf' => $detallesCaja['rentaImponible'],
                                        'creditosPersonalesCcaf' => $detallesCaja['creditosPersonales'],
                                        'descuentoDentalCcaf' => $detallesCaja['descuentoDental'],
                                        'descuentosLeasing' => $detallesCaja['descuentosLeasing'],
                                        'descuentosSeguroCcaf' => $detallesCaja['descuentosSeguro'],
                                        'otrosDescuentosCcaf' => $detallesCaja['otrosDescuentos'],
                                        'cotizacionCcafNoAfiliadosIsapre' => $detallesCaja['cotizacion'],
                                        'descuentoCargasFamiliaresCcaf' => $detallesCaja['descuentoCargas'],
                                        'otrosDescuentosCcaf1' => $detallesCaja['otrosDescuentos1'],
                                        'otrosDescuentosCcaf2' => $detallesCaja['otrosDescuentos2'],
                                        'bonosGobiernoSalud' => $detallesCaja['bonosGobierno'],
                                        'codigoSucursalSalud' => $detallesCaja['codigoSucursal'],

                                        //Datos Mutualidad
                                        'codigoMutualidad' => $detallesMutual['codigoMutual'],
                                        'rentaImponibleMutual' => $detallesMutual['rentaImponible'],
                                        'cotizacionAccidenteTrabajo' => $detallesMutual['cotizacionAccidentes'],
                                        'sucursalPagoMutual' => $detallesMutual['codigoSucursal'],

                                        //Datos Administradora de Seguro de Cesantía
                                        'rentaImponibleSeguroCesantia' => '',
                                        'aporteTrabajadorSeguroCesantia' => '',
                                        'aporteEmpleadorSeguroCesantia' => '',

                                        //Datos Pagador de Subsidios
                                        'rutPagadoraSubsidio' => $detallesPagadorSubsidio['rut'],
                                        'dvPagadoraSubsidio' => $detallesPagadorSubsidio['digito'],

                                        //Otros Datos de la Empresa
                                        'centroCosto' => $liquidacion->centroCosto ? $liquidacion->centroCosto->codigo : ''                 
                                    );
                                }
                            }
                            if(count($lineaAdicional)){
                                foreach($lineaAdicional as $linea){
                                    $listaTrabajadores[] = array(

                                        //Datos del Trabajador
                                        'rutSinDigito' => $trabajador->rut_sin_digito(),
                                        'rutDigito' => $trabajador->rut_digito(),
                                        'apellidoPaterno' => $empleado->apellidoPaterno(),
                                        'apellidoMaterno' => $empleado->apellidoMaterno(),
                                        'nombres' => $liquidacion->trabajador_nombres,
                                        'sexo' => strtoupper($empleado->sexo),
                                        'nacionalidad' => $empleado->codigoNacionalidad(),
                                        'tipoPago' => '01',
                                        'periodoDesde' => Funciones::obtenerMes($mes->nombre) . $mes->anio, 
                                        'periodoHasta' => 0, 
                                        'regimenPrevisional' => $liquidacion->regimenPrevisional(), 
                                        'tipoTrabajador' => $empleado->tipoTrabajador(), 
                                        'diasTrabajados' => $diasTrabajados,
                                        'tipoLinea' => '01', 
                                        'movimientoPersonal' => $liquidacion->movimiento_personal, 
                                        'movimientoPersonalDesde' => '',
                                        'movimientoPersonalHasta' => '', 
                                        'tramo' => $empleado->tramo_id ? strtoupper($empleado->tramo_id) : 'D', 
                                        'cargasSimples' => $liquidacion->cantidad_cargas_simples, 
                                        'cargasMaternales' => $liquidacion->cantidad_cargas_maternales, 
                                        'cargasInvalidas' => $liquidacion->cantidad_cargas_invalidas,
                                        'asignacionFamiliar' => $liquidacion->total_cargas,
                                        'asignacionFamiliarRetroactiva' => $liquidacion->asignacion_retroactiva,
                                        'reintegroCargasFamiliares' => $liquidacion->reintegro_cargas,
                                        'solicitudTrabajadorJoven' => $trabajador->solicitudTrabajadorJoven(),

                                        //Datos de la AFP
                                        'codigoAfp' => $detallesAfp['codigoAfp'] ? $detallesAfp['codigoAfp'] : $detallesSeguroCesantia['codigo'],
                                        'nombreAfp' => $detallesAfp['nombreAfp'],
                                        'rentaImponible' => $detallesAfp['rentaImponible'],
                                        'cotizacionAfp' => $detallesAfp['cotizacionAfp'],
                                        'sis' => $detallesAfp['sis'],
                                        'cuentaAhorroVoluntario' => $detallesAfp['cuentaAhorroVoluntario'],
                                        'rentaSustitutiva' => $detallesAfp['rentaSustitutiva'],
                                        'tasaSustitutiva' => $detallesAfp['tasaSustitutiva'],
                                        'aporteSustitutiva' => $detallesAfp['aporteSustitutiva'],
                                        'numeroPeriodos' => $detallesAfp['numeroPeriodos'],
                                        'periodoDesdeSustit' => $detallesAfp['periodoDesdeSustit'],
                                        'periodoHastaSustit' => $detallesAfp['periodoHastaSustit'],
                                        'puestoTrabajoPesado' => $detallesAfp['puestoTrabajoPesado'],
                                        'porcentajeTrabajoPesado' => $detallesAfp['porcentajeTrabajoPesado'],
                                        'cotizacionTrabajoPesado' => $detallesAfp['cotizacionTrabajoPesado'],

                                        //Datos Ahorro Previsional Voluntario Individual    
                                        'codigoAPVI' => $linea['codigoAPVI'],
                                        'nombreAPVI' => $linea['nombreAPVI'],
                                        'numeroContratoAPVI' => $linea['numeroContratoAPVI'],
                                        'formaPagoAPVI' => $linea['formaPagoAPVI'],
                                        'cotizacionAPVI' => $linea['cotizacionAPVI'],
                                        'cotizacionDepositosConvenidos' => $linea['cotizacionDepositosConvenidosAPVI'],

                                        //Datos Ahorro Previsional Voluntario Colectivo
                                        'codigoAPVC' => $linea['codigoAPVC'],
                                        'nombreAPVC' => $linea['nombreAPVC'],
                                        'numeroContratoAPVC' => $linea['numeroContratoAPVC'],
                                        'formaPagoAPVC' => $linea['formaPagoAPVC'],
                                        'cotizacionTrabajadorAPVC' => $linea['cotizacionTrabajadorAPVC'],
                                        'cotizacionEmpleadorAPVC' => $linea['cotizacionEmpleadorAPVC'],


                                        //Datos Afiliado Voluntario
                                        'rutAfiliadoVoluntario' => '',
                                        'dvAfiliadoVoluntario' => '',
                                        'apellidoPaternoAfiliadoVoluntario' => '',
                                        'apellidoMaternoAfiliadoVoluntario' => '',
                                        'nombresAfiliadoVoluntario' => '',
                                        'codigoMovimientoPersonalAfiliadoVoluntario' => '0',
                                        'fechaDesdeAfiliadoVoluntario' => '',
                                        'fechaHastaAfiliadoVoluntario' => '',
                                        'codigoAfpAfiliadoVoluntario' => '',
                                        'montoCapitalizacionVoluntaria' => 0,
                                        'montoAhorroVoluntario' => 0,
                                        'numeroPeriodosCotizacion' => 0,

                                        //Datos IPS-ISL-FONASA
                                        'codigoExCaja' => $detallesIpsIslFonasa['codigoExCaja'],
                                        'tasaCotizacionExCaja' => $detallesIpsIslFonasa['tasa'],
                                        'rentaImponibleIps' => $detallesIpsIslFonasa['rentaImponible'],
                                        'cotizacionObligatoriaIps' => $detallesIpsIslFonasa['cotizacionObligatoria'],
                                        'rentaImponibleDesahucio' => $detallesIpsIslFonasa['rentaImponibleDesahucio'],
                                        'codigoExCajaDesahucio' => $detallesIpsIslFonasa['codigoExCajaDesahucio'],
                                        'tasaDesahucio' => $detallesIpsIslFonasa['tasaDesahucio'],
                                        'cotizacionDesahucio' => $detallesIpsIslFonasa['cotizacionDesahucio'],
                                        'cotizacionFonasa' => $detallesIpsIslFonasa['cotizacionFonasa'],
                                        'cotizacionIsl' => $detallesIpsIslFonasa['cotizacionIsl'],
                                        'bonificacion15386' => $detallesIpsIslFonasa['bonificacion'],
                                        'descuentoCargasIsl' => $detallesIpsIslFonasa['descuentoCargasIsl'],
                                        'bonosGobierno' => $detallesIpsIslFonasa['bonosGobierno'],                            

                                        //Datos Salud
                                        'codigoInstitucionSalud' => $detallesSalud['codigoSalud'],
                                        'nombreSalud' => $detallesSalud['nombreSalud'],
                                        'numeroFun' => '',
                                        'rentaImponibleIsapre' => $detallesSalud['rentaImponible'],
                                        'monedaPlanIsapre' => $detallesSalud['moneda'],
                                        'cotizacionPactada' => $detallesSalud['cotizacionPactada'],
                                        'cotizacionObligatoria' => $detallesSalud['cotizacionObligatoria'],
                                        'cotizacionAdicional' => $detallesSalud['cotizacionAdicional'],
                                        'montoGarantiaExplicita' => $detallesSalud['ges'],

                                        //Datos Caja de Compensación
                                        'codigoCcaf' => $detallesCaja['codigoCaja'],
                                        'rentaImponibleCcaf' => $detallesCaja['rentaImponible'],
                                        'creditosPersonalesCcaf' => $detallesCaja['creditosPersonales'],
                                        'descuentoDentalCcaf' => $detallesCaja['descuentoDental'],
                                        'descuentosLeasing' => $detallesCaja['descuentosLeasing'],
                                        'descuentosSeguroCcaf' => $detallesCaja['descuentosSeguro'],
                                        'otrosDescuentosCcaf' => $detallesCaja['otrosDescuentos'],
                                        'cotizacionCcafNoAfiliadosIsapre' => $detallesCaja['cotizacion'],
                                        'descuentoCargasFamiliaresCcaf' => $detallesCaja['descuentoCargas'],
                                        'otrosDescuentosCcaf1' => $detallesCaja['otrosDescuentos1'],
                                        'otrosDescuentosCcaf2' => $detallesCaja['otrosDescuentos2'],
                                        'bonosGobiernoSalud' => $detallesCaja['bonosGobierno'],
                                        'codigoSucursalSalud' => $detallesCaja['codigoSucursal'],

                                        //Datos Mutualidad
                                        'codigoMutualidad' => $detallesMutual['codigoMutual'],
                                        'rentaImponibleMutual' => $detallesMutual['rentaImponible'],
                                        'cotizacionAccidenteTrabajo' => $detallesMutual['cotizacionAccidentes'],
                                        'sucursalPagoMutual' => $detallesMutual['codigoSucursal'],

                                        //Datos Administradora de Seguro de Cesantía
                                        'rentaImponibleSeguroCesantia' => '',
                                        'aporteTrabajadorSeguroCesantia' => '',
                                        'aporteEmpleadorSeguroCesantia' => '',

                                        //Datos Pagador de Subsidios
                                        'rutPagadoraSubsidio' => $detallesPagadorSubsidio['rut'],
                                        'dvPagadoraSubsidio' => $detallesPagadorSubsidio['digito'],

                                        //Otros Datos de la Empresa
                                        'centroCosto' => $liquidacion->centroCosto ? $liquidacion->centroCosto->codigo : ''                 
                                    );
                                }
                            }
                            
                        }
                    }
                }
            }
        }
        
        $filename = "ArchivoPrevired.xls";
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidoPaterno');
        
        $destination = public_path('stories/' . $filename);
        
        $fp = fopen($destination, "w+");
        if($fp){
            $afps = array();
            $isapres = array();
            $caja = array(
                'nombre' => $empresa->caja->glosa,
                'total' => 0,
                'totalCotizacion' => 0,
                'totalCreditos' => 0,
                'totalDescuentoDental' => 0,
                'totalLeasing' => 0,
                'totalSeguro' => 0,
                'totalOtros' => 0,
                'trabajadores' => array()
            );
            $mutual = array(
                'nombre' => $empresa->mutual->glosa,
                'total' => 0,
                'trabajadores' => array()
            );
            $ipsFonasa = array(
                'fonasa' => array(
                    'total' => 0,
                    'trabajadores' => array()
                ), 
                'isl' => array(
                    'total' => 0,
                    'trabajadores' => array()
                )
            );
            $ruts = array();
            $totalAfp = 0;
            $totalIsapre = 0;
            $totalIpsFonasa = 0;
            $totalCaja = 0;
            $totalMutual = 0;
            $apvs = array();
            $a = 0;
            if(count($listaTrabajadores)){
                foreach($listaTrabajadores as $index => $trab){
                    //fputcsv($fp, $trab, ";");
                    
                    $afp = $trab['nombreAfp'];
                    $isapre = $trab['nombreSalud'];
                    $apvs[] = $trab['codigoAPVI'];
                    $apvcs[] = $trab['codigoAPVC'];
                    $apvi = $trab['nombreAPVI'];
                    $apvc = $trab['nombreAPVC'];
                    $montoAhorroVoluntario = $trab['cuentaAhorroVoluntario'];
                    $totalApvc = ($trab['cotizacionTrabajadorAPVC'] + $trab['cotizacionEmpleadorAPVC']);
                    unset($trab['nombreAfp']);
                    unset($trab['nombreSalud']);
                    unset($trab['nombreAPVI']);
                    unset($trab['nombreAPVC']);
                    if($trab['cotizacionAPVI']){                        
                        $a = (int) $trab['codigoAPVI'];
                        $totalAfp += $trab['cotizacionAPVI'];
                        if(isset($afps[$a])){
                            $afps[$a]['total'] = ($afps[$a]['total'] + $trab['cotizacionAPVI']);        
                            $afps[$a]['apvi']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $trab['cotizacionAPVI']
                            );
                            if(isset($afps[$a]['apvi']['total'])){
                                $afps[$a]['apvi']['total'] = ($afps[$a]['apvi']['total'] + $trab['cotizacionAPVI']);        
                            }else{
                                $afps[$a]['apvi']['total'] = $trab['cotizacionAPVI'];   
                            }
                        }else{
                            $afps[$a] = array(
                                'codigo' => $a,
                                'nombre' => $apvi,
                                'total' => $trab['cotizacionAPVI'],
                                'apvi' => array(
                                    'trabajadores' => array(),
                                    'total' => $trab['cotizacionAPVI']
                                )
                            );
                            $afps[$a]['apvi']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $trab['cotizacionAPVI']
                            );
                        }
                    }                    
                    if($totalApvc){                        
                        $a = (int) $trab['codigoAPVC'];                        
                        $totalAfp += $totalApvc;
                        if(isset($afps[$a])){
                            $afps[$a]['total'] = ($afps[$a]['total'] + $totalApvc);
                            $afps[$a]['apvc']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $totalApvc
                            );
                            if(isset($afps[$a]['apvc']['total'])){
                                $afps[$a]['apvc']['total'] = ($afps[$a]['apvc']['total'] + $totalApvc);        
                            }else{
                                $afps[$a]['apvc']['total'] = $totalApvc;   
                            }
                        }else{
                            $afps[$a] = array(
                                'codigo' => $a,
                                'nombre' => 'APVC ' . $apvc,
                                'total' => $totalApvc,
                                'apvc' => array(
                                    'trabajadores' => array(),
                                    'total' => $totalApvc
                                )
                            );
                            $afps[$a]['apvc']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $totalApvc
                            );
                        }
                    }
                    if($montoAhorroVoluntario){                        
                        $a = (int) $trab['codigoAfp'];                    
                        $totalAfp += $montoAhorroVoluntario;
                        if(isset($afps[$a])){
                            $afps[$a]['total'] = ($afps[$a]['total'] + $montoAhorroVoluntario);
                            $afps[$a]['ahorro']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $montoAhorroVoluntario
                            );
                            if(isset($afps[$a]['ahorro']['total'])){
                                $afps[$a]['ahorro']['total'] = ($afps[$a]['ahorro']['total'] + $montoAhorroVoluntario);        
                            }else{
                                $afps[$a]['ahorro']['total'] = $montoAhorroVoluntario;   
                            }
                        }else{
                            $afps[$a] = array(
                                'codigo' => $a,
                                'nombre' => 'Ahorro Voluntario ' . $afp,
                                'total' => $montoAhorroVoluntario,
                                'ahorro' => array(
                                    'trabajadores' => array(),
                                    'total' => $montoAhorroVoluntario
                                )
                            );
                            $afps[$a]['ahorro']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $montoAhorroVoluntario
                            );
                        }
                    }
                    fputs($fp, utf8_decode(implode(";", $trab))."\r\n", 2048);
                    
                    if(!isset($ruts[$trab['rutSinDigito']])){
                        $ruts[$trab['rutSinDigito']] = $trab['rutSinDigito'];
                        $montoAfp = ($trab['cotizacionAfp'] + $trab['sis'] + $trab['aporteTrabajadorSeguroCesantia'] + $trab['aporteEmpleadorSeguroCesantia']);                        
                        $montoIsapre = ($trab['cotizacionObligatoria'] + $trab['cotizacionAdicional']);
                        $a = (int) $trab['codigoAfp'];
                        if($montoAfp){
                            if(isset($afps[$a])){
                                $afps[$a]['total'] = ($afps[$a]['total'] + $montoAfp);
                                $afps[$a]['afp']['trabajadores'][] = array(
                                    'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                    'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                    'trabajador' => $trab['cotizacionAfp'],
                                    'rentaImponible' => $trab['rentaImponible'],
                                    'rentaImponibleSeguroCesantia' => $trab['rentaImponibleSeguroCesantia'],
                                    'afcTrabajador' => $trab['aporteTrabajadorSeguroCesantia'],
                                    'empleador' => $trab['sis'],
                                    'afcEmpleador' => $trab['aporteEmpleadorSeguroCesantia']
                                );
                                if(isset($afps[$a]['afp']['totalTrabajador'])){
                                    $afps[$a]['afp']['totalTrabajador'] = ($afps[$a]['afp']['totalTrabajador'] + $trab['cotizacionAfp']);        
                                }else{
                                    $afps[$a]['afp']['totalTrabajador'] = $trab['cotizacionAfp'];   
                                }
                                if(isset($afps[$a]['afp']['totalEmpleador'])){
                                    $afps[$a]['afp']['totalEmpleador'] = ($afps[$a]['afp']['totalEmpleador'] + $trab['sis']);        
                                }else{
                                    $afps[$a]['afp']['totalEmpleador'] = $trab['sis'];   
                                }
                                if(isset($afps[$a]['afp']['totalAfcTrabajador'])){
                                    $afps[$a]['afp']['totalAfcTrabajador'] = ($afps[$a]['afp']['totalAfcTrabajador'] + $trab['aporteTrabajadorSeguroCesantia']);        
                                }else{
                                    $afps[$a]['afp']['totalAfcTrabajador'] = $trab['aporteTrabajadorSeguroCesantia'];   
                                }
                                if(isset($afps[$a]['afp']['totalAfcEmpleador'])){
                                    $afps[$a]['afp']['totalAfcEmpleador'] = ($afps[$a]['afp']['totalAfcEmpleador'] + $trab['aporteEmpleadorSeguroCesantia']);        
                                }else{
                                    $afps[$a]['afp']['totalAfcEmpleador'] = $trab['aporteEmpleadorSeguroCesantia'];   
                                }
                                if(isset($afps[$a]['afp']['total'])){
                                    $afps[$a]['afp']['total'] = ($afps[$a]['afp']['total'] + $montoAfp);        
                                }else{
                                    $afps[$a]['afp']['total'] = $montoAfp;   
                                }
                            }else{
                                $afps[$a] = array(
                                    'codigo' => $a,
                                    'nombre' => $afp,
                                    'total' => $montoAfp,
                                    'afp' => array(
                                        'trabajadores' => array(),
                                        'totalTrabajador' => $trab['cotizacionAfp'],
                                        'totalEmpleador' => $trab['sis'],
                                        'rentaImponible' => $trab['rentaImponible'],
                                        'rentaImponibleSeguroCesantia' => $trab['rentaImponibleSeguroCesantia'],
                                        'totalAfcTrabajador' => $trab['aporteTrabajadorSeguroCesantia'],
                                        'totalAfcEmpleador' => $trab['aporteEmpleadorSeguroCesantia'],
                                        'total' => $montoAfp
                                    )
                                );
                                $afps[$a]['afp']['trabajadores'][] = array(
                                    'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                    'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                    'trabajador' => $trab['cotizacionAfp'],
                                    'afcTrabajador' => $trab['aporteTrabajadorSeguroCesantia'],
                                    'rentaImponible' => $trab['rentaImponible'],
                                    'rentaImponibleSeguroCesantia' => $trab['rentaImponibleSeguroCesantia'],
                                    'empleador' => $trab['sis'],
                                    'afcEmpleador' => $trab['aporteEmpleadorSeguroCesantia']
                                );
                            }
                        }
                        if($montoIsapre){
                            if(isset($isapres[$trab['codigoInstitucionSalud']])){
                                $isapres[$trab['codigoInstitucionSalud']]['total'] = ($isapres[$trab['codigoInstitucionSalud']]['total'] + $montoIsapre);
                                $isapres[$trab['codigoInstitucionSalud']]['trabajadores'][] = array(
                                    'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                    'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                    'total' => $montoIsapre
                                );
                            }else{
                                $isapres[$trab['codigoInstitucionSalud']] = array(
                                    'codigo' => $trab['codigoInstitucionSalud'],
                                    'nombre' => $isapre,
                                    'total' => $montoIsapre,
                                    'trabajadores' => array()
                                );
                                $isapres[$trab['codigoInstitucionSalud']]['trabajadores'][] = array(
                                    'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                    'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                    'total' => $montoIsapre
                                );
                            }
                        }
                        if($trab['cotizacionFonasa']){
                            $ipsFonasa['fonasa']['total'] += $trab['cotizacionFonasa'];
                            $ipsFonasa['fonasa']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $trab['cotizacionFonasa']
                            );
                        }
                        if($trab['cotizacionIsl']){
                            $ipsFonasa['isl']['total'] += ($trab['cotizacionIsl'] - $trab['descuentoCargasIsl']);
                            $ipsFonasa['isl']['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'rentaImponible' => $trab['rentaImponibleCcaf'],
                                'total' => ($trab['cotizacionIsl'] - $trab['descuentoCargasIsl'])
                            );
                        }
                        //if($trab['cotizacionCcafNoAfiliadosIsapre']){
                        $caja['total'] += (($trab['cotizacionCcafNoAfiliadosIsapre'] + $trab['creditosPersonalesCcaf'] + $trab['descuentoDentalCcaf'] + $trab['descuentosLeasing'] + $trab['descuentosSeguroCcaf'] + $trab['otrosDescuentosCcaf']) - $trab['descuentoCargasFamiliaresCcaf']);
                        $caja['totalCotizacion'] += $trab['cotizacionCcafNoAfiliadosIsapre'];
                        $caja['totalCreditos'] += $trab['creditosPersonalesCcaf'];
                        $caja['totalDescuentoDental'] += $trab['descuentoDentalCcaf'];
                        $caja['totalLeasing'] += $trab['descuentosLeasing'];
                        $caja['totalSeguro'] += $trab['descuentosSeguroCcaf'];
                        $caja['totalOtros'] += $trab['otrosDescuentosCcaf'];
                        $caja['trabajadores'][] = array(
                            'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                            'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                            'rentaImponible' => $trab['rentaImponibleCcaf'],
                            'cotizacion' => $trab['cotizacionCcafNoAfiliadosIsapre'],
                            'creditos' => $trab['creditosPersonalesCcaf'],
                            'descuentoDental' => $trab['descuentoDentalCcaf'],
                            'descuentosLeasing' => $trab['descuentosLeasing'],
                            'descuentosSeguro' => $trab['descuentosSeguroCcaf'],
                            'otrosDescuentos' => $trab['otrosDescuentosCcaf'],
                            'total' => (($trab['cotizacionCcafNoAfiliadosIsapre'] + $trab['creditosPersonalesCcaf'] + $trab['descuentoDentalCcaf'] + $trab['descuentosLeasing'] + $trab['descuentosSeguroCcaf'] + $trab['otrosDescuentosCcaf']) - $trab['descuentoCargasFamiliaresCcaf'])
                        );
                        //}
                        
                        if($trab['cotizacionAccidenteTrabajo']){
                            $mutual['total'] += $trab['cotizacionAccidenteTrabajo'];
                            $mutual['trabajadores'][] = array(
                                'rut' => Funciones::formatear_rut($trab['rutSinDigito'] . $trab['rutDigito']),
                                'nombreCompleto' => $trab['nombres'] . ' ' . $trab['apellidoPaterno'] . ' ' . $trab['apellidoMaterno'],
                                'total' => $trab['cotizacionAccidenteTrabajo']
                            );
                        }
                        
                        $totalAfp += $montoAfp;
                        $totalIsapre += $montoIsapre;
                        $totalIpsFonasa += ($trab['cotizacionIsl'] + $trab['cotizacionFonasa'] - $trab['descuentoCargasIsl']);
                        $totalCaja += (($trab['cotizacionCcafNoAfiliadosIsapre'] + $trab['creditosPersonalesCcaf'] + $trab['descuentoDentalCcaf'] + $trab['descuentosLeasing'] + $trab['descuentosSeguroCcaf'] + $trab['otrosDescuentosCcaf']) - $trab['descuentoCargasFamiliaresCcaf']);
                        $totalMutual += $trab['cotizacionAccidenteTrabajo'];
                    }
                        
                }
            }
            fclose($fp);
            $detalles = array(
                'ruts' => array_values($ruts),
                'afps' => array_values($afps),
                'totalAfp' => $totalAfp,
                'isapres' => array_values($isapres),
                'totalIsapre' => $totalIsapre,
                'ipsFonasa' => $ipsFonasa,
                'totalIpsFonasa' => $totalIpsFonasa,
                'caja' => $caja,
                'apvs' => $apvs,
                'apvcs' => $apvcs,
                'totalCaja' => $totalCaja,
                'mutual' => $mutual,
                'totalMutual' => $totalMutual,
                'total' => ($totalAfp + $totalIsapre + $totalIpsFonasa + $totalCaja + $totalMutual),
                'nombreDocumento' => 'archivoPrevired.xls',
                'aliasDocumento' => 'previred.xls',
                'a' => $a
            );
        }
        
        /*
        
        Excel::create("ArchivoPrevired", function($excel) use($listaTrabajadores) {
            $excel->sheet("ArchivoPrevired", function($sheet) use($listaTrabajadores) {
                //$sheet->loadView('excel.previred')->with('listaTrabajadores', $listaTrabajadores)->getStyle('A1')->getAlignment();
                $sheet->fromArray($listaTrabajadores);
            });
        })->store('csv', public_path('stories'), true);
        */
        
        
        /*
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="ArchivoPrevired.csv"'
        ]);   */
        
        $respuesta = array(
            'success' => true,
            'datos' => $listaTrabajadores,
            'detalles' => $detalles
        );
        
        return Response::json($respuesta);
    }
    
    public function descargarPrevired()
    {
        $mes = \Session::get('mesActivo');
        $filename = "Archivo Previred " . $mes->mesActivo . ".csv";
        $destination = public_path('stories/ArchivoPrevired.xls');
        
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    public function descargarProvision()
    {
        $mes = \Session::get('mesActivo');
        $filename = "Provision Vacaciones " . $mes->mesActivo . ".xls";        
        $destination = public_path('stories/Vacaciones.xls');
        
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    public function index()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#trabajadores');
        $mes = \Session::get('mesActivo')->mes;
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;
        /*$trabajadores = FichaTrabajador::orderBy('fecha', 'DESC')->groupby('trabajador_id')->distinct()->with('Trabajador')->where('fecha', '<=', $mes)->get();
        $trabajadores = Trabajador::with(array('FichaTrabajador' => function($query){
            $query->where('estado', '=', 'Ingresado')->where('fecha_reconocimiento', '<=', '2017-02-01')->orderBy('fecha', 'DESC')->first();
        }))->get();*/
        $trabajadores = Trabajador::all();
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->fichaTrabajadorUltima;
                if($empleado){
                    //if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='En Creación' || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito >= $mes){
                        $listaTrabajadores[]=array(    
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rutFormato' => $trabajador->rut_formato(),
                            'apellidos' => $empleado->apellidos ? ucwords(strtolower($empleado->apellidos)) : "",      
                            'nombreCompleto' => $empleado->nombreCompleto(),      
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                            ),             
                            'fechaIngreso' => $empleado->fecha_ingreso,
                            'monedaSueldo' => $empleado->moneda_sueldo,
                            'tipoSueldo' => $empleado->tipo_sueldo,
                            'sueldoBase' => $empleado->sueldo_base,
                            'contratoOrden' => $empleado->tipoContrato ? ucwords(strtolower($empleado->tipoContrato->nombre)) : "", 
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ), 
                            'tipoContrato' => array(
                                'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                                'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
                            ),
                            'estado' => $empleado->estado,
                            'isContrato' => $trabajador->isContrato(),
                            'isFicha' => $trabajador->ficha() ? true : false,
                            'fechaFicha' => $trabajador->fechaFicha($empleado->fecha_ingreso)
                        );
                    //}
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');

        $totalTrabajadores = 0;
        $variable = VariableGlobal::where('variable', "TRABAJADORES")->first();
        if($variable){
            $trabajadoresPermitidos=$variable->valor;
        }else{
            $trabajadoresPermitidos=200;
        }

        $empresas = Empresa::all();
        foreach ($empresas as $empresa) {
            Config::set('database.default', $empresa->base_datos);
            $trabajadoresEmpresa = Trabajador::all();
            $totalTrabajadores+=$trabajadoresEmpresa->count();
        }

        if( $totalTrabajadores >= $trabajadoresPermitidos ){
            $permisos['crear']=false;
        }

        
        $datos = array(
            'trabajadoresPermitidos' => $trabajadoresPermitidos,
            'totalTrabajadores' => $totalTrabajadores,
            'accesos' => $permisos,
            'datos' => $listaTrabajadores,
            'em' => \Session::get('empresa')->get()
        );
        
        return Response::json($datos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    
    public function input()
    {
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mesAnterior){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'apellidos' => $empleado->apellidos,
                            'nombreCompleto' => $empleado->nombreCompleto()
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');        
        
        $datos = array(
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);
        
    }  
    
    public function inputActivos()
    {
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'apellidos' => $empleado->apellidos,
                            'nombreCompleto' => $empleado->nombreCompleto()
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');        
        
        $datos = array(
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);
        
    }
    
    /*public function seccion($sid = null)
    {
        if($sid){
            $seccion = Seccion::whereSid($sid)->first();
            $trabajadores = Trabajador::where('seccion_id', $seccion->id)->where('estado', 'Ingresado')->orderBy('apellidos')->get();
        }else{
            $trabajadores = Trabajador::where('estado', 'Ingresado')->orderBy('apellidos')->get();
        }
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $listaTrabajadores[]=array(
                    'id' => $trabajador->id,
                    'sid' => $trabajador->sid,
                    'rutFormato' => $trabajador->rut_formato(),
                    'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
                    'seccion' => array(
                        'id' => $trabajador->ficha()->seccion->id,
                        'sid' => $trabajador->ficha()->seccion->sid,
                        'nombre' => $trabajador->ficha()->seccion->nombre
                    )
                );
            }
        }
        
        
        $datos = array(
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);
    }*/
    
    public function ingresados()
    {
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;    
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rutFormato' => $trabajador->rut_formato(),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'celular' => $empleado->celular,
                            'email' => $empleado->email,
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                            ),                     
                            'fechaIngreso' => $empleado->fecha_ingreso,
                            'monedaSueldo' => $empleado->moneda_sueldo,
                            'sueldoBase' => $empleado->sueldo_base,
                            'sueldoBasePesos' => Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo),
                            'afp' => array(
                                'id' => $empleado->afp ? $empleado->afp->id : "",
                                'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
                            ),
                            'tipoContrato' => array(
                                'id' => $empleado->tipo_contrato ? $empleado->tipo_contrato->id : "",
                                'nombre' => $empleado->tipo_contrato ? $empleado->tipo_contrato->nombre : ""
                            ),
                            'estado' => $empleado->estado
                        );
                    }
                }
            }
        }
        
        
        $datos = array(
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);
    }
    
    public function trabajadoresVacaciones()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;    
        $mes = \Session::get('mesActivo')->mes;
        
        $trabajadores = Trabajador::all();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#trabajadores-vacaciones');
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mes){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rut' => $trabajador->rut,
                            'rutFormato' => $trabajador->rut_formato(),
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                            ),                     
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ),
                            'fechaIngreso' => $empleado->fecha_ingreso,                           
                            'vacaciones' => $trabajador->misVacaciones()
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
                
        $datos = array(
            'datos' => $listaTrabajadores,            
            'accesos' => $permisos
        );
        
        return Response::json($datos);
    }
    
    public function trabajadoresSemanaCorrida()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#semana-corrida');
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;    
        $trabajadores = Trabajador::all();
        $semanas = MesDeTrabajo::semanas();        
        
        $listaTrabajadoresSemanal=array();
        $listaTrabajadoresMensual=array();
        $empresa = \Session::get('empresa');
        
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes && $empleado->semana_corrida==1){
                        if($empleado->tipo_semana=='s'){
                            $listaTrabajadoresSemanal[]=array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rut' => $trabajador->rut,
                                'rutFormato' => $trabajador->rut_formato(),
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                                ),    
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'semanaCorrida' => $trabajador->totalSemanaCorridas(),
                                'total' => $trabajador->totalSemanaCorrida()
                            );
                        }else{
                            $listaTrabajadoresMensual[]=array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rut' => $trabajador->rut,
                                'rutFormato' => $trabajador->rut_formato(),
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                                ),    
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'semanaCorrida' => $trabajador->totalSemanaCorridas(),
                                'total' => $trabajador->totalSemanaCorridas()
                            );
                        }
                    }
                }
            }
            $listaTrabajadoresSemanal = Funciones::ordenar($listaTrabajadoresSemanal, 'apellidos');
            $listaTrabajadoresMensual = Funciones::ordenar($listaTrabajadoresMensual, 'apellidos');
        }        
        
        
                
        $datos = array(
            'trabajadoresSemanal' => $listaTrabajadoresSemanal,
            'trabajadoresMensual' => $listaTrabajadoresMensual,
            'semanas' => $semanas,
            'accesos' => $permisos
        );
        
        return Response::json($datos);
    }
    
    public function trabajadoresSueldoHora()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#sueldo-hora');
        $trabajadores = Trabajador::all();        
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes && $empleado->tipo_sueldo=='Por Hora' || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mesAnterior && $empleado->tipo_sueldo=='Por Hora'){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rut' => $trabajador->rut,
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                            ),        
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ),
                            'fechaIngreso' => $empleado->fecha_ingreso, 
                            'rutFormato' => $trabajador->rut_formato(),
                            'sueldo' => $empleado->sueldo_base,
                            'moneda' => $empleado->moneda_sueldo,
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'horas' => $empleado->horas,
                            'horasMes' => $trabajador->horasMes(),
                            'totalHoras' => $trabajador->totalHoras()
                        );
                    }
                }
            }
        }        
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
                
        $datos = array(
            'datos' => $listaTrabajadores,
            'accesos' => $permisos
        );
        
        return Response::json($datos);
    }
    
    public function trabajadorSueldoHora($sid)
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#sueldo-hora');
        $mes = \Session::get('mesActivo');
        $trabajador = Trabajador::whereSid($sid)->first();
        $ficha = $trabajador->ficha();
        
        $datosTrabajador = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'nombreCompleto' => $ficha->nombreCompleto(),
            'detalle' => $trabajador->detalleHoras()
        );
                            
        $datos = array(
            'datos' => $datosTrabajador,
            'mes' => $mes,
            'accesos' => $permisos
        );
        
        return Response::json($datos);
    }
    
    public function updateSemanaCorrida()
    {
        $datos = Input::all();
        
        $id = $datos['id'];
        
        if($datos['tipo']=='semanal'){
            $semanas = $datos['semanas'];
            $semanaCorrida = SemanaCorrida::find($id);

            foreach($semanas as $semana){
                $nombre = $semana['alias'];
                $semanaCorrida->$nombre = $semana['comision'];
            }
        }else{
            $semanaCorrida = SemanaCorrida::find($id);
            $semanaCorrida->semana_1 = $datos['comision'];
        }
        $semanaCorrida->save();
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $semanaCorrida->sid
        );

        return Response::json($respuesta);
    }
    
    public function trabajadorVacaciones($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#trabajadores-vacaciones');
        $mes = \Session::get('mesActivo');
        $empleado = $trabajador->ficha();
        $empresa = \Session::get('empresa');
        $feriados = Empresa::feriadosVacaciones();
        $primerMes = $empresa->primerMes();
        $ultimoMes = $empresa->ultimoMes();
        
        $trabajadorVacaciones = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
            'vacacionesMesActual' => $trabajador->mesActualVacaciones(),
            'vacacionesIniciales' => $empleado->vacaciones,
            'vacaciones' => $trabajador->miHistorialVacaciones(),
            'feriados' => $feriados
        );
        
        $datos = array(
            'accesos' => $permisos,
            'primerMes' => $primerMes,
            'ultimoMes' => $ultimoMes,
            'datos' => $trabajadorVacaciones
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadoresDocumentos()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#asociar-documentos');
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;    
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'rut' => $trabajador->rut,
                            'rutFormato' => $trabajador->rut_formato(),
                            'nombreCompleto' => $empleado->nombreCompleto(),                        
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                            ),     
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ), 
                            'totalDocumentos' => $trabajador->totalDocumentos()
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);
    }
    
    public function trabajadorDocumentos($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#asociar-documentos');
        
        $trabajadorDocumentos = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'documentos' => $trabajador->misDocumentos()
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorDocumentos
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadoresCartasNotificacion()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cartas-de-notificacion');
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;     
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $cartas = $trabajador->totalCartasNotificacion();
                        if($cartas){
                            $listaTrabajadores[]=array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rut' => $trabajador->rut,          
                                'rutFormato' => $trabajador->rut_formato(),          
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                                ),       
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'totalCartasNotificacion' => $cartas

                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);
    }
    
    public function trabajadoresCertificados()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#certificados');
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;     
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $totalCertificados = $trabajador->totalCertificados();
                        if($totalCertificados){
                            $listaTrabajadores[]=array(
                                'id' => $empleado->id,
                                'sid' => $trabajador->sid,
                                'rut' => $trabajador->rut,
                                'rutFormato' => $trabajador->rut_formato(),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),      
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",                         
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                                ),           
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ),
                                'totalCertificados' => $totalCertificados
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');        
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);
    }
    
    public function trabajadorCertificados($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#certificados');
        
        $trabajadorCertificados = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'celular' => $empleado->celular,
            'email' => $empleado->email,
            'cargo' => array(
                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
            ),                     
            'fechaIngreso' => $empleado->fecha_ingreso,
            'monedaSueldo' => $empleado->moneda_sueldo,
            'sueldoBase' => $empleado->sueldo_base,
            'sueldoBasePesos' => Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo),
            'afp' => array(
                'id' => $empleado->afp ? $empleado->afp->id : "",
                'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
            ),
            'tipoContrato' => array(
                'id' => $empleado->tipo_contrato ? $empleado->tipo_contrato->id : "",
                'nombre' => $empleado->tipo_contrato ? $empleado->tipo_contrato->nombre : ""
            ),
            'estado' => $empleado->estado,
            'certificados' => $trabajador->misCertificados()
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorCertificados
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadorCartasNotificacion($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cartas-de-notificacion');
        
        $trabajadorCartasNotificacion = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'celular' => $empleado->celular,
            'email' => $empleado->email,
            'cargo' => array(
                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
            ),                     
            'fechaIngreso' => $empleado->fecha_ingreso,
            'monedaSueldo' => $empleado->moneda_sueldo,
            'sueldoBase' => $empleado->sueldo_base,
            'sueldoBasePesos' => Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo),
            'afp' => array(
                'id' => $empleado->afp ? $empleado->afp->id : "",
                'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
            ),
            'tipoContrato' => array(
                'id' => $empleado->tipo_contrato ? $empleado->tipo_contrato->id : "",
                'nombre' => $empleado->tipo_contrato ? $empleado->tipo_contrato->nombre : ""
            ),
            'estado' => $empleado->estado,
            'cartasNotificacion' => $trabajador->misCartasNotificacion()
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorCartasNotificacion
        );
        
        return Response::json($datos);     
    }   
    
    public function trabajadorContratos($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ultimaFicha();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#trabajadores');
        
        $trabajadorContratos = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'contratos' => $trabajador->misContratos(),
            'isFicha' => $trabajador->ficha() ? true : false
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorContratos
        );
        
        return Response::json($datos);     
    }   
    
    public function trabajadorFichas($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ultimaFicha();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#trabajadores');
        
        $trabajadorFichas = array(
            'id' => $trabajador->id,
            'idFicha' => $empleado->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'fichas' => $trabajador->misFichas()
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorFichas
        );
        
        return Response::json($datos);     
    }    
    
    public function trabajadoresLiquidaciones()
    {   
        if(!\Session::get('empresa')){
            return Response::json(array('sinLiquidacion' => array(), 'conLiquidacion' => array(),'sinLiquidacionFiniquitados' => array(), 'conLiquidacionFiniquitados' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#liquidaciones-de-sueldo');
        $mes = \Session::get('mesActivo')->mes;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes)));
        $finMes = \Session::get('mesActivo')->fechaRemuneracion; 
        $finMesAnterior = Funciones::obtenerFechaRemuneracionMes(date('m', strtotime($mesAnterior)), date('Y', strtotime($mesAnterior)));
        $trabajadores = Trabajador::all();
        $liquidaciones = Liquidacion::where('mes', $mes)->orderBy('trabajador_apellidos')->get();
        
        $listaTrabajadores = array();
        $listaLiquidaciones = array();
        $listaFiniquitados = array();
        $listaLiquidacionesFiniquitados = array();
        $mostrarFiniquitados = Empresa::variableConfiguracion('finiquitados_liquidacion');
        
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mes){
                        if(!$trabajador->isLiquidacion()){

                            $observacion = LiquidacionObservacion::where('periodo', $mes)
                                ->where('trabajador_id', $trabajador->id )->first();

                            $listaTrabajadores[]=array(
                                'id' => $empleado->id,
                                'sidTrabajador' => $trabajador->sid,
                                'rut' => $trabajador->rut,
                                'rutFormato' => $trabajador->rut_formato(),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                                'cargo' => $empleado->cargo ? $empleado->cargo->nombre : "",
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ),
                                'sueldoBase' => array(
                                    'tipo' => $empleado->tipo_sueldo,
                                    'monto' => ($empleado->sueldo_base + 0),
                                    'moneda' => $empleado->moneda_sueldo,
                                    'montoPesos' => Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo)
                                ),
                                'estado' => $empleado->estado,
                                'observaciones' => $observacion ? $observacion->observaciones : ""
                            );
                        }
                    }
                }
            }
        }
        
        if($trabajadores->count() && $mostrarFiniquitados){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMesAnterior && $empleado->fecha_finiquito >= $mesAnterior){
                        if(!$trabajador->isLiquidacion()){
                            $observacion = LiquidacionObservacion::where('periodo', $mes)
                                ->where('trabajador_id', $trabajador->id )->first();
                            $listaFiniquitados[]=array(
                                'id' => $empleado->id,
                                'sidTrabajador' => $trabajador->sid,
                                'rut' => $trabajador->rut,
                                'rutFormato' => $trabajador->rut_formato(),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                                'fechaFiniquito' => $empleado->fecha_finiquito,
                                'cargo' => $empleado->cargo ? $empleado->cargo->nombre : "",
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ),
                                'sueldoBase' => array(
                                    'tipo' => $empleado->tipo_sueldo,
                                    'monto' => ($empleado->sueldo_base + 0),
                                    'moneda' => $empleado->moneda_sueldo,
                                    'montoPesos' => Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo)
                                ),
                                'estado' => $empleado->estado,
                                'observaciones' => $observacion? $observacion->observaciones : ""
                            );
                        }
                    }
                }
            }
        }
        
        if( $liquidaciones->count() ){
            foreach( $liquidaciones as $liquidacion ){
                if($liquidacion->estado==1 || true){
                    if($liquidacion->trabajador_centro_costo){
                        $centro = $liquidacion->trabajador_centro_costo;
                    }else{
                        if($liquidacion->centroCosto){
                            $centro = $liquidacion->centroCosto->nombre;
                        }else{
                            $empleado = $liquidacion->trabajador->ficha();
                            if($empleado->centroCosto){
                                $centro = $empleado->centroCosto->nombre;
                            }else{
                                $centro = '';
                            }
                        }
                    }
                    $listaLiquidaciones[]=array(
                        'id' => $liquidacion->trabajador_id,
                        'sid' => $liquidacion->sid,
                        'sidDocumento' => $liquidacion->documento->sid,
                        'nombreDocumento' => $liquidacion->documento->nombre,
                        'aliasDocumento' => $liquidacion->documento->alias,
                        'sidTrabajador' => $liquidacion->trabajador->sid,
                        'rut' => $liquidacion->trabajador->rut,
                        'rutFormato' => $liquidacion->trabajador->rut_formato(),
                        'apellidos' => ucwords(strtolower($liquidacion->trabajador_apellidos)),
                        'cargoOrden' => ucwords(strtolower($liquidacion->trabajador_cargo)),
                        'seccionOrden' => $liquidacion->trabajador_seccion ? ucwords(strtolower($liquidacion->trabajador_seccion)) : "", 
                        'seccion' => array(
                            'nombre' => $liquidacion->trabajador_seccion,
                        ), 
                        'centroCostoOrden' => $centro, 
                        'centroCosto' => array(
                            'nombre' => $centro,
                        ),
                        'nombreCompleto' => $liquidacion->nombreCompleto(),
                        'cargo' => $liquidacion->trabajador_cargo,              
                        'sueldoBasePesos' => $liquidacion->sueldo_base,
                        'sueldoLiquido' => $liquidacion->sueldo_liquido,
                        'observaciones' => $liquidacion->observacion
                    );
                }else{
                    if($mostrarFiniquitados){
                        if($liquidacion->trabajador_centro_costo){
                            $centro = $liquidacion->trabajador_centro_costo;
                        }else{
                            if($liquidacion->centroCosto){
                                $centro = $liquidacion->centroCosto->nombre;
                            }else{
                                $empleado = $liquidacion->trabajador->ficha();
                                if($empleado->centroCosto){
                                    $centro = $empleado->centroCosto->nombre;
                                }else{
                                    $centro = '';
                                }
                            }
                        }
                        $listaLiquidacionesFiniquitados[]=array(
                            'id' => $liquidacion->trabajador_id,
                            'sid' => $liquidacion->sid,
                            'sidDocumento' => $liquidacion->documento->sid,
                            'nombreDocumento' => $liquidacion->documento->nombre,
                            'aliasDocumento' => $liquidacion->documento->alias,
                            'sidTrabajador' => $liquidacion->trabajador->sid,
                            'rut' => $liquidacion->trabajador->rut,
                            'rutFormato' => $liquidacion->trabajador->rut_formato(),
                            'apellidos' => ucwords(strtolower($liquidacion->trabajador_apellidos)),
                            'cargoOrden' => ucwords(strtolower($liquidacion->trabajador_cargo)),
                            'seccionOrden' => $liquidacion->trabajador_seccion ? ucwords(strtolower($liquidacion->trabajador_seccion)) : "", 
                            'seccion' => array(
                                'nombre' => $liquidacion->trabajador_seccion,
                            ), 
                            'centroCostoOrden' => $centro, 
                            'centroCosto' => array(
                                'nombre' => $centro,
                            ),
                            'nombreCompleto' => $liquidacion->nombreCompleto(),
                            'cargo' => $liquidacion->trabajador_cargo,              
                            'sueldoBasePesos' => $liquidacion->sueldo_base,
                            'sueldoLiquido' => $liquidacion->sueldo_liquido,
                            'observaciones' => $liquidacion->observacion
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        $listaLiquidaciones = Funciones::ordenar($listaLiquidaciones, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'sinLiquidacion' => $listaTrabajadores,
            'conLiquidacion' => $listaLiquidaciones,
            'sinLiquidacionFiniquitados' => $listaFiniquitados,
            'conLiquidacionFiniquitados' => $listaLiquidacionesFiniquitados,
            'cuentas' => Liquidacion::comprobarCuentas($liquidaciones),
            'mostrarFiniquitados' => $mostrarFiniquitados,
            'mesAnterior' => $mesAnterior
        );
        
        return Response::json($datos);
    }
    
    public function generarF1887Trabajadores()
    {               
        $mes = \Session::get('mesActivo');
		$empresa = \Session::get('empresa');
		$rutEmpresa = Funciones::formatear_rut($empresa->rut);
        $empresa->domicilio = $empresa->domicilio();
        $empresa->rut_formato = Funciones::formatear_rut($empresa->rut);        
        $certificados = array();
        $folio = DeclaracionTrabajador::obtenerUltimoFolio();
                
        $datos = Input::all();
        $sid = (array) $datos['trabajadores'];
        $anio = $datos['anio'];
        $isComprobar = $datos['comprobar'];
        $anioRemuneracion = AnioRemuneracion::where('anio', $anio)->first();
        $trabajadores = Trabajador::whereIn('sid', $sid)->get();
        $lista = array();
        $destination = public_path() . '/planillas/1887_1.xlsx';
        $folder = public_path() . '/stories/';
        $resumen = null;
        $fecha = date('d / m / Y');        
        $empresa->fecha = $empresa->comuna->provincia->provincia . ' ' . $fecha;
        
        if($isComprobar){
            $comprobar = $this->comprobarDeclaraciones($datos['trabajadores'], $anioRemuneracion);
        }

        if($trabajadores->count()){            
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->fichaAnual($anio);
                if($empleado){                            
                    $filename = '1887_1_'.$trabajador->rut.'_'.$empresa->rut.'_'.$anio;   
                    $resumen = $empleado->resumen($anio);
                    $folio = DeclaracionTrabajador::obtenerSiguienteFolio($folio);
                    Excel::load($destination, function($reader) use($trabajador, $empleado, $anio, $empresa, $fecha, $filename, $folio, $resumen) {             
                        $i = 24;                                                
                        $sheet = $reader->getActiveSheet();
                        for($x=0; $x<13; $x++){
                            $a = 'G' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['sueldo']));
                            $a = 'K' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['cotizacionPrevisional']));
                            $a = 'O' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rentaImponible']));
                            $a = 'S' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['impuestoUnico']));
                            $a = 'W' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['mayorRetencion']));
                            $a = 'AA' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rentaTotal']));
                            $a = 'AE' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rentaNoGravada']));
                            $a = 'AI' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rebaja']));
                            $a = 'AM' . $i;
                            $sheet->setCellValue($a, $resumen[$x]['factor']);
                            $a = 'AQ' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rentaAfecta']));
                            $a = 'AU' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['impuestoUnicoRetenido']));
                            $a = 'AY' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['mayorRetencionImpuesto']));
                            $a = 'BC' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rentaTotalExenta']));
                            $a = 'BG' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rentaTotalNoGravada']));
                            $a = 'BJ' . $i;
                            $sheet->setCellValue($a, Funciones::formatoPesos($resumen[$x]['rebajaZonasExtremas']));
                            $i++;
                            
                        }
                        $sheet->setCellValue('M3', $empresa->razon_social);
                        $sheet->setCellValue('M4', $empresa->rut_formato());
                        $sheet->setCellValue('BA4', $folio);
                        $sheet->setCellValue('M5', $empresa->domicilio());
                        $sheet->setCellValue('BA5', $empresa->comuna->provincia->provincia . ' ' . $fecha);
                        $sheet->setCellValue('M6', $empresa->actividad_economica);
                        $sheet->setCellValue('K15', $empresa->razon_social);
                        $sheet->setCellValue('AH15', $empleado->nombreCompleto());
                        $sheet->setCellValue('AX15', $trabajador->rut_formato());
                        $sheet->setCellValue('BJ15', $anio);                                                
                            
                    })->setFilename($filename)->store('xlsx', $folder);
                    
                    $declaracion = new DeclaracionTrabajador();
                    $declaracion->sid = Funciones::generarSID();
                    $declaracion->nombre_archivo = $filename . '.xlsx';
                    $declaracion->folio = $folio;
                    $declaracion->trabajador_id = $trabajador->id;
                    $declaracion->anio_id = $anioRemuneracion->id;
                    $declaracion->sueldo = $resumen[12]['sueldo'];
                    $declaracion->cotizacion_previsional = $resumen[12]['cotizacionPrevisional'];
                    $declaracion->renta_imponible = $resumen[12]['rentaImponible'];
                    $declaracion->impuesto_unico = $resumen[12]['impuestoUnico'];
                    $declaracion->mayor_retencion = $resumen[12]['mayorRetencion'];
                    $declaracion->renta_total = $resumen[12]['rentaTotal'];
                    $declaracion->renta_no_gravada = $resumen[12]['rentaNoGravada'];
                    $declaracion->rebaja = $resumen[12]['rebaja'];
                    $declaracion->factor = $resumen[12]['factor'];
                    $declaracion->renta_afecta = $resumen[12]['rentaAfecta'];
                    $declaracion->impuesto_unico_retenido = $resumen[12]['impuestoUnicoRetenido'];
                    $declaracion->mayor_retencion_impuesto = $resumen[12]['mayorRetencionImpuesto'];
                    $declaracion->renta_total_exenta = $resumen[12]['rentaTotalExenta'];
                    $declaracion->renta_total_no_gravada = $resumen[12]['rentaTotalNoGravada'];
                    $declaracion->rebaja_zonas_extremas = $resumen[12]['rebajaZonasExtremas'];
                    $declaracion->renta_imponible_actualizada = $resumen[12]['rentaImponibleActualizada'];
                    $declaracion->actividad = $resumen[12]['actividad'];
                    $declaracion->save();
                                        
                    $filenamePDF = 'certificado.pdf';
                    /*$destination = public_path() . '/stories/' . $filenamePDF;
                    $pdf = new \Thujohn\Pdf\Pdf();
                    $content = $pdf->load(View::make('pdf.certificado', array('datos' => $resumen, 'empresa' => $empresa, 'folio' => $folio)), 'A4', 'landscape')->output();          
                    File::put($destination, $content); */
                    
                    $lista[] = array(
                        'id' => $trabajador->id,
                        'nombreCompleto' => $empleado->nombreCompleto(),
                        'rutFormato' => $trabajador->rut_formato(),
                        'rut' => $trabajador->rut,
                        'resumen' => $resumen,
                        'declaracion' => $declaracion,
                        'nombre' => $filename . '.xlsx',
                        'alias' => $filename . '.xlsx'
                    );
                    $folio++;
                }
            }                  
        }            
                
        $respuesta = array(
            'datos' => $lista,
            'success' => true,
            'mensaje' => "La Información fue generada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function generarF1887($anio)
    {               
		$empresa = \Session::get('empresa');
		$rutEmpresa = Funciones::formatear_rut($empresa->rut);
        $anioRemuneracion = AnioRemuneracion::whereSid($anio)->first();
        //$declaraciones = DeclaracionTrabajador::where('anio_id', $anioRemuneracion->id)->orderBy('folio')->get();
        $obtenerDeclaraciones = DeclaracionTrabajador::obtenerDeclaraciones($anioRemuneracion->id);
        $declaraciones = $obtenerDeclaraciones['declaraciones'];
        $totales = $obtenerDeclaraciones['totales'];

        $filename = '188_'.$empresa->rut.'_'.$anioRemuneracion->anio;
        $destination = public_path() . '/planillas/188.xlsx';
        $folder = public_path() . '/stories/';
        $fecha = date('d / m / Y');
        $anio = $anioRemuneracion->anio;
        $lista = array();
        $folio = F1887::obtenerFolio();
        
        $comprobar = F1887::comprobarDeclaracion($anio);

        if(count($declaraciones)){            
            Excel::load($destination, function($reader) use($declaraciones, $totales, $anio, $empresa, $fecha, $filename, $folio) {    
                $count = 0;
                $i = 4;                                                
                $sheet = $reader->sheet(0);
                
                $a = 'BL' . $i;
                $sheet->cell($a, function($cell) use($folio) {
                    $cell->setValue($folio);                                    
                    $cell->setFontWeight('bold');                    
                });
                
                $i++;
                $i++;
                
                $a = 'U' . $i;
                $sheet->cell($a, function($cell) use($anio) {
                    $cell->setValue($anio);                                    
                    $cell->setFontWeight('bold');                    
                });
                
                $i = 11;
                
                $a = 'AE' . $i;
                $sheet->setCellValue($a, $empresa->razon_social);  
                $a = 'B' . $i;
                $sheet->setCellValue($a, $empresa->rut_formato()); 
                
                $i++;
                $i++;
                
                $a = 'B' . $i;
                $sheet->setCellValue($a, $empresa->domicilio()); 
                $a = 'AE' . $i;
                $sheet->setCellValue($a, $empresa->comuna->comuna); 
                
                $i++;
                $i++;
                
                $a = 'B' . $i;
                $sheet->setCellValue($a, ""); 
                $a = 'AE' . $i;
                $sheet->setCellValue($a, $empresa->fax);
                $a = 'AO' . $i;
                $sheet->setCellValue($a, $empresa->telefono);
                
                $i = 21;

                foreach($declaraciones as $declaracion){   
                    
                    $count++;
                    
                    $sheet->mergeCells('B'.$i.':C'.$i);
                    $a = 'B' . $i;
                    $sheet->setCellValue($a, $count); 
                    $a = 'B'.$i.':C'.$i;
                    $sheet->setBorder($a, 'thin');

                    $sheet->mergeCells('D'.$i.':J'.$i);
                    $a = 'D' . $i;
                    $sheet->setCellValue($a, Funciones::formatear_rut($declaracion['rut'])); 
                    $a = 'D'.$i.':J'.$i;
                    $sheet->setBorder($a, 'thin');
                    
                    $sheet->mergeCells('K'.$i.':Q'.$i);
                    $a = 'K' . $i;
                    $sheet->setCellValue($a, Funciones::formatoPesos($declaracion['rentaAfecta'])); 
                    $a = 'K'.$i.':Q'.$i;
                    $sheet->setBorder($a, 'thin');
                    
                    $sheet->mergeCells('R'.$i.':X'.$i);
                    $a = 'R' . $i;
                    $sheet->setCellValue($a, Funciones::formatoPesos($declaracion['impuestoUnicoRetenido'])); 
                    $a = 'R'.$i.':X'.$i;
                    $sheet->setBorder($a, 'thin');
                    
                    $sheet->mergeCells('Y'.$i.':AF'.$i);
                    $a = 'Y' . $i;
                    $sheet->setCellValue($a, Funciones::formatoPesos($declaracion['mayorRetencionImpuesto'])); 
                    $a = 'Y'.$i.':AF'.$i;
                    $sheet->setBorder($a, 'thin');
                    
                    $sheet->mergeCells('AG'.$i.':AL'.$i);
                    $a = 'AG' . $i;
                    $sheet->setCellValue($a, Funciones::formatoPesos($declaracion['rentaTotalNoGravada'])); 
                    $a = 'AG'.$i.':AL'.$i;
                    $sheet->setBorder($a, 'thin');
                    
                    $sheet->mergeCells('AM'.$i.':AS'.$i);
                    $a = 'AM' . $i;
                    $sheet->setCellValue($a, Funciones::formatoPesos($declaracion['rentaTotalExenta'])); 
                    $a = 'AM'.$i.':AS'.$i;
                    $sheet->setBorder($a, 'thin');
                    
                    $sheet->mergeCells('AT'.$i.':AY'.$i);
                    $a = 'AT' . $i;
                    $sheet->setCellValue($a, Funciones::formatoPesos($declaracion['rebajaZonasExtremas'])); 
                    $a = 'AT'.$i.':AY'.$i;
                    $sheet->setBorder($a, 'thin');                                    
                    
                    $a = 'AZ' . $i;
                    $sheet->setCellValue($a, $declaracion['enero']); 
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BA' . $i;
                    $sheet->setCellValue($a, $declaracion['febrero']); 
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BB' . $i;
                    $sheet->setCellValue($a, $declaracion['marzo']); 
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BC' . $i;
                    $sheet->setCellValue($a, $declaracion['abril']); 
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BD' . $i;
                    $sheet->setCellValue($a, $declaracion['mayo']);  
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BE' . $i;
                    $sheet->setCellValue($a, $declaracion['junio']);  
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BF' . $i;
                    $sheet->setCellValue($a, $declaracion['julio']);  
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BG' . $i;
                    $sheet->setCellValue($a, $declaracion['agosto']);  
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BH' . $i;
                    $sheet->setCellValue($a, $declaracion['septiembre']); 
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BI' . $i;
                    $sheet->setCellValue($a, $declaracion['octubre']);  
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BJ' . $i;
                    $sheet->setCellValue($a, $declaracion['noviembre']); 
                    $sheet->setBorder($a, 'thin');
                    
                    $a = 'BK' . $i;
                    $sheet->setCellValue($a, $declaracion['diciembre']);  
                    $sheet->setBorder($a, 'thin');
                    
                    $sheet->mergeCells('BL'.$i.':BO'.$i);
                    $a = 'BL' . $i;
                    $sheet->setCellValue($a, $declaracion['folio']); 
                    $a = 'BL'.$i.':BO'.$i;
                    $sheet->setBorder($a, 'thin');

                    $i++;                    
                }
                
                $i++;
                $i++;
                
                $sheet->mergeCells('B'.$i.':BA'.($i + 1));
                $a = 'B' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('TOTAL MONTOS ANUALES SIN ACTUALIZAR');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'B'.$i.':BA'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('BB'.$i.':BH'.($i + 5));
                $a = 'BB' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('TOTAL REMUNERACIÓN IMPONIBLE PARA EFECTOS PREVISIONALES ACTUALIZADA A TODOS LOS TRABAJADORES');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'BB'.$i.':BH'.($i + 5);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $i++;
                $i++;
                
                $sheet->mergeCells('B'.$i.':J'.($i + 3));
                $a = 'B' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('RENTA TOTAL NETA PAGADA (Art.42 N°1, Ley de la Renta)');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'B'.$i.':J'.($i + 3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('K'.$i.':AG'.$i);
                $a = 'K' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('IMPUESTO UNICO RETENIDO');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'K'.$i.':AG'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AH'.$i.':AN'.($i + 3));
                $a = 'AH' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('RENTA TOTAL EXENTA NO GRAVADA');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'AH'.$i.':AN'.($i + 3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AO'.$i.':AU'.($i + 3));
                $a = 'AO' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('RENTA TOTAL EXENTA');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'AO'.$i.':AU'.($i + 3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AV'.$i.':BA'.($i + 3));
                $a = 'AV' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('REBAJA POR ZONAS EXTREMAS (FRANQUICIA D.L.889)');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'AV'.$i.':BA'.($i + 3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $i++;
                
                $sheet->mergeCells('K'.$i.':S'.($i + 2));
                $a = 'K' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('POR RENTA TOTAL NETA PAGADA DURANTE EL AÑO');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'K'.$i.':S'.($i + 2);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('T'.$i.':AG'.($i + 2));
                $a = 'T' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('POR RENTAS ACCESORIAS Y/O COMPLEMENTARIA PAGADA ENTRE ENE-ABR. AÑO SGTE.');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'T'.$i.':AG'.($i + 2);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $i++;
                $i++;
                $i++;
                
                $sheet->mergeCells('B'.$i.':J'.($i + 1));
                $a = 'B' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rentaImponible'])); 
                $a = 'B'.$i.':J'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('K'.$i.':S'.($i + 1));
                $a = 'K' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['impuestoUnico'])); 
                $a = 'K'.$i.':S'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('T'.$i.':AG'.($i + 1));
                $a = 'T' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos(0)); 
                $a = 'T'.$i.':AG'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AH'.$i.':AN'.($i + 1));
                $a = 'AH' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rentaNoGravada'])); 
                $a = 'AH'.$i.':AN'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AO'.$i.':AU'.($i + 1));
                $a = 'AO' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rentaTotal'])); 
                $a = 'AO'.$i.':AU'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AV'.$i.':BA'.($i + 1));
                $a = 'AV' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rebaja'])); 
                $a = 'AV'.$i.':BA'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('BB'.$i.':BH'.($i + 1));
                $a = 'BB' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rentaImponibleActualizada'])); 
                $a = 'BB'.$i.':BH'.($i + 1);
                $sheet->setBorder($a, 'thin');
                                
                $i++;
                $i++;
                $i++;
                $i++;
                
                $sheet->mergeCells('D'.$i.':AW'.($i + 1));
                $a = 'D' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('CUADRO RESUMEN FINAL DE LA DECLARACIÓN');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'D'.$i.':AW'.($i + 1);
                $sheet->setBorder($a, 'thin');
                
                $i++;   
                $i++;   
                
                $sheet->mergeCells('D'.$i.':AP'.$i);
                $a = 'D' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('TOTAL MONTOS ANUALES ACTUALIZADOS');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'D'.$i.':AP'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AQ'.$i.':AW'.($i +4));
                $a = 'AQ' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('TOTAL DE CASOS INFORMADOS');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'AQ'.$i.':AW'.($i +4);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $i++;
                
                $sheet->mergeCells('D'.$i.':J'.($i +3));
                $a = 'D' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('RENTA TOTAL NETA PAGADA (Art.42 N°1, Ley de la Renta)');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'D'.$i.':J'.($i +3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('K'.$i.':Q'.($i +3));
                $a = 'K' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('IMPUESTO UNICO RETENIDO');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'K'.$i.':Q'.($i +3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('R'.$i.':W'.($i +3));
                $a = 'R' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('MAYOR RETENCIÓN SOLICITADA (Art.88 L.I.R)');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'R'.$i.':W'.($i +3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('X'.$i.':AC'.($i +3));
                $a = 'X' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('RENTA TOTAL EXENTA Y NO GRAVADA');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'X'.$i.':AC'.($i +3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AD'.$i.':AI'.($i +3));
                $a = 'AD' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('RENTA TOTAL EXENTA');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'AD'.$i.':AI'.($i +3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AJ'.$i.':AP'.($i +3));
                $a = 'AJ' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('REBAJA POR ZONAS EXTREMAS (FRANQUICIA D.L.889)');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'AJ'.$i.':AP'.($i +3);
                $sheet->getStyle($a)->getAlignment()->setWrapText(true); 
                $sheet->setBorder($a, 'thin');
                
                $i++;
                $i++;
                $i++;
                $i++;
                
                $sheet->mergeCells('D'.$i.':J'.$i);
                $a = 'D' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rentaAfecta'])); 
                $a = 'D'.$i.':J'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('K'.$i.':Q'.$i);
                $a = 'K' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['impuestoUnicoRetenido'])); 
                $a = 'K'.$i.':Q'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('R'.$i.':W'.$i);
                $a = 'R' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['mayorRetencionImpuesto'])); 
                $a = 'R'.$i.':W'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('X'.$i.':AC'.$i);
                $a = 'X' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rentaTotalNoGravada'])); 
                $a = 'X'.$i.':AC'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AD'.$i.':AI'.$i);
                $a = 'AD' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rentaTotalExenta'])); 
                $a = 'AD'.$i.':AI'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AJ'.$i.':AP'.$i);
                $a = 'AJ' . $i;
                $sheet->setCellValue($a, Funciones::formatoPesos($totales['rebajaZonasExtremas'])); 
                $a = 'AJ'.$i.':AP'.$i;
                $sheet->setBorder($a, 'thin');
                
                $sheet->mergeCells('AQ'.$i.':AW'.$i);
                $a = 'AQ' . $i;
                $sheet->setCellValue($a, $count); 
                $a = 'AQ'.$i.':AW'.$i;
                $sheet->setBorder($a, 'thin');
                
                $i++;
                $i++;
                
                $a = 'B' . $i;
                $sheet->mergeCells('B'.$i.':BH'.$i);
                $sheet->cell($a, function($cell) {
                    $cell->setValue('DECLARO BAJO JURAMENTO QUE LOS DATOS CONTENIDOS EN EL PRESENTE DOCUMENTO SON LA EXPRESION FIEL DE LA VERDAD, POR LO QUE ASUMO LA RESPONSABILIDAD');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                });
                
                $i++;
                $i++;
                $i++;
                $i++;
                
                $sheet->mergeCells('B'.$i.':M'.$i);
                $a = 'B' . $i;
                $sheet->cell($a, function($cell) {
                    $cell->setValue('RUT REPRESENTANTE LEGAL');
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setFontWeight('bold');
                    $cell->setAlignment( 'center');
                });
                $a = 'B'.$i.':M'.$i;
                $sheet->setBorder($a, 'thin');
                
                $i++;
                
                $sheet->mergeCells('B'.$i.':M'.$i);
                $a = 'B' . $i;
                $sheet->cell($a, function($cell) use($empresa) {
                    $cell->setValue(Funciones::formatear_rut($empresa->representante_rut));
                    $cell->setFontFamily('Arial');
                    $cell->setFontSize(8);
                    $cell->setAlignment( 'center');
                });
                $a = 'B'.$i.':M'.$i;
                $sheet->setBorder($a, 'thin');    
                
                $sheet->row(34, function ($row) {
                    $row->setFontFamily('Arial');
                    $row->setFontSize(8);
                    $row->setFontWeight('bold');
                });

            })->setFilename($filename)->store('xlsx', $folder);
        }        
        
        $filenameCSV = $filename . ".csv";        
        
        $f1887 = new F1887();
        $f1887->sid = Funciones::generarSID();
        $f1887->folio = $folio;
        $f1887->anio = $anio;
        $f1887->rut_empresa = $empresa->rut;
        $f1887->nombre_empresa = $empresa->razon_social;
        $f1887->domicilio_empresa = $empresa->domicilio();
        $f1887->comuna_empresa = $empresa->comuna->comuna;
        $f1887->email_empresa = '';
        $f1887->fax_empresa = $empresa->fax ? $empresa->fax : '';
        $f1887->telefono_empresa = $empresa->telefono ? $empresa->telefono : '';
        $f1887->renta_total_neta = $totales['rentaImponible'];
        $f1887->por_renta_total_neta_pagada_anio = $totales['impuestoUnico'];;
        $f1887->rentas_accesorias = 1;
        $f1887->renta_no_gravada = $totales['rentaNoGravada'];
        $f1887->renta_exenta = $totales['rentaTotal'];
        $f1887->rebaja = $totales['rebaja'];
        $f1887->total_remuneracion_imponible = $totales['rentaImponibleActualizada'];
        $f1887->renta_total_neta_pagada = $totales['rentaAfecta'];
        $f1887->impuesto_unico_retenido = $totales['impuestoUnicoRetenido'];
        $f1887->retencion_solicitada = $totales['mayorRetencionImpuesto'];
        $f1887->renta_total_no_gravada = $totales['rentaTotalNoGravada'];
        $f1887->renta_total_exenta = $totales['rentaTotalExenta'];
        $f1887->rebaja_zonas_extremas = $totales['rebajaZonasExtremas'];
        $f1887->total_casos_informados = count($declaraciones);
        $f1887->rut_representante = $empresa->representante_rut;
        $f1887->excel = $filename . '.xlsx';
        $f1887->csv = $filenameCSV;
        $f1887->save();
        
        $destinationCSV = public_path('stories/' . $filenameCSV);
        $fp = fopen($destinationCSV, "w+");
        if($fp){
            if(count($declaraciones)){
                foreach($declaraciones as $index => $trab){
                    $actividad = $trab['enero'].$trab['febrero'].$trab['marzo'].$trab['abril'].$trab['mayo'].$trab['junio'].$trab['julio'].$trab['agosto'].$trab['septiembre'].$trab['octubre'].$trab['noviembre'].$trab['diciembre'];
                    $detalle = new DetalleF1887();
                    $detalle->sid = Funciones::generarSID();
                    $detalle->f1887_id = $f1887->id;
                    $detalle->folio = $trab['folio'];
                    $detalle->rut = $trab['rut'];
                    $detalle->renta_total_neta_pagada = $trab['rentaAfecta'];
                    $detalle->impuesto_unico_retenido = $trab['impuestoUnicoRetenido'];
                    $detalle->mayor_retencion_solicitada = $trab['mayorRetencionImpuesto'];
                    $detalle->renta_total_exenta = $trab['rentaTotalExenta'];
                    $detalle->renta_total_no_gravada = $trab['rentaTotalNoGravada'];
                    $detalle->rebaja_zonas_extremas = $trab['rebajaZonasExtremas'];
                    $detalle->actividad = $actividad;
                    $detalle->save();
                    unset($trab['rentaTotalExenta']);
                    fputs($fp, utf8_decode(implode(";", $trab))."\r\n", 2048);
                    $obtenerDeclaraciones['declaraciones'][$index]['rutFormato'] = Funciones::formatear_rut($trab['rut']);
                }
            }
            fclose($fp);
        }
        
        $obtenerDeclaraciones['totales']['folio'] = $folio;
                
        $respuesta = array(
            'datos' => $obtenerDeclaraciones,
            'anio' => $anio,
            'folio' => $folio,
            'declaraciones' => $declaraciones,
            'nombreExcel' => $filename . '.xlsx',
            'aliasExcel' => 'F1887.xlsx',
            'nombreCSV' => $filenameCSV,
            'aliasCSV' => 'ArchivoF1887.xls',
            'success' => true,
            'mensaje' => "La Información fue generada correctamente"
        );
        
        return Response::json($respuesta);
    }
    
    public function verF1887($anio)
    {
        $f1887 = F1887::where('anio', $anio)->first();    
        $totales = array();
        $filenameCSV = '';
        $filename = '';
        $folio = '';
        $datosDetalles = array();
        
        if($f1887){
            $totales = array(
                'id' => $f1887->id,
                'sid' => $f1887->sid,
                'folio' => $f1887->folio,
                'anio' => $f1887->anio,
                'rentaAfecta' => $f1887->renta_total_neta_pagada,
                'impuestoUnico' => $f1887->por_renta_total_neta_pagada_anio,
                'rentaNoGravada' => $f1887->renta_no_gravada,
                'rentaTotal' => $f1887->renta_exenta,
                'rebaja' => $f1887->rebaja,
                'rentaImponible' => $f1887->renta_total_neta,
                'impuestoUnicoRetenido' => $f1887->impuesto_unico_retenido,
                'mayorRetencionImpuesto' => $f1887->retencion_solicitada,
                'rentaTotalNoGravada' => $f1887->renta_total_no_gravada,
                'rentaTotalExenta' => $f1887->renta_total_exenta,
                'rebajaZonasExtremas' => $f1887->rebaja_zonas_extremas,
                'rentaImponibleActualizada' => $f1887->total_remuneracion_imponible
            );
            
            $detalles = $f1887->detalles;
            
            if($detalles->count()){
                foreach($detalles as $detalle){
                    $datosDetalles[] = array(
                        'id' => $detalle->id,
                        'rutFormato' => Funciones::formatear_rut($detalle->rut),
                        'rentaAfecta' => $detalle->renta_total_neta_pagada,
                        'impuestoUnicoRetenido' => $detalle->impuesto_unico_retenido,
                        'mayorRetencionImpuesto' => $detalle->mayor_retencion_solicitada,
                        'rentaTotalNoGravada' => $detalle->renta_total_no_gravada,
                        'rentaTotalExenta' => $detalle->renta_total_exenta,
                        'rebajaZonasExtremas' => $detalle->rebaja_zonas_extremas,
                        'folio' => $detalle->folio
                    );
                }
            }
            
            $filenameCSV = $f1887->csv;
            $filename = $f1887->excel;
            $folio = $f1887->folio;
        }
        
        $datos = array(
            'declaraciones' => $datosDetalles,
            'totales' => $totales
        );
        
        $respuesta = array(
            'datos' => $datos,
            'anio' => $anio,
            'folio' => $folio,
            'nombreExcel' => $filename,
            'aliasExcel' => 'F1887.xlsx',
            'nombreCSV' => $filenameCSV,
            'aliasCSV' => 'ArchivoF1887.xls',
        );
        
        return Response::json($respuesta);
    }
    
    public function trabajadoresF1887($sid)
    {   
        if(!\Session::get('empresa')){
            return Response::json(array('sinCertificado' => array(), 'conCertificado' => array(), 'permisos' => array()));
        }
        
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#f1887');
        $mes = \Session::get('mesActivo')->mes;
        $trabajadores = Trabajador::all();
        $aniosRemuneraciones = AnioRemuneracion::aniosF1887();
        
        $listaSinDeclaracion = array();
        $listaConDeclaracion = array();
        
        if(!$sid){
            $mes = MesDeTrabajo::orderBy('mes', 'DESC')->first();  
            $id = $mes->anio_id;        
            $anioRemuneracion = AnioRemuneracion::find($id);
        }else{
            $anioRemuneracion = AnioRemuneracion::whereSid($sid)->first();
        }
        
        if($anioRemuneracion->isDiciembre()){        
            if($trabajadores->count()){
                foreach($trabajadores as $trabajador){
                    $empleado = $trabajador->fichaAnual($anioRemuneracion->anio);
                    if($empleado){
                        if($trabajador->isActivo($anioRemuneracion)){
                            $declaracion = $trabajador->declaracion($anioRemuneracion);
                            if($declaracion){
                                $listaConDeclaracion[]=array(
                                    'id' => $empleado->id,
                                    'sidTrabajador' => $trabajador->sid,
                                    'rut' => $trabajador->rut,
                                    'rutFormato' => $trabajador->rut_formato(),
                                    'isActividad' => true,
                                    'nombreCompleto' => $empleado->nombreCompleto(),
                                    'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                    'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                                    'cargo' => $empleado->cargo ? $empleado->cargo->nombre : "",
                                    'declaracion' => $declaracion
                                );
                            }else{
                                $listaSinDeclaracion[]=array(
                                    'id' => $trabajador->id,
                                    'sidTrabajador' => $trabajador->sid,
                                    'rut' => $trabajador->rut,
                                    'rutFormato' => $trabajador->rut_formato(),
                                    'isActividad' => $trabajador->isActividad($anioRemuneracion),
                                    'nombreCompleto' => $empleado->nombreCompleto(),
                                    'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                    'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                                    'cargo' => $empleado->cargo ? $empleado->cargo->nombre : ""
                                );
                            }
                        }
                    }
                }
            }

            $listaSinDeclaracion = Funciones::ordenar($listaSinDeclaracion, 'apellidos');
            $listaConDeclaracion = Funciones::ordenar($listaConDeclaracion, 'apellidos');
        }
        
        $datos = array(
            'accesos' => $permisos,
            'sinDeclaracion' => $listaSinDeclaracion,
            'conDeclaracion' => $listaConDeclaracion,
            'mes' => $mes,
            'anios' => $aniosRemuneraciones,
            'anio' => $anioRemuneracion,
            'isDeclaracion' => F1887::isDeclaracion($anioRemuneracion->anio)
        );
        
        return Response::json($datos);
    }
    
    public function planillaCostoEmpresa()
    {   
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $mes = \Session::get('mesActivo')->mes;
        $liquidaciones = Liquidacion::where('mes', $mes)->orderBy('trabajador_apellidos')->get();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#planilla-costo-empresa');
        $listaTrabajadores = array();
        
        $totalSueldo = 0;
        $totalImponibles = 0;
        $totalNoImponibles = 0;
        $totalSC = 0;
        $totalSIS = 0;
        $totalCaja = 0;
        $totalMutual = 0;
        $totalISL = 0;
        
        if($liquidaciones->count()){
            foreach( $liquidaciones as $liquidacion ){
                $mutual = $liquidacion->detalleMutual ? $liquidacion->detalleMutual->cotizacion_accidentes : 0;
                $isl = $liquidacion->detalleIpsIslFonasa ? $liquidacion->detalleIpsIslFonasa->cotizacion_isl : 0;
                $sc = $liquidacion->detalleSeguroCesantia ? $liquidacion->detalleSeguroCesantia->aporte_empleador : 0;
                $sis = $liquidacion->detalleAfp ? $liquidacion->detalleAfp->sis : 0;
                $caja = $liquidacion->detalleCaja ? $liquidacion->detalleCaja->cotizacion : 0;
                $listaTrabajadores[]=array(
                    'id' => $liquidacion->id,
                    'idTrabajador' => $liquidacion->trabajador_id,
                    'sid' => $liquidacion->sid,
                    'rutFormato' => $liquidacion->trabajador->rut_formato(),
                    'apellidos' => $liquidacion->trabajador->ficha()->apellidos,
                    'nombreCompleto' => $liquidacion->trabajador->ficha()->nombreCompleto(),
                    'sueldoBasePesos' => $liquidacion->sueldo,
                    'sueldoLiquido' => $liquidacion->sueldo_liquido,
                    'imponibles' => $liquidacion->imponibles,
                    'noImponibles' => $liquidacion->no_imponibles,
                    'mutual' => $mutual,
                    'isl' => $isl,
                    'seguroCesantia' => $sc,
                    'sis' => $sis,
                    'caja' => $caja,
                    'fonasa' => $liquidacion->detalleIpsIslFonasa ? $liquidacion->detalleIpsIslFonasa->cotizacion_fonasa : 0,
                    'aportes' => $liquidacion->total_aportes
                );
                
                $totalSueldo += $liquidacion->sueldo;
                $totalImponibles += $liquidacion->imponibles;
                $totalNoImponibles += $liquidacion->no_imponibles;
                $totalSC += $sc;
                $totalSIS += $sis;
                $totalCaja += $caja;
                $totalMutual += $mutual;
                $totalISL += $isl;
            }
        }
        
        $totales = array(
            'totalSueldo' => $totalSueldo,
            'totalImponibles' => $totalImponibles,
            'totalNoImponibles' => $totalNoImponibles,
            'totalSC' => $totalSC,
            'totalSIS' => $totalSIS,
            'totalCaja' => $totalCaja,
            'totalMutual' => $totalMutual,
            'totalISL' => $totalISL
        );
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
            
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores,
            'totales' => $totales
        );
        
        return Response::json($datos);
    }
    
    public function cartaNotificacion()
    {
        $datos = Input::all();
        $sidTrabajador = $datos['sidTrabajador'];
        $sidPlantilla = $datos['sidPlantilla']['sid'];
        $carta = PlantillaCartaNotificacion::whereSid($sidPlantilla)->first();  
        $clausulas = array();
        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        $empleado = $trabajador->ficha();
        $idEmpresa = \Session::get('empresa')->id;
        $empresa = Empresa::find($idEmpresa);
        
        if($datos['inasistencias']){            
            $inasistencias = $datos['inasistencias'];
        }
                
        $idEmpresa = \Session::get('empresa')->id;
        $empresa = Empresa::find($idEmpresa);
        $comunaEmpresa = $empresa->comuna->comuna;
        $fechaPalabras = Funciones::obtenerFechaTexto();
        $nombreTrabajador = $empleado->nombreCompleto();
        $rutTrabajador = $trabajador->rut_formato();
        $direccionTrabajador = $empleado->direccion;
        $comunaTrabajador =$empleado->comuna->comuna;
        $ciudadTrabajador = $empleado->comuna->provincia->provincia;
        $faltas = "";
        $faltasLineal = "";
        $nombreEmpresa = $empresa->razon_social;
        $rutEmpresa = $empresa->rut_formato();
        
        if($datos['inasistencias']){  
            foreach($inasistencias as $inasistencia){
                $faltas = $faltas . "\n" . Funciones::obtenerFechasTexto($inasistencia);
                $faltasLineal = Funciones::obtenerFechasTextoLineal($inasistencia);
            }
        }
        
        $carta = $this->reemplazarCampos($trabajador, $empleado, $empresa, $carta, $faltas, $faltasLineal);
        $datosTrabajador = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $empleado->nombreCompleto(),
            'sexo' => $empleado->sexo,
            'direccion' => $empleado->direccion,
            'comuna' => array(
                'id' => $empleado->comuna->id,
                'nombre' => $empleado->comuna->localidad(),
                'comuna' => $empleado->comuna->comuna,
                'provincia' => $empleado->comuna->provincia->provincia
            ), 
            'telefono' => $empleado->telefono,
            'celular' => $empleado->celular,
            'celularEmpresa' => $empleado->celular_empresa,
            'email' => $empleado->email,
            'emailEmpresa' => $empleado->email_empresa,
            'cargo' => array(
                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
            ),
            'seccion' => array(
                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : ""
            ),
            'fechaIngreso' => $empleado->fecha_ingreso

        );
        
        $datos = array(
            'datos' => $carta,
            'trabajador' => $datosTrabajador
        );
        
        return Response::json($datos);
    }
    
    public function contrato()
    {
        $datos = Input::all();
        $sidTrabajador = $datos['sidTrabajador'];
        $sidPlantilla = $datos['sidPlantilla'];
        $clausulas = $datos['clausulas'];
        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        $empleado = $trabajador->ficha();
        $idEmpresa = \Session::get('empresa')->id;
        $empresa = Empresa::find($idEmpresa);
        
        $contrato = $this->reemplazar($trabajador, $empleado, $empresa, $clausulas, $sidPlantilla);
        
        $datosRepresentante = array(
            'rut' => $empresa->representante_rut,
            'nombreCompleto' => $empresa->representante_nombre,
            'domicilio' => $empresa->representante_direccion . ', comuna de ' . $empresa->comunaRepresentante->comuna . ', de la ciudad de ' . $empresa->comunaRepresentante->provincia->provincia
        );
        
        $datosTrabajador = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $empleado->nombreCompleto(),
            'nacionalidad' => array(
                'id' => $empleado->nacionalidad ? $empleado->nacionalidad->id : "",
                'nombre' => $empleado->nacionalidad ? $empleado->nacionalidad->glosa : ""
            ),
            'sexo' => $trabajador->sexo,
            'estadoCivil' => array(
                'id' => $empleado->estadoCivil ? $empleado->estadoCivil->id : "",
                'nombre' => $empleado->estadoCivil ? $empleado->estadoCivil->nombre : ""
            ),
            'fechaNacimiento' => $empleado->fecha_nacimiento,
            'direccion' => $empleado->direccion,
            'domicilio' => $empleado->domicilio(),
            'comuna' => array(
                'id' => $empleado->comuna ? $empleado->comuna->id : "",
                'nombre' => $empleado->comuna ? $empleado->comuna->localidad() : "",
                'comuna' => $empleado->comuna ? $empleado->comuna->comuna : "",
                'provincia' => $empleado->comuna ? $empleado->comuna->provincia->provincia : ""
            ), 
            'telefono' => $empleado->telefono,
            'celular' => $empleado->celular,
            'celularEmpresa' => $empleado->celular_empresa,
            'email' => $empleado->email,
            'emailEmpresa' => $empleado->email_empresa,
            'cargo' => array(
                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
            ),
            'titulo' => array(
                'id' => $empleado->titulo ? $empleado->titulo->id : "",
                'nombre' => $empleado->titulo ? $empleado->titulo->nombre : ""
            ),
            'seccion' => array(
                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : ""
            ),
            'tipoCuenta' => array(
                'id' => $empleado->tipoCuenta ? $empleado->tipoCuenta->id : "",
                'nombre' => $empleado->tipoCuenta ? $empleado->tipoCuenta->nombre : ""
            ),
            'banco' => array(
                'id' => $empleado->banco ? $empleado->banco->id : "",
                'nombre' => $empleado->banco ? $empleado->banco->nombre : ""
            ),
            'numeroCuenta' => $empleado->numero_cuenta,
            'fechaIngreso' => $empleado->fecha_ingreso,
            'fechaReconocimiento' => $empleado->fecha_reconocimiento,
            'tipoContrato' => array(
                'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
            ),
            'fechaVencimiento' => $empleado->fecha_vencimiento ? $empleado->fecha_vencimiento : "",
            'tipoJornada' => array(
                'id' => $empleado->tipoJornada ? $empleado->tipoJornada->id : "",
                'nombre' => $empleado->tipoJornada ? $empleado->tipoJornada->nombre : ""
            ),
            'semanaCorrida' => $empleado->semana_corrida ? true : false,
            'monedaSueldo' => $empleado->moneda_sueldo,
            'sueldoBase' => $empleado->sueldo_base,
            'tipoTrabajador' => $empleado->tipo_trabajador,
            'excesoRetiro' => $empleado->exceso_retiro,
            'monedaColacion' => $empleado->moneda_colacion,
            'montoColacion' => $empleado->monto_colacion,
            'monedaMovilizacion' => $empleado->moneda_movilizacion,
            'montoMovilizacion' => $empleado->monto_movilizacion,
            'monedaViatico' => $empleado->moneda_viatico,
            'montoViatico' => $empleado->monto_viatico,
            'afp' => array(
                'id' => $empleado->afp ? $empleado->afp->id : "",
                'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
            ),
            'seguroDesempleo' => $empleado->seguro_desempleo ? true : false,
            'afpSeguro' => array(
                'id' => $empleado->afpSeguro ? $empleado->afpSeguro->id : "",
                'nombre' => $empleado->afpSeguro ? $empleado->afpSeguro->glosa : ""
            ),
            'isapre' => array(
                'id' => $empleado->isapre ? $empleado->isapre->id : "",
                'nombre' => $empleado->isapre ? $empleado->isapre->glosa : ""
            ),
            'cotizacionIsapre' => $empleado->cotizacion_isapre,
            'montoIsapre' => $empleado->monto_isapre,
            'sindicato' => $empleado->sindicato ? true : false,
            'monedaSindicato' => $empleado->moneda_sindicato,
            'montoSindicato' => $empleado->monto_sindicato,
            'estado' => $empleado->estado,
            'apvs' => $trabajador->misApvs(),
            'haberes' => $trabajador->misHaberes(),
            'descuentos' => $trabajador->misDescuentos(),
            'prestamos' => $trabajador->misPrestamos(),
            'cargas' => $trabajador->misCargas()

        );
        
        $datos = array(
            'datos' => $contrato,
            'trabajador' => $datosTrabajador,
            'representante' => $datosRepresentante,
            'empresa' => array(
                'domicilio' => $empresa->domicilio()
            )
        );
        
        return Response::json($datos);
    }
    
    public function finiquito()
    {
        $datos = Input::all();
        $mes = \Session::get('mesActivo');
        $sidTrabajador = $datos['sidTrabajador'];
        $sidPlantilla = $datos['sidPlantilla'];
        $plantilla = PlantillaFiniquito::whereSid($sidPlantilla)->first();
        $idCausal = $datos['idCausal'];
        $totalFiniquito = $datos['totalFiniquito'];
        $causal = CausalFiniquito::find($idCausal);
        $fechaFiniquito = $datos['fecha'];
        $clausulas = $datos['clausulas'];
        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        $empleado = $trabajador->ficha();
        $idEmpresa = \Session::get('empresa')->id;
        $empresa = Empresa::find($idEmpresa);
        
        $detalleFiniquito = $this->detalleFiniquito($datos);
        
        $finiquito = $this->reemplazarFiniquito($trabajador, $empleado, $empresa, $clausulas, $plantilla, $causal, $fechaFiniquito, $totalFiniquito, $detalleFiniquito);
        $finiquito->cuerpo = '<html><head><style>table {width: 100%; border-collapse: collapse;} th {height: 50px;} td {padding: 8px;} tr:nth-child(even) {background-color: #f2f2f2} noClass tr {background-color: white} </style></head><body>' . $finiquito->cuerpo;
        
        $datosRepresentante = array(
            'rut' => $empresa->representante_rut,
            'nombreCompleto' => $empresa->representante_nombre,
            'domicilio' => $empresa->representante_direccion . ', comuna de ' . $empresa->comunaRepresentante->comuna . ', de la ciudad de ' . $empresa->comunaRepresentante->provincia->provincia
        );
        
        $datosTrabajador = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $empleado->nombreCompleto(),
            'nacionalidad' => array(
                'id' => $empleado->nacionalidad ? $empleado->nacionalidad->id : "",
                'nombre' => $empleado->nacionalidad ? $empleado->nacionalidad->glosa : ""
            ),
            'sexo' => $trabajador->sexo,
            'estadoCivil' => array(
                'id' => $empleado->estadoCivil ? $empleado->estadoCivil->id : "",
                'nombre' => $empleado->estadoCivil ? $empleado->estadoCivil->nombre : ""
            ),
            'fechaNacimiento' => $empleado->fecha_nacimiento,
            'direccion' => $empleado->direccion,
            'domicilio' => $empleado->domicilio(),
            'comuna' => array(
                'id' => $empleado->comuna ? $empleado->comuna->id : "",
                'nombre' => $empleado->comuna ? $empleado->comuna->localidad() : "",
                'comuna' => $empleado->comuna ? $empleado->comuna->comuna : "",
                'provincia' => $empleado->comuna ? $empleado->comuna->provincia->provincia : ""
            ), 
            'telefono' => $empleado->telefono,
            'celular' => $empleado->celular,
            'celularEmpresa' => $empleado->celular_empresa,
            'email' => $empleado->email,
            'emailEmpresa' => $empleado->email_empresa,
            'cargo' => array(
                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
            ),
            'titulo' => array(
                'id' => $empleado->titulo ? $empleado->titulo->id : "",
                'nombre' => $empleado->titulo ? $empleado->titulo->nombre : ""
            ),
            'seccion' => array(
                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : ""
            ),
            'tipoCuenta' => array(
                'id' => $empleado->tipoCuenta ? $empleado->tipoCuenta->id : "",
                'nombre' => $empleado->tipoCuenta ? $empleado->tipoCuenta->nombre : ""
            ),
            'banco' => array(
                'id' => $empleado->banco ? $empleado->banco->id : "",
                'nombre' => $empleado->banco ? $empleado->banco->nombre : ""
            ),
            'numeroCuenta' => $empleado->numero_cuenta,
            'fechaIngreso' => $empleado->fecha_ingreso,
            'fechaReconocimiento' => $empleado->fecha_reconocimiento,
            'tipoContrato' => array(
                'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
            ),
            'fechaVencimiento' => $empleado->fecha_vencimiento ? $empleado->fecha_vencimiento : "",
            'tipoJornada' => array(
                'id' => $empleado->tipoJornada ? $empleado->tipoJornada->id : "",
                'nombre' => $empleado->tipoJornada ? $empleado->tipoJornada->nombre : ""
            ),
            'semanaCorrida' => $empleado->semana_corrida ? true : false,
            'monedaSueldo' => $empleado->moneda_sueldo,
            'sueldoBase' => $empleado->sueldo_base,
            'tipoTrabajador' => $empleado->tipo_trabajador,
            'excesoRetiro' => $empleado->exceso_retiro,
            'monedaColacion' => $empleado->moneda_colacion,
            'montoColacion' => $empleado->monto_colacion,
            'monedaMovilizacion' => $empleado->moneda_movilizacion,
            'montoMovilizacion' => $empleado->monto_movilizacion,
            'monedaViatico' => $empleado->moneda_viatico,
            'montoViatico' => $empleado->monto_viatico,
            'afp' => array(
                'id' => $empleado->afp ? $empleado->afp->id : "",
                'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
            ),
            'seguroDesempleo' => $empleado->seguro_desempleo ? true : false,
            'afpSeguro' => array(
                'id' => $empleado->afpSeguro ? $empleado->afpSeguro->id : "",
                'nombre' => $empleado->afpSeguro ? $empleado->afpSeguro->glosa : ""
            ),
            'isapre' => array(
                'id' => $empleado->isapre ? $empleado->isapre->id : "",
                'nombre' => $empleado->isapre ? $empleado->isapre->glosa : ""
            ),
            'cotizacionIsapre' => $empleado->cotizacion_isapre,
            'montoIsapre' => $empleado->monto_isapre,
            'sindicato' => $empleado->sindicato ? true : false,
            'monedaSindicato' => $empleado->moneda_sindicato,
            'montoSindicato' => $empleado->monto_sindicato,
            'estado' => $empleado->estado,
            'apvs' => $trabajador->misApvs(),
            'haberes' => $trabajador->misHaberes(),
            'descuentos' => $trabajador->misDescuentos(),
            'prestamos' => $trabajador->misPrestamos(),
            'cargas' => $trabajador->misCargas()

        );
        
        $datos = array(
            'datos' => $finiquito,
            'vacaciones' => $datos['vacaciones'],
            'indemnizacion' => $datos['indemnizacion'],
            'mesAviso' => $datos['mesAviso'],
            'sueldoNormal' => $datos['sueldoNormal'],
            'sueldoVariable' => $datos['sueldoVariable'],
            'totalFiniquito' => $totalFiniquito,
            'causal' =>array(
                'id' => $causal->id,
                'sid' => $causal->sid,
                'nombre' => $causal->nombre
            ),
            'fecha' => $fechaFiniquito,
            'plantilla' => $plantilla,
            'trabajador' => $datosTrabajador,
            'representante' => $datosRepresentante,
            'empresa' => array(
                'domicilio' => $empresa->domicilio()
            ),
            'isIndicadores' => $mes->indicadores
        );
        
        return Response::json($datos);
    }
    
    public function detalleFiniquito($datos)
    {            
        $table = '<div class="mceNonEditable">';
        $table .= '<table>';
        $table .= '<thead>';
        $table .= '<tr>';
        $table .= '<th>CONCEPTO</th>';
        $table .= '<th>MONTO</th>';
        $table .= '</tr>';
        $table .= '</thead>';
        $table .= '<tbody>';
        
        if($datos['mesAviso']['mesAviso']){
            
            if($datos['sueldoVariable']){
                $mesAviso = '(promedio Renta Imponible últimos ' . $datos['mesAviso']['meses'] . ' meses)';
            }else{
                $mesAviso = '';
            }
            if($datos['detalle'][0]['imponibles']['rentaImponible']['check']){
                $table .= '<tr><td>Mes de Aviso ' . $mesAviso . '</td><td>' . Funciones::formatoPesos($datos['mesAviso']['imponibles']['suma']) . '</td></tr>';
            }
        }
        
        if($datos['noImponibles']){
            
            if($datos['sueldoVariable']){
                $noImponibles = '(promedio últimos ' . $datos['mesAviso']['meses'] . ' meses)';
            }else{
                $noImponibles = '(' . $datos['detalle'][0]['mes'] . ')';
            }
            if($datos['mesAviso']['noImponibles']['check']){
                $table .= '<tr><td>No Imponibles ' . $noImponibles . '</td><td>' . Funciones::formatoPesos($datos['mesAviso']['noImponibles']['suma']) . '</td></tr>';
            }
        }
        
        if($datos['indemnizacion']['indemnizacion']){
            $table .= '<tr><td>Indemnización Años de Servicio (' . $datos['indemnizacion']['anios'] . ' años)</td><td>' . Funciones::formatoPesos($datos['indemnizacion']['monto']) . '</td></tr>';
        }
        if($datos['vacaciones']['vacaciones']){
            $table .= '<tr><td>Feriado Proporcional (' . $datos['vacaciones']['dias'] . ' días)</td><td>' . Funciones::formatoPesos($datos['vacaciones']['monto']) . '</td></tr>';
        }
        
        if($datos['otros']){
            
            foreach($datos['otros'] as $otro){
                $table .= '<tr><td colspan="2">' . $otro['nombre'] . '</td></tr>';
                foreach($otro['detalles'] as $detalle){
                    $table .= '<tr><td>' . $detalle['nombre'] . '</td><td>' . Funciones::formatoPesos(Funciones::convertir($detalle['monto'], $detalle['moneda'])) . '</td></tr>';
                }
            }
            
            /*if($datos['sueldoVariable']){
                $noImponibles = '(promedio últimos ' . $datos['mesAviso']['meses'] . ' meses)';
            }else{
                $noImponibles = '(' . $datos['detalle'][0]['mes'] . ')';
            }
            
            $table .= '<tr><td>No Imponibles ' . $noImponibles . '</td><td>' . Funciones::formatoPesos($datos['mesAviso']['noImponibles']['suma']) . '</td></tr>';*/
        }
        if($datos['prestamos']['check']){
            $table .= '<tr><td>Préstamos</td><td>-' . Funciones::formatoPesos($datos['prestamos']['monto']) . '</td></tr>';
        }
        
        $table .= '</tbody>';
        $table .= '<tfoot>';
        $table .= '<tr>';
        $table .= '<td><b>Total: </b></td>';
        $table .= '<td><b>' . Funciones::formatoPesos($datos['totalFiniquito']) . '</b></td>';
        $table .= '</tr>';
        $table .= '</tfoot>';
        $table .= '</table>';
        $table .= '</div>';
        
        return $table;
    }
    
    public function certificado()
    {
        $datos = Input::all();
        $sidTrabajador = $datos['sidTrabajador'];
        $sidPlantilla = $datos['sidPlantilla']['sid'];
        $certificado = PlantillaCertificado::whereSid($sidPlantilla)->first();  
        $tipo = $certificado->nombre;
        $clausulas = array();
        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        $empleado = $trabajador->ficha();
        
        $idEmpresa = \Session::get('empresa')->id;
        $empresa = Empresa::find($idEmpresa);
        $comunaEmpresa = $empresa->comuna->comuna;
        $fechaPalabras = Funciones::obtenerFechaTexto();
        $nombreTrabajador = $empleado->nombreCompleto();
        $rutTrabajador = $trabajador->rut_formato();
        $direccionTrabajador = $empleado->direccion;
        $comunaTrabajador = $empleado->comuna->comuna;
        $ciudadTrabajador = $empleado->comuna->provincia->provincia;
        $faltas = "";
        $faltasLineal = "";
        $nombreEmpresa = $empresa->razon_social;
        $rutEmpresa = $empresa->rut_formato();
        
        
        $certificado = $this->reemplazarCampos($trabajador, $empleado, $empresa, $certificado, $faltas, $faltasLineal);
        $certificado->tipo = $tipo;
        
        $datosTrabajador = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $empleado->nombreCompleto(),
            'sexo' => $empleado->sexo,
            'direccion' => $empleado->direccion,
            'comuna' => array(
                'id' => $empleado->comuna->id,
                'nombre' => $empleado->comuna->localidad(),
                'comuna' => $empleado->comuna->comuna,
                'provincia' => $empleado->comuna->provincia->provincia
            ), 
            'telefono' => $empleado->telefono,
            'celular' => $empleado->celular,
            'celularEmpresa' => $empleado->celular_empresa,
            'email' => $empleado->email,
            'emailEmpresa' => $empleado->email_empresa,
            'cargo' => array(
                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
            ),
            'seccion' => array(
                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : ""
            ),
            'fechaIngreso' => $empleado->fecha_ingreso

        );
        
        $datos = array(
            'datos' => $certificado,
            'trabajador' => $datosTrabajador
        );
        
        return Response::json($datos);
    }
    
    public function reemplazar($trabajador, $ficha, $empresa, $clausulas, $sidPlantilla)
    {        
        $contrato = PlantillaContrato::whereSid($sidPlantilla)->first();        
        $textoClausulas = "";
        
        $comunaEmpresa = $empresa->comuna->comuna;
        $fechaPalabras = Funciones::obtenerFechaTexto();
        $fechaActual = Funciones::obtenerFechaActual();
        $nombreEmpresa = $empresa->razon_social;
        $rutEmpresa = $empresa->rut_formato();
        $nombreRepresentante = $empresa->representante_nombre;
        $rutRepresentante = Funciones::formatear_rut($empresa->representante_rut);
        $domicilioRepresentante = $empresa->representante_direccion . ', comuna de ' . $empresa->comunaRepresentante->comuna . ', de la ciudad de ' . $empresa->comunaRepresentante->provincia->provincia;
        $domicilioEmpresa = $empresa->domicilio();
        $nombreTrabajador = $ficha->nombreCompleto();
        $nacionalidadTrabajador = $ficha->nacionalidad->glosa;
        $rutTrabajador = $trabajador->rut_formato();
        $estadoCivilTrabajador = $ficha->estadoCivil->nombre;
        $fechaNacimientoPalabrasTrabajador = Funciones::obtenerFechaTexto($ficha->fecha_nacimiento);
        $cargoTrabajador = $ficha->cargo ? $ficha->cargo->nombre : "";
        $domicilioTrabajador = $ficha->domicilio();
        $trabajadorAfp = $ficha->afp ? $ficha->afp->glosa : "";
        $trabajadorIsapre = $ficha->isapre ? $ficha->isapre->glosa : "";
        
        $sb = $trabajador->sueldoBase();
        $sueldoBase = Funciones::formatoPesos($sb);
        $sueldoBasePalabras = Funciones::convertirPalabras($sb);
        $col = Funciones::convertir($ficha->monto_colacion, $ficha->moneda_colacion);
        $colacion = Funciones::formatoPesos($col);
        $colacionPalabras = Funciones::convertirPalabras($col);
        $mov = Funciones::convertir($ficha->monto_movilizacion, $ficha->moneda_movilizacion);
        $movilizacion = Funciones::formatoPesos($mov);
        $movilizacionPalabras = Funciones::convertirPalabras($mov);
        $via = Funciones::convertir($ficha->monto_viatico, $ficha->moneda_viatico);
        $viatico = Funciones::formatoPesos($via);
        $viaticoPalabras = Funciones::convertirPalabras($via);
        $fechaInicial = $ficha->fecha_reconocimiento;
        $fechaInicialPalabras = Funciones::obtenerFechaTexto($fechaInicial);
        $fechaTermino = $ficha->fecha_termino ? $ficha->fecha_termino : "";
        $fechaTerminoPalabras = $ficha->fecha_termino ? Funciones::obtenerFechaTexto($fechaTermino) : "";
        
        $direccionTrabajador = $ficha->direccion;
        $comunaTrabajador = $ficha->comuna->comuna;
        $ciudadTrabajador = $ficha->comuna->provincia->provincia;        
        $ciudadEmpresa = $empresa->comuna->provincia->provincia;        
        $contratoTrabajador = $ficha->tipoContrato->nombre;
        $vigenciaContrato = $ficha->vigenciaContrato();
        
        $index = 1;
        foreach($clausulas as $clausula){
            $textoClausulas =  $textoClausulas . '<br /><b>' . Funciones::obtenerOrdinalTexto($index) . "." . $clausula['nombre'] . ".</b>" . $clausula['clausula'] . "<br />";
            $index++;
        }
        
        $contrato->cuerpo = str_replace('${clausulas}', $textoClausulas, $contrato->cuerpo);
        
        $var = array('${comunaEmpresa}', '${fechaActual}', '${fechaPalabras}', '${nombreEmpresa}', '${rutEmpresa}', '${nombreRepresentante}', '${rutRepresentante}', '${domicilioRepresentante}', '${domicilioEmpresa}', '${nombreTrabajador}', '${nacionalidadTrabajador}', '${rutTrabajador}', '${estadoCivilTrabajador}', '${fechaNacimientoPalabrasTrabajador}', '${cargoTrabajador}', '${domicilioTrabajador}', '${trabajadorAfp}', '${trabajadorIsapre}', '${sueldoBase}', '${sueldoBasePalabras}', '${colacion}', '${colacionPalabras}', '${movilizacion}', '${movilizacionPalabras}','${viatico}', '${viaticoPalabras}', '${fechaInicial}', '${fechaInicialPalabras}', '${fechaTermino}', '${fechaTerminoPalabras}', '${direccionTrabajador}', '${comunaTrabajador}', '${ciudadTrabajador}', '${contratoTrabajador}', '${vigenciaContrato}', '${ciudadEmpresa}');
        
        $replace = array($comunaEmpresa, $fechaActual, $fechaPalabras, $nombreEmpresa, $rutEmpresa, $nombreRepresentante, $rutRepresentante, $domicilioRepresentante, $domicilioEmpresa, $nombreTrabajador, $nacionalidadTrabajador, $rutTrabajador, $estadoCivilTrabajador, $fechaNacimientoPalabrasTrabajador, $cargoTrabajador, $domicilioTrabajador, $trabajadorAfp, $trabajadorIsapre, $sueldoBase, $sueldoBasePalabras, $colacion, $colacionPalabras, $movilizacion, $movilizacionPalabras, $viatico, $viaticoPalabras, $fechaInicial, $fechaInicialPalabras, $fechaTermino, $fechaTerminoPalabras, $direccionTrabajador, $comunaTrabajador, $ciudadTrabajador, $contratoTrabajador, $vigenciaContrato, $ciudadEmpresa);
        
        $contrato->cuerpo = str_replace($var, $replace, $contrato->cuerpo);    
        
        return $contrato;
    }
    
    public function reemplazarFiniquito($trabajador, $ficha, $empresa, $clausulas, $plantilla, $causal, $fechaFiniquito, $totalFiniquito, $detalleFiniquito)
    {        
        $finiquito = $plantilla;     
        $textoClausulas = "";
        
        $causalFiniquito = $causal->nombre;
        $numeroArticulo = $causal->articulo;
        $numeroCodigo = $causal->codigo;
        $comunaEmpresa = $empresa->comuna->comuna;
        $fechaPalabras = Funciones::obtenerFechaTexto();
        $totalFiniquitoPalabras = Funciones::convertirPalabras($totalFiniquito);
        $totalFiniquito = Funciones::formatoPesos($totalFiniquito);
        $nombreEmpresa = $empresa->razon_social;
        $rutEmpresa = $empresa->rut_formato();
        $nombreRepresentante = $empresa->representante_nombre;
        $rutRepresentante = Funciones::formatear_rut($empresa->representante_rut);
        $domicilioRepresentante = $empresa->representante_direccion . ', comuna de ' . $empresa->comunaRepresentante->comuna . ', de la ciudad de ' . $empresa->comunaRepresentante->provincia->provincia;
        $domicilioEmpresa = $empresa->domicilio();
        $nombreTrabajador = $ficha->nombreCompleto();
        $nacionalidadTrabajador = $ficha->nacionalidad->glosa;
        $rutTrabajador = $trabajador->rut_formato();
        $estadoCivilTrabajador = $ficha->estadoCivil->nombre;
        $fechaNacimientoPalabrasTrabajador = Funciones::obtenerFechaTexto($ficha->fecha_nacimiento);
        $fechaFiniquitoPalabras = Funciones::obtenerFechaTexto($fechaFiniquito);
        $cargoTrabajador = $ficha->cargo ? $ficha->cargo->nombre : "";
        $domicilioTrabajador = $ficha->domicilio();
        $trabajadorAfp = $ficha->afp ? $ficha->afp->glosa : '' ;
        $trabajadorIsapre = $ficha->isapre ? $ficha->isapre->glosa : '';
        
        $sb = $trabajador->sueldoBase();
        $sueldoBase = Funciones::formatoPesos($sb);
        $sueldoBasePalabras = Funciones::convertirPalabras($sb);
        $col = Funciones::convertir($ficha->monto_colacion, $ficha->moneda_colacion);
        $colacion = Funciones::formatoPesos($col);
        $colacionPalabras = Funciones::convertirPalabras($col);
        $mov = Funciones::convertir($ficha->monto_movilizacion, $ficha->moneda_movilizacion);
        $movilizacion = Funciones::formatoPesos($mov);
        $movilizacionPalabras = Funciones::convertirPalabras($mov);
        $via = Funciones::convertir($ficha->monto_viatico, $ficha->moneda_viatico);
        $viatico = Funciones::formatoPesos($via);
        $viaticoPalabras = Funciones::convertirPalabras($via);
        $fechaInicial = $ficha->fecha_reconocimiento;
        $fechaInicialPalabras = Funciones::obtenerFechaTexto($fechaInicial);
        $fechaTermino = $ficha->fecha_termino ? $ficha->fecha_termino : "";
        $fechaTerminoPalabras = $ficha->fecha_termino ? Funciones::obtenerFechaTexto($fechaTermino) : "";
        
        $direccionTrabajador = $ficha->direccion;
        $comunaTrabajador = $ficha->comuna->comuna;
        $ciudadTrabajador = $ficha->comuna->provincia->provincia;        
        $ciudadEmpresa = $empresa->comuna->provincia->provincia;        
        $contratoTrabajador = $ficha->tipoContrato->nombre;
        
        $index = 1;
        foreach($clausulas as $clausula){
            $textoClausulas =  $textoClausulas . '<br /><b>' . Funciones::obtenerOrdinalTexto($index) . "." . $clausula['nombre'] . ".</b>" . $clausula['clausula'] . "<br />";
            $index++;
        }
        
        $finiquito->cuerpo = str_replace('${clausulas}', $textoClausulas, $finiquito->cuerpo);        
        $var = array('${comunaEmpresa}', '${fechaPalabras}', '${nombreEmpresa}', '${rutEmpresa}', '${nombreRepresentante}', '${rutRepresentante}', '${domicilioRepresentante}', '${domicilioEmpresa}', '${nombreTrabajador}', '${nacionalidadTrabajador}', '${rutTrabajador}', '${estadoCivilTrabajador}', '${fechaNacimientoPalabrasTrabajador}', '${cargoTrabajador}', '${domicilioTrabajador}', '${trabajadorAfp}', '${trabajadorIsapre}', '${sueldoBase}', '${sueldoBasePalabras}', '${colacion}', '${colacionPalabras}', '${movilizacion}', '${movilizacionPalabras}','${viatico}', '${viaticoPalabras}', '${fechaInicial}', '${fechaInicialPalabras}', '${fechaTermino}', '${fechaTerminoPalabras}', '${direccionTrabajador}', '${comunaTrabajador}', '${ciudadTrabajador}', '${contratoTrabajador}', '${ciudadEmpresa}', '${fechaFiniquito}', '${fechaFiniquitoPalabras}', '${causalFiniquito}','${numeroArticulo}', '${numeroCodigo}', '${totalFiniquito}', '${totalFiniquitoPalabras}', '${detalleFiniquito}');
        
        $replace = array($comunaEmpresa, $fechaPalabras, $nombreEmpresa, $rutEmpresa, $nombreRepresentante, $rutRepresentante, $domicilioRepresentante, $domicilioEmpresa, $nombreTrabajador, $nacionalidadTrabajador, $rutTrabajador, $estadoCivilTrabajador, $fechaNacimientoPalabrasTrabajador, $cargoTrabajador, $domicilioTrabajador, $trabajadorAfp, $trabajadorIsapre, $sueldoBase, $sueldoBasePalabras, $colacion, $colacionPalabras, $movilizacion, $movilizacionPalabras, $viatico, $viaticoPalabras, $fechaInicial, $fechaInicialPalabras, $fechaTermino, $fechaTerminoPalabras, $direccionTrabajador, $comunaTrabajador, $ciudadTrabajador, $contratoTrabajador, $ciudadEmpresa, $fechaFiniquito, $fechaFiniquitoPalabras, $causalFiniquito, $numeroArticulo, $numeroCodigo, $totalFiniquito, $totalFiniquitoPalabras, $detalleFiniquito);
        
        $finiquito->cuerpo = str_replace($var, $replace, $finiquito->cuerpo);    
        
        return $finiquito;
    }
    
    public function reemplazarCampos($trabajador, $ficha, $empresa, $documento, $faltas, $faltasLineal)
    {                              
        $comunaEmpresa = $empresa->comuna->comuna;
        $fechaPalabras = Funciones::obtenerFechaTexto();
        $nombreEmpresa = $empresa->razon_social;
        $rutEmpresa = $empresa->rut_formato();
        $nombreRepresentante = $empresa->representante_nombre;
        $rutRepresentante = Funciones::formatear_rut($empresa->representante_rut);
        $domicilioRepresentante = $empresa->representante_direccion . ', comuna de ' . $empresa->comunaRepresentante->comuna . ', de la ciudad de ' . $empresa->comunaRepresentante->provincia->provincia;
        $domicilioEmpresa = $empresa->domicilio();
        $nombreTrabajador = $ficha->nombreCompleto();
        $nacionalidadTrabajador = $ficha->nacionalidad->glosa;
        $rutTrabajador = $trabajador->rut_formato();
        $estadoCivilTrabajador = $ficha->estadoCivil->nombre;
        $fechaNacimientoPalabrasTrabajador = Funciones::obtenerFechaTexto($ficha->fecha_nacimiento);
        $cargoTrabajador = $ficha->cargo ? $ficha->cargo->nombre : "";
        $domicilioTrabajador = $ficha->domicilio();
        $trabajadorAfp = $ficha->afp->glosa;
        $trabajadorIsapre = $ficha->isapre->glosa;
        
        $sb = $trabajador->sueldoBase();
        $sueldoBase = Funciones::formatoPesos($sb);
        $sueldoBasePalabras = Funciones::convertirPalabras($sb);
        $col = Funciones::convertir($ficha->monto_colacion, $ficha->moneda_colacion);
        $colacion = Funciones::formatoPesos($col);
        $colacionPalabras = Funciones::convertirPalabras($col);
        $mov = Funciones::convertir($ficha->monto_movilizacion, $ficha->moneda_movilizacion);
        $movilizacion = Funciones::formatoPesos($mov);
        $movilizacionPalabras = Funciones::convertirPalabras($mov);
        $via = Funciones::convertir($ficha->monto_viatico, $ficha->moneda_viatico);
        $viatico = Funciones::formatoPesos($via);
        $viaticoPalabras = Funciones::convertirPalabras($via);
        $fechaInicial = $ficha->fecha_reconocimiento;
        $fechaInicialPalabras = Funciones::obtenerFechaTexto($fechaInicial);
        $fechaTermino = $ficha->fecha_termino ? $ficha->fecha_termino : "";
        $fechaTerminoPalabras = $ficha->fecha_termino ? Funciones::obtenerFechaTexto($fechaTermino) : "";
        $direccionTrabajador = $ficha->direccion;
        $comunaTrabajador = $ficha->comuna->comuna;
        $ciudadTrabajador = $ficha->comuna->provincia->provincia;
        $ciudadEmpresa = $empresa->comuna->provincia->provincia;
        $contratoTrabajador = $ficha->tipoContrato->nombre;
                
        $var = array('${comunaEmpresa}', '${fechaPalabras}', '${nombreEmpresa}', '${rutEmpresa}', '${nombreRepresentante}', '${rutRepresentante}', '${domicilioRepresentante}', '${domicilioEmpresa}', '${nombreTrabajador}', '${nacionalidadTrabajador}', '${rutTrabajador}', '${estadoCivilTrabajador}', '${fechaNacimientoPalabrasTrabajador}', '${cargoTrabajador}', '${domicilioTrabajador}', '${trabajadorAfp}', '${trabajadorIsapre}', '${sueldoBase}', '${sueldoBasePalabras}', '${colacion}', '${colacionPalabras}', '${movilizacion}', '${movilizacionPalabras}','${viatico}', '${viaticoPalabras}', '${fechaInicial}', '${fechaInicialPalabras}', '${fechaTermino}', '${fechaTerminoPalabras}', '${direccionTrabajador}', '${comunaTrabajador}', '${ciudadTrabajador}', '${faltas}', '${faltasLineal}', '${contratoTrabajador}', '${ciudadEmpresa}');
        
        $replace = array($comunaEmpresa, $fechaPalabras, $nombreEmpresa, $rutEmpresa, $nombreRepresentante, $rutRepresentante, $domicilioRepresentante, $domicilioEmpresa, $nombreTrabajador, $nacionalidadTrabajador, $rutTrabajador, $estadoCivilTrabajador, $fechaNacimientoPalabrasTrabajador, $cargoTrabajador, $domicilioTrabajador, $trabajadorAfp, $trabajadorIsapre, $sueldoBase, $sueldoBasePalabras, $colacion, $colacionPalabras, $movilizacion, $movilizacionPalabras, $viatico, $viaticoPalabras, $fechaInicial, $fechaInicialPalabras, $fechaTermino, $fechaTerminoPalabras, $direccionTrabajador, $comunaTrabajador, $ciudadTrabajador, $faltas, $faltasLineal, $contratoTrabajador, $ciudadEmpresa);
        
        $documento->cuerpo = str_replace($var, $replace, $documento->cuerpo);    
        
        return $documento;
    }
    
    public function reajuste()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array(), 'rmi' => null));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#reajuste-global');
        $mesActual = \Session::get('mesActivo');
        $finMes = $mesActual->fechaRemuneracion;
        $mes = $mesActual->mes;
        
        $rmi = RentaMinimaImponible::where('mes', $mes)->where('nombre', 'Trab. Dependientes e Independientes')->first()->valor;
        $trabajadores = Trabajador::all();
        $listaTrabajadores=array();
        
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes && $empleado->tipo_sueldo=='Mensual' && Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo)<$rmi){
                        $listaTrabajadores[]=array(
                            'id' => $empleado->id,
                            'sid' => $trabajador->sid,
                            'rut' => $trabajador->rut,
                            'rutFormato' => $trabajador->rut_formato(),
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),      
                            'nombreCompleto' => $empleado->nombreCompleto(),      
                            'celular' => $empleado->celular,
                            'email' => $empleado->email,
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                            ),                    
                            'fechaIngreso' => $empleado->fecha_ingreso,
                            'monedaSueldo' => $empleado->moneda_sueldo,
                            'sueldoBase' => $empleado->sueldo_base,
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ), 
                            'afp' => array(
                                'id' => $empleado->afp ? $empleado->afp->id : "",
                                'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
                            ),
                            'estado' => $empleado->estado
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
                
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores,
            'rmi' => $rmi
        );
        
        return Response::json($datos);
    }
    
    public function reajustarRMI()
    {
        $datos = Input::all();
        $mesActual = \Session::get('mesActivo');
        $mes = $mesActual->mes;  
        $idMes = $mesActual->id;  
        
        $rmi = RentaMinimaImponible::where('mes', $mes)->where('nombre', 'Trab. Dependientes e Independientes')->first()->valor;
        
        foreach($datos['trabajadores'] as $trab){
            $ficha = FichaTrabajador::find($trab['id']);
            if($ficha->mes_id!=$idMes){
                $id = (FichaTrabajador::orderBy('id', 'DESC')->first()->id + 1);
                $nuevaFicha = new FichaTrabajador();
                $nuevaFicha = $ficha->replicate();
                $nuevaFicha->id = $id;
                $nuevaFicha->mes_id = $idMes;
                $nuevaFicha->fecha = $mes;
                $nuevaFicha->sueldo_base = $rmi;
                $nuevaFicha->save(); 
                $trabajador = $nuevaFicha->trabajador;
                $id = $nuevaFicha->id;
                $nombre = $nuevaFicha->nombreCompleto();
            }else{
                $ficha->sueldo_base = $rmi;
                $ficha->save(); 
                $trabajador = $ficha->trabajador;
                $id = $ficha->id;
                $nombre = $ficha->nombreCompleto();
            }                
            
            Logs::crearLog('#reajuste-global', $trabajador->id, $trabajador->rut_formato(), 'Reajuste', $id, $nombre, NULL);
            
        }
        
        $respuesta=array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente"
        );
        
        return Response::json($respuesta);
    }    
    
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
        $trabajador = new Trabajador();
        $errores = Trabajador::errores($datos);  
        $idMes = \Session::get('mesActivo')->id;  
        
        if(!$errores){
            $fecha = $trabajador->fechaFicha($datos['fecha_ingreso']);
            $trabajador->sid = Funciones::generarSID();
            $trabajador->rut = $datos['rut'];            
            $trabajador->save();
            
            $ficha = new FichaTrabajador();
            $ficha->trabajador_id = $trabajador->id;
            $ficha->mes_id = $idMes;            
            $ficha->nombres = $datos['nombres'];
            $ficha->apellidos = $datos['apellidos'];
            $ficha->nacionalidad_id = $datos['nacionalidad_id'];
            $ficha->sexo = $datos['sexo'];
            $ficha->gratificacion = $datos['gratificacion'];
            $ficha->gratificacion_especial = $datos['gratificacion_especial'];
            $ficha->moneda_gratificacion = $datos['moneda_gratificacion'];
            $ficha->monto_gratificacion = $datos['monto_gratificacion'];
            $ficha->gratificacion_proporcional_inasistencias =  $datos['gratificacion_proporcional_inasistencias'];
            $ficha->gratificacion_proporcional_licencias =  $datos['gratificacion_proporcional_licencias'];
            $ficha->estado_civil_id = $datos['estado_civil_id'];
            $ficha->fecha_nacimiento = $datos['fecha_nacimiento'];
            $ficha->direccion = $datos['direccion'];
            $ficha->comuna_id = $datos['comuna_id'];
            $ficha->telefono = $datos['telefono'];
            $ficha->celular = $datos['celular'];
            $ficha->celular_empresa = $datos['celular_empresa'];
            $ficha->email = $datos['email'];
            $ficha->email_empresa = $datos['email_empresa'];
            $ficha->tipo_id = $datos['tipo_id'];
            $ficha->cargo_id = $datos['cargo_id'];
            $ficha->titulo_id = $datos['titulo_id'];
            $ficha->seccion_id = $datos['seccion_id'];
            $ficha->tienda_id = $datos['tienda_id'];
            $ficha->centro_costo_id = $datos['centro_costo_id'];
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
            $ficha->tipo_semana = $datos['tipo_semana'];
            $ficha->tipo_sueldo = $datos['tipo_sueldo'];
            $ficha->horas = $datos['horas'];
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
            $ficha->zona_id = $datos['zona_id'];
            $ficha->estado = $datos['estado'];
            $ficha->fecha = $fecha;
            
            if($ficha->estado=='Ingresado'){
                $ficha->tramo_id = FichaTrabajador::calcularTramo(Funciones::convertir($datos['sueldo_base'], $datos['moneda_sueldo']));
                $trabajador->crearUser();
                if($ficha->semana_corrida==1){
                    $trabajador->crearSemanaCorrida();
                }
            }
            
            $ficha->save();
            
            if($ficha->fecha_reconocimiento){
                $trabajador->calcularMisVacaciones($ficha->fecha_reconocimiento);
            }
            
            Logs::crearLog('#trabajadores', $trabajador->id, $trabajador->rut_formato(), 'Create', $ficha->id, $ficha->nombreCompleto(), 'Trabajadores'); 
            
            $respuesta=array(
                'success' => true,
                'mensaje' => "La Información fue almacenada correctamente",
                'sid' => $trabajador->sid
            );
                    
            if($datos['descuentos']){    
                
                $descuentos = $datos['descuentos'];
                foreach($descuentos as $descuento){
                    $tipo = $descuento['tipo'];
                    $errores = Descuento::errores($descuento);
                    if(!$errores){
                        $nuevoDescuento = new Descuento();
                        $nuevoDescuento->sid = Funciones::generarSID();
                        $nuevoDescuento->trabajador_id = $trabajador->id;
                        $nuevoDescuento->tipo_descuento_id = $tipo['id'];
                        $nuevoDescuento->mes_id = null;
                        $nuevoDescuento->por_mes = 0;
                        $nuevoDescuento->rango_meses = 0;
                        $nuevoDescuento->permanente = 1;
                        $nuevoDescuento->todos_anios = 0;
                        $nuevoDescuento->mes = null;
                        $nuevoDescuento->desde = null;
                        $nuevoDescuento->hasta = null;
                        $nuevoDescuento->moneda = $descuento['moneda'];
                        $nuevoDescuento->monto = $descuento['monto'];
                        $nuevoDescuento->save();                                                
                    }else{
                        $respuesta=array(
                            'success' => false,
                            'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                            'errores' => $errores
                        );
                    }
                }
            }
            if($datos['haberes']){    
                
                $haberes = $datos['haberes'];
                foreach($haberes as $haber){
                    $tipo = $haber['tipo'];
                    $errores = Haber::errores($haber);
                    if(!$errores){
                        $nuevoHaber = new Haber();
                        $nuevoHaber->sid = Funciones::generarSID();
                        $nuevoHaber->trabajador_id = $trabajador->id;
                        $nuevoHaber->tipo_haber_id = $tipo['id'];
                        $nuevoHaber->mes_id = null;
                        $nuevoHaber->por_mes = 0;
                        $nuevoHaber->rango_meses = 0;
                        $nuevoHaber->permanente = 1;
                        $nuevoHaber->todos_anios = 0;
                        $nuevoHaber->mes = null;
                        $nuevoHaber->desde = null;
                        $nuevoHaber->hasta = null;
                        $nuevoHaber->moneda = $haber['moneda'];
                        $nuevoHaber->monto = $haber['monto'];
                        $nuevoHaber->proporcional = $haber['proporcional'] ? $haber['proporcional'] : 0;
                        $nuevoHaber->save();                                                
                    }else{
                        $respuesta=array(
                            'success' => false,
                            'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                            'errores' => $errores
                        );
                    }
                }
            }
            
        }else{
            $respuesta=array(
                'success' => false,
                'mensaje' => "La acción no pudo ser completada debido a errores en la información ingresada",
                'errores' => $errores
            );
        }

        return Response::json($respuesta);
    }    
    
    public function listaAfps()
    {        
        $datos=array(
            'datos' => Glosa::listaAfps()
        );
        
        return Response::json($datos);
    }
    
    public function seccionesFormulario()
    {
        $listaSecciones=array();
        $listaCentrosCosto=array();
        Seccion::listaSecciones($listaSecciones, 0, 1);
        CentroCosto::arbolCentrosCosto($listaCentrosCosto, 0, 1);
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;    
        $trabajadores = Trabajador::all();        
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
                        
        $listaTrabajadores=array();
        if( $trabajadores->count() ){
            foreach( $trabajadores as $trabajador ){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mesAnterior){
                        $listaTrabajadores[]=array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rutFormato' => $trabajador->rut_formato(),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'apellidos' => $empleado->apellidos,
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'sid' => $empleado->seccion ? $empleado->seccion->sid : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : ""
                            ),
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'sid' => $empleado->centroCosto ? $empleado->centroCosto->sid : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : ""
                            )
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
		$datos=array(
			'secciones' => $listaSecciones,
			'centrosCosto' => $listaCentrosCosto,
			'trabajadores' => $listaTrabajadores
		);
        
		return Response::json($datos);
	}
    
    public function trabajadoresInasistencias()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-inasistencias');
        $trabajadores = Trabajador::all();
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mesAnterior){
                        $totalInasistencias = $trabajador->totalInasistencias();
                        if($totalInasistencias){
                            $listaTrabajadores[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                                ),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'totalInasistencias' => $totalInasistencias
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadorInasistencias($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-inasistencias');        
        
        $trabajadorInasistencias = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
            'inasistencias' => $trabajador->misInasistencias()
        );
        $datos = array(
            'accesos' => $permisos,            
            'datos' => $trabajadorInasistencias
        );
        return Response::json($datos);     
    }
    
    public function trabajadoresAtrasos()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#atrasos');
        $trabajadores = Trabajador::all();
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mesAnterior){
                        $atrasos = $trabajador->totalAtrasos();
                        if($atrasos['atrasos']){
                            $listaTrabajadores[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                                ),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'total' => $atrasos['total'],
                                'atrasos' => $atrasos['atrasos'],
                                'jornada' => $trabajador->horasJornada()
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadorAtrasos($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#atrasos');
        
        $trabajadorInasistencias = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
            'atrasos' => $trabajador->misAtrasos(),
            'jornada' => $trabajador->horasJornada()
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorInasistencias
        );
        return Response::json($datos);     
    }
    
    public function trabajadoresLicencias()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-licencias');
        $trabajadores = Trabajador::all();
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mesAnterior){
                        $licencias = $trabajador->totalLicencias();
                        if($licencias){
                            $listaTrabajadores[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'totalLicencias' => $licencias,
                                'totalDiasLicencias' => $trabajador->totalDiasLicencias()
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadorLicencias($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-licencias');
        
        $trabajadorLicencias = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
            'licencias' => $trabajador->misLicencias()
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorLicencias
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadoresHorasExtra()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-horas-extra');
        $mes = \Session::get('mesActivo');
        $finMes = $mes->fechaRemuneracion;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes->mes)));
        $finMesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($finMes)));
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito <= $finMes && $empleado->fecha_finiquito >= $mesAnterior){
                        $horasExtra = $trabajador->totalHorasExtra();
                        if($horasExtra){
                            $listaTrabajadores[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                                'jornadaOrden' => $empleado->tipoJornada ? ucwords(strtolower($empleado->tipoJornada->nombre)) : "",
                                'jornada' => array(
                                    'id' => $empleado->tipoJornada ? $empleado->tipoJornada->id : "",
                                    'nombre' => $empleado->tipoJornada ? $empleado->tipoJornada->nombre : "",
                                    'horas' => $empleado->tipoJornada ? $empleado->tipoJornada->numero_horas : ""
                                ),
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                                ),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'tramos' => $trabajador->tramosHorasExtra(),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'totalHorasExtra' => $horasExtra
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);     
    }    
    
    public function trabajadorHorasExtra($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-horas-extra');
        $tipos = TipoHoraExtra::listaTiposHoraExtra();
        
        $trabajadorHorasExtra = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => Funciones::formatear_rut($trabajador->rut),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $empleado->nombreCompleto(),
            'tramos' => $trabajador->tramosHorasExtra(),
            'tipos' => $tipos,
            'horasExtra' => $trabajador->misHorasExtra()
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorHorasExtra
        );
        
        return Response::json($datos);     
    }            
    
    public function trabajadoresPrestamos()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-prestamos');
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;     
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $totalPrestamos = $trabajador->totalPrestamos();
                        if($totalPrestamos){
                            $listaTrabajadores[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'totalPrestamos' => $totalPrestamos
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadorPrestamos($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-prestamos');
        
        $trabajadorPrestamos = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
            'prestamos' => $trabajador->prestamos
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorPrestamos
        );
        
        return Response::json($datos);       
    }
    
    public function trabajadoresApvs()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#apvs');
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;     
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $misApvs = $trabajador->misApvs();
                        if(count($misApvs)){
                            $listaTrabajadores[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ), 
                                'apvs' => $misApvs,
                                'regimen' => $trabajador->misRegimenes(),
                                'total' => $trabajador->totalApv()
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadorApvs($sid)
    {        
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#apvs');
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();        
        
        $trabajadorApvs = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $empleado->nombreCompleto(),
            'apvs' => $trabajador->misApvs()
            
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorApvs
        );
        
        return Response::json($datos);       
    }
    
    public function trabajadoresCargas()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('datos' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cargas-familiares');
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;     
        $trabajadores = Trabajador::all();
        $tiposCargas = TipoCarga::listaTiposCarga();
        
        $listaTrabajadores = array();
        if($trabajadores->count()){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $totalGrupoFamiliar = $trabajador->totalGrupoFamiliar();
                        if($totalGrupoFamiliar){
                            $listaTrabajadores[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'tramo' => strtoupper($empleado->tramo_id),
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'cargasFamiliares' => $trabajador->totalCargasFamiliares(),
                                'grupoFamiliar' => $totalGrupoFamiliar,
                                'cargasAutorizadas' => $trabajador->totalCargasAutorizadas(),
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "",
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                )
                            );
                        }
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaTrabajadores,
            'tiposCargas' => $tiposCargas
        );
        
        return Response::json($datos);     
    }
    
    public function trabajadorCargas($sid)
    {        
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cargas-familiares');
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();
        $tiposCargas = TipoCarga::listaTiposCarga();
        $listaTramos = AsignacionFamiliar::listaAsignacionFamiliar();
        
        $trabajadorCargas = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'tramo' => $empleado->tramo_id,
            'esCargas' => $trabajador->isCargas(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'cargas' => $trabajador->miGrupoFamiliar(),
            'grupo' => $trabajador->miGrupo()
            
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorCargas,
            'tramos' => $listaTramos,
            'tiposCargas' => $tiposCargas
        );
        
        return Response::json($datos);       
    }
    
    public function cambiarTramo()
    {        
        $datos = Input::all();
        $trabajador = Trabajador::find($datos['idTrabajador']);
        $empleado = $trabajador->ficha();
        $empleado->tramo_id = $datos['tramo']['tramo'];
        $empleado->save();       
        
        Logs::crearLog('#cargas-familiares', $trabajador->id, $trabajador->rut_formato(), 'Update', $empleado->id, $empleado->nombreCompleto(), 'Cambiar Tramo');
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $trabajador->sid
        );
        
        return Response::json($respuesta);       
    }
    
    public function trabajadorCargasAutorizar($sid)
    {        
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#cargas-familiares');
        $trabajador = Trabajador::whereSid($sid)->first();
        $listaTramos = AsignacionFamiliar::listaAsignacionFamiliar();
        $empleado = $trabajador->ficha();
        
        $trabajadorCargas = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $empleado->nombreCompleto(),
            'cargas' => $trabajador->misCargas(),
            'tramo' => $empleado->tramo_id
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorCargas,
            'tramos' => $listaTramos
        );
        
        return Response::json($datos);       
    }
    
    public function trabajadorAutorizarCargas()
    {                
        $datos = Input::all();
        $sidTrabajador = $datos['sidTrabajador'];
        $trabajador = Trabajador::whereSid($sidTrabajador)->first();
        $cargas = $datos['cargas'];
        $idTramo = $datos['tramo'];
        $empleado = $trabajador->ficha();
        
        $empleado->tramo_id = $idTramo; 
        $empleado->save();
        
        if($cargas){
            foreach($cargas as $carga){
                $cargaFamiliar = Carga::whereSid($carga['sid'])->first();
                $cargaFamiliar->es_autorizada = 1;
                $cargaFamiliar->fecha_autorizacion = $carga['fecha'];
                $cargaFamiliar->save();
            }    
        }
        
        $respuesta = array(
            'success' => true,
            'mensaje' => "La Información fue actualizada correctamente",
            'sid' => $trabajador->sid
        );
        
        return Response::json($respuesta);       
    }
    
    public function haberes($sid)
    {
        $trabajador = Trabajador::whereSid($sid)->first();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-haberes');
        
        $trabajadorHaberes = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
            'haberes' => $trabajador->todosMisHaberes()
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorHaberes
        );
        
        return Response::json($datos);     
    }
    
    public function descuentos($sid)
    {
        $trabajador = Trabajador::whereSid($sid)->first();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#ingreso-descuentos');
        
        $trabajadorDescuentos = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'rut' => $trabajador->rut,
            'nombreCompleto' => $trabajador->ficha()->nombreCompleto(),
            'descuentos' => $trabajador->todosMisDescuentos()
        );
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorDescuentos
        );
        
        return Response::json($datos);     
    }    
    
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($sid)
    {
        $mes = \Session::get('mesActivo');
        $listaSecciones=array();
        Seccion::listaSecciones($listaSecciones, 0, 1);
        $listaCentrosCosto=array();
        CentroCosto::listaCentrosCostoDependencia($listaCentrosCosto, 0, 1, 0);
        $datosTrabajador = array();
        
		$datosFormulario=array(
			'nacionalidades' => Glosa::listaNacionalidades(),
			'estadosCiviles' => EstadoCivil::listaEstadosCiviles(),
			'cargos' => Cargo::listaCargos(),
			'tiendas' => Tienda::listaTiendas(),
			'centros' => $listaCentrosCosto,
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
			'formasPago' => Glosa::listaFormasPago(),
			'isapres' => Glosa::listaIsapres(),
			'tiposDescuento' => TipoDescuento::listaTiposDescuento(),
			'tiposHaber' => TipoHaber::listaTiposHaber(),
            'rmi' => RentaMinimaImponible::rmi(),
            'rti' => RentaTopeImponible::rti(),
            'tasasSeguroCesantia' => SeguroDeCesantia::listaSeguroDeCesantia(),
            'rentasTopesImponibles' => RentaTopeImponible::listaRentasTopeImponibles(),
            'tablaImpuestoUnico' => TablaImpuestoUnico::tabla(),
            'zonas' => ZonaImpuestoUnico::listaZonas()
		);
        
        if($sid){
            $trabajador = Trabajador::whereSid($sid)->first();
            $empleado = $trabajador->ficha();
            $sueldoBase = $empleado->sueldo_base;
            if($empleado->moneda_sueldo=='$'){
                $sueldoBase = (int) $sueldoBase;
            }
            $gratificacion = $empleado->monto_gratificacion;
            if($empleado->moneda_gratificacion=='$'){
                $gratificacion = (int) $gratificacion;
            }
            $montoIsapre = $empleado->monto_isapre;
            if($empleado->cotizacion_isapre=='$'){
                $montoIsapre = (int) $montoIsapre;
            }

            $datosTrabajador = array(
                'id' => $trabajador->id,
                'idFicha' => $empleado->id,
                'sid' => $trabajador->sid,
                'rutFormato' => $trabajador->rut_formato(),
                'rut' => $trabajador->rut,
                'nombres' => $empleado->nombres,
                'apellidos' => $empleado->apellidos,
                'nombreCompleto' => $empleado->nombreCompleto(),
                'nacionalidad' => array(
                    'id' => $empleado->nacionalidad ? $empleado->nacionalidad->id : "",
                    'nombre' => $empleado->nacionalidad ? $empleado->nacionalidad->glosa : ""
                ),
                'sexo' => $empleado->sexo,
                'estadoCivil' => array(
                    'id' => $empleado->estadoCivil ? $empleado->estadoCivil->id : "",
                    'nombre' => $empleado->estadoCivil ? $empleado->estadoCivil->nombre : ""
                ),
                'fechaNacimiento' => $empleado->fecha_nacimiento,
                'direccion' => $empleado->direccion,
                //'domicilio' => $trabajador->domicilio(),
                'comuna' => array(
                    'id' => $empleado->comuna ? $empleado->comuna->id : "",
                    'nombre' => $empleado->comuna ? $empleado->comuna->localidad() : "",
                    'comuna' => $empleado->comuna ? $empleado->comuna->comuna : "",
                    'provincia' => $empleado->comuna ? $empleado->comuna->provincia->provincia : ""
                ), 
                'telefono' => $empleado->telefono,
                'celular' => $empleado->celular,
                'celularEmpresa' => $empleado->celular_empresa,
                'email' => $empleado->email,
                'emailEmpresa' => $empleado->email_empresa,
                'tipo' => array(
                    'id' => $empleado->tipo_id,
                    'nombre' => $empleado->tipo ? $empleado->tipo->nombre : ""
                ),
                'cargo' => array(
                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                ),
                'titulo' => array(
                    'id' => $empleado->titulo ? $empleado->titulo->id : "",
                    'nombre' => $empleado->titulo ? $empleado->titulo->nombre : ""
                ),
                'seccion' => array(
                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : ""
                ),
                'tienda' => array(
                    'id' => $empleado->tienda ? $empleado->tienda->id : "",
                    'nombre' => $empleado->tienda ? $empleado->tienda->nombre : ""
                ),
                'centroCosto' => array(
                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : ""
                ),
                'tipoCuenta' => array(
                    'id' => $empleado->tipoCuenta ? $empleado->tipoCuenta->id : "",
                    'nombre' => $empleado->tipoCuenta ? $empleado->tipoCuenta->nombre : ""
                ),
                'banco' => array(
                    'id' => $empleado->banco ? $empleado->banco->id : "",
                    'nombre' => $empleado->banco ? $empleado->banco->nombre : ""
                ),
                'numeroCuenta' => $empleado->numero_cuenta,                
                'fechaIngreso' => $empleado->fecha_ingreso,
                'fechaReconocimiento' => $empleado->fecha_reconocimiento,
                'fechaReconocimientoCesantia' => $empleado->fecha_reconocimiento_cesantia,
                'fechaFiniquito' => $empleado->fecha_finiquito,
                'tipoContrato' => array(
                    'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                    'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
                ),
                'fechaVencimiento' => $empleado->fecha_vencimiento ? $empleado->fecha_vencimiento : "",
                'tipoJornada' => array(
                    'id' => $empleado->tipoJornada ? $empleado->tipoJornada->id : "",
                    'nombre' => $empleado->tipoJornada ? $empleado->tipoJornada->nombre : ""
                ),
                'semanaCorrida' => $empleado->semana_corrida ? true : false,
                'tipoSemana' => $empleado->tipo_semana,
                'monedaSueldo' => $empleado->moneda_sueldo,
                'gratificacion' => $empleado->gratificacion,
                'gratificacionEspecial' => $empleado->gratificacion_especial ? true : false,
                'monedaGratificacion' => $empleado->moneda_gratificacion,
                'montoGratificacion' => $gratificacion,
                'proporcionalInasistencias' => $empleado->gratificacion_proporcional_inasistencias ? true : false,
                'proporcionalLicencias' => $empleado->gratificacion_proporcional_licencias ? true : false,
                'tipoSueldo' => $empleado->tipo_sueldo,
                'horas' => $empleado->horas,
                'sueldoBase' => $sueldoBase,
                'tipoTrabajador' => $empleado->tipo_trabajador,
                'excesoRetiro' => $empleado->exceso_retiro,
                'proporcionalColacion' => $empleado->proporcional_colacion ? true : false,
                'monedaColacion' => $empleado->moneda_colacion,
                'montoColacion' => $empleado->monto_colacion,
                'proporcionalMovilizacion' => $empleado->proporcional_movilizacion ? true : false,
                'monedaMovilizacion' => $empleado->moneda_movilizacion,
                'montoMovilizacion' => $empleado->monto_movilizacion,
                'proporcionalViatico' => $empleado->proporcional_viatico ? true : false,
                'monedaViatico' => $empleado->moneda_viatico,
                'montoViatico' => $empleado->monto_viatico,
                'prevision' => array(
                    'id' => $empleado->prevision ? $empleado->prevision->id : "",
                    'nombre' => $empleado->prevision ? $empleado->prevision->glosa : ""
                ),
                'afp' => array(
                    'id' => $empleado->afp ? $empleado->afp->id : "",
                    'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
                ),
                'seguroDesempleo' => $empleado->seguro_desempleo ? true : false,
                'afpSeguro' => array(
                    'id' => $empleado->afpSeguro ? $empleado->afpSeguro->id : "",
                    'nombre' => $empleado->afpSeguro ? $empleado->afpSeguro->glosa : ""
                ),
                'isapre' => array(
                    'id' => $empleado->isapre ? $empleado->isapre->id : "",
                    'nombre' => $empleado->isapre ? $empleado->isapre->glosa : ""
                ),
                'cotizacionIsapre' => $empleado->cotizacion_isapre,
                'montoIsapre' => $montoIsapre,
                'sindicato' => $empleado->sindicato ? true : false,
                'monedaSindicato' => $empleado->moneda_sindicato,
                'montoSindicato' => $empleado->monto_sindicato,
                'estado' => $empleado->estado,
                'haberes' => $trabajador->misHaberesPermanentes(),
                'descuentos' => $trabajador->misDescuentosPermanentes(),
                'prestamos' => $trabajador->misPrestamos(),
                'zonaImpuestoUnico' => array(
                    'id' => $empleado->zonaImpuestoUnico ? $empleado->zonaImpuestoUnico->id : "",
                    'nombre' => $empleado->zonaImpuestoUnico ? $empleado->zonaImpuestoUnico->nombre : "",
                    'porcentaje' => $empleado->zonaImpuestoUnico ? $empleado->zonaImpuestoUnico->porcentaje : ""
                )
            );
        }
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'trabajador' => $datosTrabajador,
            'formulario' => $datosFormulario,
            'isIndicadores' => $mes->indicadores
        );
        
        return Response::json($datos);
    }    
    
    public function vigentes()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('activos' => array(), 'inactivos' => array(), 'permisos' => array()));
        }
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;
        $mes = \Session::get('mesActivo')->mes;
        /*$activos = Trabajador::whereFicha('estado', 'Ingresado')->orWhereFicha('estado', 'Finiquitado')->whereFicha('fecha_finiquito', '>=', $mes)->get();*/
        /*$activos = Trabajador::with('FichaTrabajador')->WhereIn('fichaTrabajador.estado', 'Ingresado')->get();*/
        /*$activos = DB::table('trabajadores')
            ->join('fichas_trabajadores', function($join)
                   {
                        $join->on('trabajadores.id', '=', 'fichas_trabajadores.trabajador_id')
                            ->where('fichas_trabajadores.estado', '=', 'Ingresado');
                    })
            ->get();*/
        
        $trabajadores = Trabajador::all();
                
        $listaActivos = array();
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $listaActivos[] = array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rut' => $trabajador->rut,
                            'rutFormato' => $trabajador->rut_formato(),
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'fechaIngreso' => $empleado->fecha_ingreso,
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                            ),
                            'contratoOrden' => $empleado->tipoContrato ? ucwords(strtolower($empleado->tipoContrato->nombre)) : "", 
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ), 
                            'tipoContrato' => array(
                                'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                                'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
                            ),
                            'monedaSueldo' => $empleado->moneda_sueldo,
                            'sueldoBase' => $empleado->sueldo_base
                        );
                    }
                }
            }
        }
        
        //$inactivos = Trabajador::where('estado', '<>', 'Ingresado')->orderBy('apellidos')->get();
        //$inactivos = FichaTrabajador::with('Trabajador')->where('mes_id', $idMes)->where('estado', '<>', 'Ingresado')->orWhere('estado', 'Finiquitado')->where('fecha_finiquito', '<=', $mes)->get();
        
        $listaInactivos = array();
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='En Creación' || $empleado->estado=='Finiquitado'){
                        $listaInactivos[] = array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rut' => $trabajador->rut,
                            'rutFormato' => $trabajador->rut_formato(),
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'fechaIngreso' => $empleado->fecha_ingreso,
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "",
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                            ),
                            'contratoOrden' => $empleado->tipoContrato ? ucwords(strtolower($empleado->tipoContrato->nombre)) : "", 
                            'tipoContrato' => array(
                                'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                                'nombre' => $empleado->tipoContrato ?$empleado->tipoContrato->nombre : ""
                            ),
                            'monedaSueldo' => $empleado->moneda_sueldo,
                            'estado' => $empleado->estado,
                            'sueldoBase' => $empleado->sueldo_base
                        );
                    }
                }
            }
        }
        
        $listaActivos = Funciones::ordenar($listaActivos, 'apellidos');
        $listaInactivos = Funciones::ordenar($listaInactivos, 'apellidos');
        
        $datos = array(
            'accesos' => array(
                'ver' => true,
                'editar' => true
            ),
            'activos' => $listaActivos,
            'inactivos' => $listaInactivos
        );
        
        return Response::json($datos); 
    }
    
    public function provisionVacaciones()
    {
        $mesActual = \Session::get('mesActivo');
        $finMes = $mesActual->fechaRemuneracion;
        $mes = $mesActual->mes;
        $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes)));
        $empresa = \Session::get('empresa');
        $trabajadores = Trabajador::all();
        
        $listaTrabajadores = array();
        
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito >= $mesAnterior && $empleado->fecha_ingreso<=$finMes){
                        $listaTrabajadores[] = array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rutFormato' => $trabajador->rut_formato(),
                            'rut' => $trabajador->rut,
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),    
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ), 
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                            ),             
                            'fechaIngreso' => date('d-m-Y', strtotime($empleado->fecha_ingreso)),
                            'provision' => $trabajador->provisionVacaciones()
                        );
                    }
                }
            }
        }
        
        $listaTrabajadores = Funciones::ordenar($listaTrabajadores, 'apellidos');
         
        Excel::create('Vacaciones', function($excel) use($listaTrabajadores, $mesActual, $empresa) {
            $excel->sheet('Vacaciones', function($sheet) use($listaTrabajadores, $mesActual, $empresa) {
                $sheet->loadView('excel.vacaciones')->with(array('datos' => $listaTrabajadores, 'mes' => $mesActual, 'empresa' => $empresa));
            });
        })->store('xls', public_path('stories'));                
        
        $datos = array(
            'datos' => $listaTrabajadores,
            'mes' => $mesActual
        );
        
        return Response::json($datos);
    }
    
    public function archivoPrevired()
    {        
        if(!\Session::get('empresa')){
            return Response::json(array('activos' => array(), 'conLiquidacion' => array(), 'permisos' => array()));
        }
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#archivo-previred');
        $mesActual = \Session::get('mesActivo');
        $finMes = $mesActual->fechaRemuneracion;
        $mes = $mesActual->mes;
        $mostrarFiniquitados = Empresa::variableConfiguracion('finiquitados_liquidacion');
        
        if($mostrarFiniquitados){
            $mesAnterior = date('Y-m-d', strtotime('-' . 1 . ' month', strtotime($mes)));
        }else{
            $mesAnterior = $mes;
        }
        $trabajadores = Trabajador::all();
        
        $listaActivos = array();
        
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes || $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito >= $mesAnterior && $empleado->fecha_ingreso<=$finMes){
                        $agregar = true;
                        if($mostrarFiniquitados && $empleado->estado=='Finiquitado' && $empleado->fecha_finiquito<=$mes){
                            if(!$trabajador->isLiquidacion()){
                                $agregar = false;
                            }
                        }
                        if($agregar){
                            $listaActivos[] = array(
                                'id' => $trabajador->id,
                                'sid' => $trabajador->sid,
                                'rutFormato' => $trabajador->rut_formato(),
                                'rut' => $trabajador->rut,
                                'nombreCompleto' => $empleado->nombreCompleto(),
                                'apellidos' => ucwords(strtolower($empleado->apellidos)),      
                                'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                                'cargo' => array(
                                    'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                    'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
                                ),             
                                'fechaIngreso' => $empleado->fecha_ingreso,
                                'contratoOrden' => $empleado->tipoContrato ? ucwords(strtolower($empleado->tipoContrato->nombre)) : "", 
                                'tipoContrato' => array(
                                    'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                                    'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
                                ),
                                'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                                'seccion' => array(
                                    'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                    'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                                ), 
                                'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                                'centroCosto' => array(
                                    'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                    'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                                ),
                                'monedaSueldo' => $empleado->moneda_sueldo,
                                'sueldoBase' => $empleado->sueldo_base,        
                                'estado' => $empleado->estado,
                                'isLiquidacion' => $trabajador->isLiquidacion()
                            );
                        }
                    }
                }
            }
        }
        
        $listaActivos = Funciones::ordenar($listaActivos, 'apellidos');
                                    
        $datos = array(
            'accesos' => $permisos,
            'datos' => $listaActivos,
            'isLiquidaciones' => AnioRemuneracion::isLiquidaciones(),
            'isIndicadores' => $mesActual->indicadores
        );
        
        return Response::json($datos); 
    }
    
    public function trabajadoresFiniquitos()
    {
        if(!\Session::get('empresa')){
            return Response::json(array('activos' => array(), 'finiquitados' => array(), 'permisos' => array()));
        }
        
        $finMes = \Session::get('mesActivo')->fechaRemuneracion;
        $mes = \Session::get('mesActivo')->mes;
        $trabajadores = Trabajador::all();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#finiquitar-trabajador');
        
        $listaActivos = array();
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Ingresado' && $empleado->fecha_ingreso<=$finMes){
                        $listaActivos[] = array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rutFormato' => $trabajador->rut_formato(),
                            'rut' => $trabajador->rut,
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'fechaIngreso' => $empleado->fecha_ingreso,
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                            ),
                            'contratoOrden' => $empleado->tipoContrato ? ucwords(strtolower($empleado->tipoContrato->nombre)) : "", 
                            'tipoContrato' => array(
                                'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                                'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
                            ),
                            'seccionOrden' => $empleado->seccion ? ucwords(strtolower($empleado->seccion->nombre)) : "", 
                            'seccion' => array(
                                'id' => $empleado->seccion ? $empleado->seccion->id : "",
                                'nombre' => $empleado->seccion ? $empleado->seccion->nombre : "",
                            ), 
                            'centroCostoOrden' => $empleado->centroCosto ? ucwords(strtolower($empleado->centroCosto->nombre)) : "", 
                            'centroCosto' => array(
                                'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                                'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : "",
                            ), 
                            'monedaSueldo' => $empleado->moneda_sueldo,
                            'sueldoBase' => $empleado->sueldo_base,
                            'mesesAntiguedad' => $empleado->mesesAntiguedad()
                        );
                    }
                }
            }
        }

        $listaInactivos = array();
        if($trabajadores){
            foreach($trabajadores as $trabajador){
                $empleado = $trabajador->ficha();
                if($empleado){
                    if($empleado->estado=='Finiquitado'){
                        $listaInactivos[] = array(
                            'id' => $trabajador->id,
                            'sid' => $trabajador->sid,
                            'rutFormato' => $trabajador->rut_formato(),
                            'rut' => $trabajador->rut,
                            'apellidos' => ucwords(strtolower($empleado->apellidos)),
                            'nombreCompleto' => $empleado->nombreCompleto(),
                            'fechaIngreso' => $empleado->fecha_ingreso,
                            'fechaFiniquito' => $empleado->fecha_finiquito,
                            'cargoOrden' => $empleado->cargo ? ucwords(strtolower($empleado->cargo->nombre)) : "", 
                            'cargo' => array(
                                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                            ),
                            'contratoOrden' => $empleado->tipoContrato ? ucwords(strtolower($empleado->tipoContrato->nombre)) : "", 
                            'tipoContrato' => array(
                                'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                                'nombre' => $empleado->tipoContrato ?$empleado->tipoContrato->nombre : ""
                            ),
                            'monedaSueldo' => $empleado->moneda_sueldo,
                            'sueldoBase' => $empleado->sueldo_base
                        );
                    }
                }
            }
        }
        
        $listaActivos = Funciones::ordenar($listaActivos, 'apellidos');
        $listaInactivos = Funciones::ordenar($listaInactivos, 'apellidos');
        
        $datos = array(
            'accesos' => $permisos,
            'activos' => $listaActivos,
            'finiquitados' => $listaInactivos
        );
        
        return Response::json($datos); 
    }
    
    public function trabajadorFiniquitos($sid)
    {        
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();
        $permisos = MenuSistema::obtenerPermisosAccesosURL(Auth::usuario()->user(), '#finiquitar-trabajador');
        
        $trabajadorFiniquitos = array(
            'id' => $trabajador->id,
            'sid' => $trabajador->sid,
            'rutFormato' => $trabajador->rut_formato(),
            'nombreCompleto' => $empleado->nombreCompleto(),
            'celular' => $empleado->celular,
            'email' => $empleado->email,
            'cargo' => array(
                'id' => $empleado->cargo ? $empleado->cargo->id : "",
                'nombre' => $empleado->cargo ? $empleado->cargo->nombre : "",
            ),                     
            'fechaIngreso' => $empleado->fecha_ingreso,
            'monedaSueldo' => $empleado->moneda_sueldo,
            'sueldoBase' => $empleado->sueldo_base,
            'sueldoBasePesos' => Funciones::convertir($empleado->sueldo_base, $empleado->moneda_sueldo),
            'afp' => array(
                'id' => $empleado->afp ? $empleado->afp->id : "",
                'nombre' => $empleado->afp ? $empleado->afp->glosa : ""
            ),
            'tipoContrato' => array(
                'id' => $empleado->tipo_contrato ? $empleado->tipo_contrato->id : "",
                'nombre' => $empleado->tipo_contrato ? $empleado->tipo_contrato->nombre : ""
            ),
            'estado' => $empleado->estado,
            'finiquitos' => $trabajador->misFiniquitos()
        );
        
        $datos = array(
            'accesos' => $permisos,
            'datos' => $trabajadorFiniquitos
        );
        
        return Response::json($datos);     
    }
  
  	public function comprobarLiquidaciones($trabajadores)
    {
        $mes = \Session::get('mesActivo')->mes;
        $trabajadores = (array) $trabajadores;
        $sid = Trabajador::whereIn('sid', $trabajadores)->lists('id');
        $liquidaciones = Liquidacion::where('mes', $mes)->whereIn('trabajador_id', $sid)->where('mes', $mes)->get();
        foreach($liquidaciones as $liquidacion){
            $documento = $liquidacion->documento;
            if($documento){
                $documento->eliminarDocumento();                
            }
        }
        
        return $liquidaciones;
    }
    
    public function comprobarDeclaraciones($trabajadores, $anio)
    {
        $trabajadores = (array) $trabajadores;
        $sid = Trabajador::whereIn('sid', $trabajadores)->lists('id');
        $declaraciones = DeclaracionTrabajador::whereIn('trabajador_id', $sid)->where('anio_id', $anio->id)->get();
        foreach($declaraciones as $declaracion){
            if(file_exists(public_path() . '/stories/' . $declaracion->nombre_archivo)){
                unlink(public_path() . '/stories/' . $declaracion->nombre_archivo);
            }

            $declaracion->delete();                
        }
        
        return $declaraciones;
    }
    
    public function miLiquidacion()
    {
        $datos = Input::all();
        $sid = (array) $datos['trabajadores'];
        $isComprobar = $datos['comprobar'];
        $comprobar = false;
        $comprobarRentaImponibleAnterior = $datos['rentaImponibleAnterior'];
        $listaRentaImponibleAnteriorSIS = $datos['listaRentaImponibleAnteriorSIS'];
        $listaRentaImponibleAnteriorSC = $datos['listaRentaImponibleAnteriorSC'];
        
        if($isComprobar){
            $comprobar = $this->comprobarLiquidaciones($datos['trabajadores']);
        }
        
        $mes = \Session::get('mesActivo');
        $trabajadores = Trabajador::whereIn('sid', $sid)->get();
        $listaPDF = array();
        $sinRentaImponibleAnterior = array();
		$empresa = \Session::get('empresa');
		$rutEmpresa = Funciones::formatear_rut($empresa->rut);
        $liquidaciones = array();
        $cliente = Config::get('cliente.CLIENTE.EMPRESA');
        $configuracion = \Session::get('configuracion');
        $logo = $empresa->logo ? URL::to("stories/".$empresa->logo) : NULL;
        $isValida = $mes->indicadores;
        $configuracion->centro_costo = $empresa->centro_costo ? true : false;
        
        foreach($trabajadores as $trabajador){
            $empleado = $trabajador->ficha();
            $observacion = LiquidacionObservacion::where('periodo', $mes->mes)->where('trabajador_id', $trabajador->id )->first();
            $sis = 0;
            $apvs = $trabajador->misApvsPagar();
            $apvc = $trabajador->apvc();
            $diasDescontados = $trabajador->diasDescontados();
            $diasTrabajados = $trabajador->diasTrabajados();
            $horasExtra = $trabajador->horasExtraPagar();
            $horasExtraDetalle = $trabajador->horasExtraPagarDetalle();
            $totalMutual = $trabajador->totalMutual();  
            if($comprobarRentaImponibleAnterior){
                $totalAfp = $trabajador->totalAfp($listaRentaImponibleAnteriorSIS[0]);
                $totalSeguroCesantia = $trabajador->totalSeguroCesantia($listaRentaImponibleAnteriorSC[0]);
            }else{
                $totalAfp = $trabajador->totalAfp();
                $totalSeguroCesantia = $trabajador->totalSeguroCesantia();
            }
            if($totalAfp['isSIS'] && $totalSeguroCesantia['isSC']){
                $atrasos = $trabajador->descuentoAtrasos();

                if($totalAfp['pagaSis']=='empleado'){
                    $sis = $totalAfp['sis'];
                }

                $tasaAfp = $trabajador->tasaAfp();
                $totalSalud = $trabajador->totalSalud();
                $baseImpuestoUnico = $trabajador->baseImpuestoUnico();
                $impuestoDeterminado = $trabajador->impuestoDeterminado();
                $tramoImpuesto = $trabajador->tramoImpuesto()->factor;
                $sumaImponibles = $trabajador->sumaImponibles();
                $noImponibles = $trabajador->noImponibles();
                $totalOtrosDescuentos = $trabajador->totalOtrosDescuentos();
                $totalAnticipos = $trabajador->totalAnticipos();
                $totalDescuentosPrevisionales = $trabajador->totalDescuentosPrevisionales();
                $otrosImponibles = $trabajador->otrosImponibles();
                $rentaImponible = $trabajador->rentaImponible();
                $totalHaberes = $trabajador->totalHaberes();
                $cargasFamiliares = $trabajador->cargasFamiliares();
                $asignacionRetroactiva = $trabajador->asignacionRetroactiva();
                $sueldo = $trabajador->sueldo();
                $sueldoDiario = $trabajador->sueldoDiario();
                $sueldoLiquido = $trabajador->sueldoLiquido();
                $gratificacion = $trabajador->gratificacion();
                $totalColacion = 0;
                $totalMovilizacion = 0;
                $totalViatico = 0;                      
                $semanaCorrida = $trabajador->miSemanaCorrida();
                $semanaCorridas = $trabajador->miSemanaCorridas();
                $descuentos = $trabajador->misDescuentos();
                $haberes = $trabajador->misHaberes();
                $prestamos = $trabajador->misCuotasPrestamo();
                $totalAportes = ($totalMutual + $totalSeguroCesantia['totalEmpleador'] + $totalSalud['montoCaja'] + $totalSalud['montoFonasa']);
                $movimientoPersonal = $trabajador->movimientoPersonal();
                $prestamosCaja = $trabajador->prestamosCaja();
                $miCentroCosto = $empleado->miCentroCosto();
                $diasLicencia = $trabajador->totalDiasLicencias();
                $diasInasistencia = $trabajador->totalInasistencias();
                
                $licencias = array(
                    'dias' => $diasLicencia,
                    'total' => ($diasLicencia * $sueldoDiario)                    
                );
                
                $inasistencias = array(
                    'dias' => $diasInasistencia,
                    'total' => ($diasInasistencia * $sueldoDiario)                    
                );

                $filename = date("m-Y", strtotime($mes->mes))."_".$empresa->rut."_Liquidacion_".$trabajador->rut. '.pdf';
                $alias = 'Liquidación de Sueldo ' . $empleado->nombreCompleto() . ' ' . $mes->nombre . ' del ' . $mes->anio . '.pdf';


                $miLiquidacion = array(
                    'mes' => $mes->nombre . ' del ' . $mes->anio,
                    'empresa' => $empresa,
                    'rutEmpresa' => $rutEmpresa,
                    'id' => $trabajador->id,
                    'sid' => $trabajador->sid,
                    'rutFormato' => $trabajador->rut_formato(),
                    'rut' => $trabajador->rut,
                    'nombres' => $empleado->nombres,
                    'apellidos' => ucwords(strtolower($empleado->apellidos)),
                    'nombreCompleto' => $empleado->nombreCompleto(),
                    'cargo' => array(
                        'id' => $empleado->cargo ? $empleado->cargo->id : "",
                        'nombre' => $empleado->cargo ? $empleado->cargo->nombre : ""
                    ),
                    'seccion' => array(
                        'id' => $empleado->seccion ? $empleado->seccion->id : "",
                        'nombre' => $empleado->seccion ? $empleado->seccion->nombre : ""
                    ),
                    'centroCosto' => array(
                        'id' => $empleado->centroCosto ? $empleado->centroCosto->id : "",
                        'nombre' => $empleado->centroCosto ? $empleado->centroCosto->nombre : ""
                    ),
                    'fechaIngreso' => $empleado->fecha_ingreso,
                    'tipoContrato' => array(
                        'id' => $empleado->tipoContrato ? $empleado->tipoContrato->id : "",
                        'nombre' => $empleado->tipoContrato ? $empleado->tipoContrato->nombre : ""
                    ),
                    'monedaSueldo' => $empleado->moneda_sueldo,
                    'sueldoBase' => $trabajador->sueldoBase(),
                    'colacion' => array(
                        'monto' => 0
                    ),
                    'movilizacion' => array(
                        'monto' => 0
                    ),
                    'viatico' => array(
                        'monto' => 0
                    ),
                    'afp' => array(
                        'id' => $empleado->afp ? $empleado->afp->id : "",
                        'nombre' => $empleado->afp ? strtoupper($empleado->afp->glosa) : ""
                    ),
                    'prevision' => array(
                        'id' => $empleado->prevision ? $empleado->prevision->id : "",
                        'nombre' => $empleado->prevision ? strtoupper($empleado->prevision->glosa) : ""
                    ),
                    'seguroDesempleo' => $empleado->seguro_desempleo ? true : false,
                    'afpSeguro' => array(
                        'id' => $empleado->afpSeguro ? $empleado->afpSeguro->id : "",
                        'nombre' => $empleado->afpSeguro ? strtoupper($empleado->afpSeguro->glosa) : ""
                    ),
                    'isapre' => array(
                        'id' => $empleado->isapre ? $empleado->isapre->id : "",
                        'nombre' => $empleado->isapre ? $empleado->isapre->glosa : ""
                    ),
                    'licencias' => $licencias,
                    'inasistencias' => $inasistencias,
                    'cotizacionIsapre' => $empleado->cotizacion_isapre,
                    'montoIsapre' => $empleado->monto_isapre,
                    'sindicato' => $empleado->sindicato ? true : false,
                    'monedaSindicato' => $empleado->moneda_sindicato,
                    'montoSindicato' => $empleado->monto_sindicato,
                    'estado' => $empleado->estado,
                    'diasTrabajados' => $diasTrabajados,
                    'diasDescontados' => $diasDescontados,
                    'sueldoDiario' => $sueldoDiario,
                    'sueldo' => $sueldo,
                    'gratificacion' => $gratificacion,
                    'horasExtra' => $horasExtra,
                    'horasExtraDetalle' => $horasExtraDetalle,
                    'imponibles' => $sumaImponibles,
                    'totalHaberes' => $totalHaberes,
                    'cargasFamiliares' => $cargasFamiliares,
                    'asignacionRetroactiva' => $asignacionRetroactiva,
                    'noImponibles' => $noImponibles,
                    'semanaCorrida' => $semanaCorrida,
                    'semanaCorridas' => $semanaCorridas,
                    'isSemanaCorrida' => $empleado->semana_corrida ? true : false,
                    'rentaImponible' => $rentaImponible,
                    'tasaAfp' => $tasaAfp['tasaTrabajador'],
                    'tasaSis' => $tasaAfp['tasaSis'],
                    'totalMutual' => $totalMutual,
                    'totalAfp' => $totalAfp['totalTrabajador'],
                    'totalAfpEmpleador' => $totalAfp['totalEmpleador'],
                    'totalSalud' => $totalSalud,
                    'totalSeguroCesantia' => $totalSeguroCesantia,
                    'totalDescuentosPrevisionales' => $totalDescuentosPrevisionales,
                    'totalDescuentos' => ($totalDescuentosPrevisionales + $totalOtrosDescuentos + $impuestoDeterminado),
                    'baseImpuestoUnico' => $baseImpuestoUnico['base'],
                    'rebaja' => $baseImpuestoUnico['rebaja'],
                    'tramoImpuesto' => $tramoImpuesto,
                    'impuestoDeterminado' => $impuestoDeterminado,
                    'totalOtrosDescuentos' => $totalOtrosDescuentos,
                    'otrosImponibles' => $otrosImponibles,
                    'totalAportes' => $totalAportes,
                    'apvs' => $apvs,
                    'haberes' => $haberes,
                    'haberesImponibles' => $trabajador->haberesImponibles(),
                    'haberesNoImponibles' => $trabajador->haberesNoImponibles(),
                    'descuentos' => $descuentos,
                    'prestamos' => $prestamos,
                    'sueldoLiquidoPalabras' => strtoupper(Funciones::convertirPalabras($sueldoLiquido)),
                    'sueldoLiquido' => $sueldoLiquido,
                    'prop' => $trabajador->gratificacionProporcional(),
                    'banco' => $empleado->banco ? $empleado->banco->nombre : "",
                    'cuenta' => $empleado->numero_cuenta ? $empleado->numero_cuenta : "",
                    'sis' => $sis,
                    'uf' => $mes->uf,
                    'observacion' => $observacion? $observacion->observaciones : "",
                    'nombreDocumento' => $filename,
                    'aliasDocumento' => $alias,
                    'atrasos' => $atrasos,
                    'logoEmpresa' => $logo,
                    'z' => $trabajador->horasExtraPagarDetalle()
                );

                $documento = new Documento();
                $documento->sid = Funciones::generarSID();
                $documento->trabajador_id = $trabajador->id;
                $documento->tipo_documento_id = 4;
                $documento->nombre = $filename;
                $documento->alias = $alias;
                $documento->descripcion = 'Liquidación de Sueldo de ' . $empleado->nombreCompleto() . ' del mes de ' . $mes->nombre . ' del ' . $mes->anio;
                $documento->save();

                $totalApv = 0;

                if($apvs){                    
                    foreach($apvs as $apv){
                        $totalApv = ($totalApv + $apv['montoPesos']);
                    }
                }

                $html = "";
                $view = View::make('pdf.liquidacion', [
                    'liquidacion' => $miLiquidacion,
                    'configuracion' => $configuracion,
                    'isValida' => $isValida
                ]);
                $html = $view->render();

                $liquidacion = new Liquidacion();
                $liquidacion->sid = Funciones::generarSID();
                $liquidacion->trabajador_id = $trabajador->id;
                $liquidacion->documento_id = $documento->id;
                $liquidacion->empresa_id = $empresa->id;
                $liquidacion->empresa_razon_social = $empresa->razon_social;
                $liquidacion->empresa_rut = $empresa->rut;
                $liquidacion->empresa_direccion = $empresa->direccion;
                $liquidacion->inasistencias = $trabajador->totalInasistencias();
                $liquidacion->encargado_id = Auth::usuario()->user()->id;
                $liquidacion->mes = $mes->mes;
                $liquidacion->folio = 45646548;
                $liquidacion->estado = ($empleado->estado=='Ingresado') ? 1 : 0;
                $liquidacion->trabajador_rut = $trabajador->rut;
                $liquidacion->trabajador_nombres = $empleado->nombres;
                $liquidacion->trabajador_apellidos = $empleado->apellidos;
                $liquidacion->trabajador_cargo = $empleado->cargo ? $empleado->cargo->nombre : "";
                $liquidacion->trabajador_seccion = $empleado->miSeccion();
                $liquidacion->trabajador_centro_costo = $miCentroCosto['nombre'];
                $liquidacion->centro_costo_codigo = $miCentroCosto['codigo'];
                $liquidacion->trabajador_tienda = $empleado->miTienda();
                $liquidacion->trabajador_fecha_ingreso = $empleado->fecha_ingreso;
                $liquidacion->uf = $mes->uf;
                $liquidacion->utm = $mes->utm;
                $liquidacion->dias_trabajados = $diasTrabajados;
                $liquidacion->horas_extra = $horasExtra['cantidad'];
                $liquidacion->total_horas_extra = $horasExtra['total'];
                $liquidacion->tipo_contrato = $empleado->tipoContrato->nombre;
                $liquidacion->sueldo_base = $trabajador->sueldoBase();
                $liquidacion->seguro_cesantia = $empleado->seguro_desempleo ? $empleado->seguro_desempleo : 0;
                $liquidacion->base_impuesto_unico = $baseImpuestoUnico['base'];
                $liquidacion->rebaja_zona = $baseImpuestoUnico['rebaja'];
                $liquidacion->impuesto_determinado = $impuestoDeterminado;
                $liquidacion->tramo_impuesto = $tramoImpuesto;
                $liquidacion->imponibles = $sumaImponibles;
                $liquidacion->no_imponibles = $noImponibles;
                $liquidacion->total_otros_descuentos = $totalOtrosDescuentos;
                $liquidacion->total_anticipos = $totalAnticipos;
                $liquidacion->total_descuentos_previsionales = $totalDescuentosPrevisionales;
                $liquidacion->total_descuentos = ($totalDescuentosPrevisionales + $totalOtrosDescuentos + $impuestoDeterminado);
                $liquidacion->total_aportes = $totalAportes;
                $liquidacion->renta_imponible = $rentaImponible;
                $liquidacion->otros_imponibles = $otrosImponibles;
                $liquidacion->total_haberes = $totalHaberes;
                $liquidacion->total_cargas = $cargasFamiliares['monto'];
                $liquidacion->cantidad_cargas = $cargasFamiliares['cantidad'];
                $liquidacion->cantidad_cargas_invalidas = $cargasFamiliares['cantidadInvalidas'];
                $liquidacion->cantidad_cargas_simples = $cargasFamiliares['cantidadSimples'];
                $liquidacion->cantidad_cargas_maternales = $cargasFamiliares['cantidadMaternales'];
                $liquidacion->asignacion_retroactiva = $asignacionRetroactiva;
                $liquidacion->reintegro_cargas = 0;
                $liquidacion->sueldo = $sueldo;
                $liquidacion->sueldo_diario = $sueldoDiario;
                $liquidacion->sueldo_liquido = $sueldoLiquido;
                $liquidacion->gratificacion = $gratificacion;
                $liquidacion->colacion = $totalColacion;
                $liquidacion->movilizacion = $totalMovilizacion;
                $liquidacion->viatico = $totalViatico;
                $liquidacion->movimiento_personal = $movimientoPersonal['codigo'];
                $liquidacion->fecha_desde = $movimientoPersonal['fechaDesde'];
                $liquidacion->fecha_hasta = $movimientoPersonal['fechaHasta'];
                $liquidacion->tramo_id = $empleado->tramo_id ? $empleado->tramo_id : 'a';
                $liquidacion->prevision_id = $empleado->prevision_id;
                $liquidacion->observacion = $observacion? $observacion->observaciones : "";
                $liquidacion->centro_costo_id = $empleado->centro_costo_id;
                $liquidacion->cuerpo = $html;

                $liquidacion->save();

                if($totalAfp['isAfp']){
                    $detalleAfp = new DetalleAfp();
                    $detalleAfp->liquidacion_id = $liquidacion->id;
                    $detalleAfp->afp_id = $empleado->afp_id;
                    $detalleAfp->renta_imponible = $totalAfp['rentaImponible'];
                    $detalleAfp->cotizacion = $totalAfp['cotizacion'];
                    $detalleAfp->sis = $totalAfp['sis'];
                    $detalleAfp->paga_sis = $totalAfp['pagaSis'];
                    $detalleAfp->porcentaje_cotizacion = $totalAfp['porcentajeCotizacion'];
                    $detalleAfp->porcentaje_sis = $totalAfp['porcentajeSis'];
                    $detalleAfp->cuenta_ahorro_voluntario = $totalAfp['cuentaAhorroVoluntario'];
                    $detalleAfp->renta_imponible_ingresada = $totalAfp['rentaImponibleIngresada'];
                    $detalleAfp->renta_sustitutiva = 0;
                    $detalleAfp->tasa_sustitutiva = 0;
                    $detalleAfp->aporte_sustitutiva = 0;
                    $detalleAfp->numero_periodos = 0;
                    $detalleAfp->periodo_desde = null;
                    $detalleAfp->periodo_hasta = null;
                    $detalleAfp->puesto_trabajo_pesado = null;
                    $detalleAfp->porcentaje_trabajo_pesado = 0;
                    $detalleAfp->cotizacion_trabajo_pesado = 0;
                    //$detalleAfp->save();

                    $liquidacion->setRelation('detalleAfp', $detalleAfp);
                }
                /*
                if($totalColacion>0){                
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = 'Colación';
                    $detalleLiquidacion->tipo = 'no imponible';
                    $detalleLiquidacion->tipo_id = 1;
                    $detalleLiquidacion->valor = $totalColacion;
                    $detalleLiquidacion->valor_2 = $empleado->monto_colacion;
                    $detalleLiquidacion->valor_3 = $empleado->moneda_colacion;
                    $detalleLiquidacion->valor_4 = null;
                    $detalleLiquidacion->valor_5 = null;
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->detalle_id = 3;
                    //$detalleLiquidacion->save(); 

                    $liquidacion->detalles->add( $detalleLiquidacion );
                }
                if($totalMovilizacion>0){                
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = 'Movilización';
                    $detalleLiquidacion->tipo = 'no imponible';
                    $detalleLiquidacion->tipo_id = 1;
                    $detalleLiquidacion->valor = $totalMovilizacion;
                    $detalleLiquidacion->valor_2 = $empleado->monto_movilizacion;
                    $detalleLiquidacion->valor_3 = $empleado->moneda_movilizacion;
                    $detalleLiquidacion->valor_4 = null;
                    $detalleLiquidacion->valor_5 = null;
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->detalle_id = 4;
                    //$detalleLiquidacion->save(); 

                    $liquidacion->detalles->add( $detalleLiquidacion );
                }
                if($totalViatico>0){                
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = 'Viático';
                    $detalleLiquidacion->tipo = 'no imponible';
                    $detalleLiquidacion->tipo_id = 1;
                    $detalleLiquidacion->valor = $totalColacion;
                    $detalleLiquidacion->valor_2 = $empleado->monto_viatico;
                    $detalleLiquidacion->valor_3 = $empleado->moneda_viatico;
                    $detalleLiquidacion->valor_4 = null;
                    $detalleLiquidacion->valor_5 = null;
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->detalle_id = 5;
                    //$detalleLiquidacion->save(); 

                    $liquidacion->detalles->add( $detalleLiquidacion );
                }  */
                if($semanaCorrida>0){                
                    $detalleLiquidacion = new DetalleLiquidacion();
                    $detalleLiquidacion->sid = Funciones::generarSID();
                    $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                    $detalleLiquidacion->nombre = 'Semana Corrida';
                    $detalleLiquidacion->tipo = 'imponible';
                    $detalleLiquidacion->tipo_id = 1;
                    $detalleLiquidacion->valor = $semanaCorrida;
                    $detalleLiquidacion->valor_2 = $semanaCorrida;
                    $detalleLiquidacion->valor_3 = '$';
                    $detalleLiquidacion->valor_4 = null;
                    $detalleLiquidacion->valor_5 = null;
                    $detalleLiquidacion->valor_6 = null;
                    $detalleLiquidacion->detalle_id = 6;
                    //$detalleLiquidacion->save(); 

                    $liquidacion->detalles->add( $detalleLiquidacion );
                }            
                if($haberes){                
                    foreach($haberes as $haber)
                    {
                        $detalleLiquidacion = new DetalleLiquidacion();
                        $detalleLiquidacion->sid = Funciones::generarSID();
                        $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                        $detalleLiquidacion->nombre = $haber['tipo']['nombre'];
                        $detalleLiquidacion->tipo = $haber['tipo']['imponible'] ? 'imponible' : 'no imponible';
                        $detalleLiquidacion->tipo_id = 1;
                        $detalleLiquidacion->valor = $haber['montoPesos'];
                        $detalleLiquidacion->valor_2 = $haber['monto'];
                        $detalleLiquidacion->valor_3 = $haber['moneda'];
                        $detalleLiquidacion->valor_4 = null;
                        $detalleLiquidacion->valor_5 = null;
                        $detalleLiquidacion->valor_6 = null;
                        $detalleLiquidacion->detalle_id = $haber['tipo']['id'];
                        //$detalleLiquidacion->save(); 

                        $liquidacion->detalles->add($detalleLiquidacion);
                    }
                }
                
                if($horasExtraDetalle){                
                    foreach($horasExtraDetalle as $horaExtra)
                    {
                        $detalleLiquidacion = new DetalleLiquidacion();
                        $detalleLiquidacion->sid = Funciones::generarSID();
                        $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                        $detalleLiquidacion->nombre = $horaExtra['nombre'];
                        $detalleLiquidacion->tipo = $horaExtra['imponible'] ? 'imponible' : 'no imponible';
                        $detalleLiquidacion->tipo_id = 5;
                        $detalleLiquidacion->valor = round($horaExtra['total']);
                        $detalleLiquidacion->valor_2 = null;
                        $detalleLiquidacion->valor_3 = $horaExtra['cantidad'];
                        $detalleLiquidacion->valor_4 = $horaExtra['horas'];
                        $detalleLiquidacion->valor_5 = $horaExtra['minutos'];
                        $detalleLiquidacion->valor_6 = null;
                        $detalleLiquidacion->detalle_id = $horaExtra['idTipo'];
                        //$detalleLiquidacion->save(); 

                        $liquidacion->detalles->add($detalleLiquidacion);
                    }
                }

                if($descuentos){                    
                    foreach($descuentos as $descuento)
                    {
                        //if($descuento->estructura_descuento_id!=3){
                            $detalleLiquidacion = new DetalleLiquidacion();
                            $detalleLiquidacion->sid = Funciones::generarSID();
                            $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                            $detalleLiquidacion->nombre = $descuento['tipo']['nombre'];
                            $detalleLiquidacion->tipo = 'descuento';
                            $detalleLiquidacion->tipo_id = 2;
                            $detalleLiquidacion->valor = $descuento['montoPesos'];
                            $detalleLiquidacion->valor_2 = $descuento['monto'];
                            $detalleLiquidacion->valor_3 = $descuento['moneda'];
                            if($descuento['tipo']['estructura']['id']==2){
                                $detalleLiquidacion->valor_4 = 1;   
                                $detalleLiquidacion->valor_5 = null;
                                $detalleLiquidacion->valor_6 = null;
                            }else if($descuento['tipo']['estructura']['id']==3){
                                $detalleLiquidacion->valor_4 = 2;   
                                $detalleLiquidacion->valor_5 = $descuento['tipo']['formaPago']['id'];
                                $detalleLiquidacion->valor_6 = $descuento['tipo']['afp']['id'];
                            }else{
                                if($descuento['tipo']['id']==2){                            
                                    $detalleLiquidacion->valor_4 = 3;                              
                                }else{
                                    $detalleLiquidacion->valor_4 = null;                              
                                }
                                $detalleLiquidacion->valor_5 = null;
                                $detalleLiquidacion->valor_6 = null;
                            }
                            $detalleLiquidacion->detalle_id = $descuento['tipo']['id'];

                            //$detalleLiquidacion->save(); 

                            $liquidacion->detalles->add($detalleLiquidacion);
                        //}
                    }
                }

                if($apvs){                    
                    foreach($apvs as $apv)
                    {                    
                        $detalleApvi = new DetalleApvi();
                        $detalleApvi->liquidacion_id = $liquidacion->id;
                        $detalleApvi->afp_id = $apv['afp']['id'];
                        $detalleApvi->numero_contrato = $apv['numeroContrato'];
                        $detalleApvi->forma_pago_id = $apv['formaPago']['id'];
                        $detalleApvi->monto = $apv['montoPesos'];
                        $detalleApvi->moneda = $apv['moneda'];
                        $detalleApvi->regimen = strtolower($apv['regimen']);
                        $detalleApvi->cotizacion = $apv['monto'];
                        $detalleApvi->cotizacion_depositos_convenidos = 0;
                        //$detalleApvi->save();

                        $liquidacion->detalleApvi->add($detalleApvi);
                    }
                }

                if($prestamos){                    
                    foreach($prestamos as $prestamo)
                    {
                        $prestamo = (array) $prestamo;
                        $detalleLiquidacion = new DetalleLiquidacion();
                        $detalleLiquidacion->sid = Funciones::generarSID();
                        $detalleLiquidacion->liquidacion_id = $liquidacion->id;
                        $detalleLiquidacion->nombre = $prestamo['nombreLiquidacion'];
                        $detalleLiquidacion->tipo = $prestamo['codigo'];
                        $detalleLiquidacion->tipo_id = 4;
                        $detalleLiquidacion->valor = $prestamo['montoCuotaPagar'];
                        $detalleLiquidacion->valor_2 = $prestamo['monto'];
                        $detalleLiquidacion->valor_3 = $prestamo['moneda'];
                        $detalleLiquidacion->valor_4 = $prestamo['numeroCuotaPagar'];
                        $detalleLiquidacion->valor_5 = $prestamo['cuotas'];
                        if($prestamo['prestamoCaja']){
                            $detalleLiquidacion->valor_6 = 1;
                        }else if($prestamo['leasingCaja']){
                            $detalleLiquidacion->valor_6 = 2;
                        }
                        $detalleLiquidacion->detalle_id = $prestamo['id'];
                        //$detalleLiquidacion->save(); 

                        $liquidacion->detalles->add( $detalleLiquidacion );
                    }
                }

                if($apvc){
                    $detalleApvc = new DetalleApvc();
                    $detalleApvc->liquidacion_id = $liquidacion->id;
                    $detalleApvc->afp_id = $apvc['idAfp'];
                    $detalleApvc->numero_contrato = $apvc['numeroContrato'];
                    $detalleApvc->forma_pago_id = $apvc['idFormaPago'];
                    $detalleApvc->monto = $apvc['monto'];
                    $detalleApvc->moneda = $apvc['moneda'];
                    $detalleApvc->cotizacion_trabajador = $apvc['cotizacionTrabajador'];
                    $detalleApvc->cotizacion_empleador = $apvc['cotizacionEmpleador'];
                    //$detalleApvc->save();

                    $liquidacion->detalleApvc->add($detalleApvc);
                }

                $detalleIpsIslFonasa = new DetalleIpsIslFonasa();
                $detalleIpsIslFonasa->liquidacion_id = $liquidacion->id;
                if($empleado->prevision_id==9){
                    $detalleIpsIslFonasa->ex_caja_id = $empleado->afp_id;
                    $detalleIpsIslFonasa->tasa_cotizacion = $tasaAfp['tasaTrabajador'];
                    $detalleIpsIslFonasa->cotizacion_obligatoria = $totalAfp['totalTrabajador'];
                }else{
                    $detalleIpsIslFonasa->ex_caja_id = 0;
                    $detalleIpsIslFonasa->tasa_cotizacion = 0;
                    $detalleIpsIslFonasa->cotizacion_obligatoria = 0;
                }
                $detalleIpsIslFonasa->renta_imponible = $rentaImponible;
                $detalleIpsIslFonasa->renta_imponible_desahucio = 0;
                $detalleIpsIslFonasa->ex_caja_desahucio_id = 0;
                $detalleIpsIslFonasa->tasa_desahucio = 0;
                $detalleIpsIslFonasa->cotizacion_desahucio = 0;
                if($empleado->isapre_id==246){
                    $detalleIpsIslFonasa->cotizacion_fonasa = $totalSalud['montoFonasa'];
                }else{
                    $detalleIpsIslFonasa->cotizacion_fonasa = 0;
                }
                if($empresa->mutual_id==263){
                    $detalleIpsIslFonasa->cotizacion_isl = $totalMutual;                
                }else{
                    $detalleIpsIslFonasa->cotizacion_isl = 0;
                }
                $detalleIpsIslFonasa->bonificacion = 0;
                $detalleIpsIslFonasa->descuento_cargas_isl = $totalSalud['cargas'];
                $detalleIpsIslFonasa->bonos_gobierno = 0;
                //$detalleIpsIslFonasa->save();   

                $liquidacion->setRelation('detalleIpsIslFonasa', $detalleIpsIslFonasa);

                $detalleSalud = new DetalleSalud();
                $detalleSalud->liquidacion_id = $liquidacion->id;
                $detalleSalud->salud_id = $empleado->isapre_id;

                if($empleado->salud_id!=240 & $empleado->salud_id!=246){
                    $detalleSalud->numero_fun = 145652;
                    $detalleSalud->renta_imponible = $rentaImponible;
                    if($empleado->cotizacion_isapre=='UF'){
                        $mon = 'UF';
                        $pac = $empleado->monto_isapre;
                    }else{
                        $mon = '$';
                        if($empleado->cotizacion_isapre=='7%'){
                            $pac = round($rentaImponible * 0.07);
                        }else if($empleado->cotizacion_isapre=='7% + UF'){
                            $pac = round($rentaImponible * 0.07);
                            $pac += Funciones::convertirUF($empleado->monto_isapre);
                        }else{
                            $pac = $empleado->monto_isapre;
                        }
                    }
                    $detalleSalud->moneda = $mon;
                    $detalleSalud->cotizacion_pactada = $pac;
                    $detalleSalud->cotizacion_obligatoria = $totalSalud['obligatorio'];
                    $detalleSalud->cotizacion_adicional = $totalSalud['adicional'];
                    $detalleSalud->ges = 0;
                }else{
                    $detalleSalud->numero_fun = 0;
                    $detalleSalud->renta_imponible = $rentaImponible;
                    $detalleSalud->moneda = null;
                    $detalleSalud->cotizacion_pactada = 0;
                    $detalleSalud->cotizacion_obligatoria = 0;
                    $detalleSalud->cotizacion_adicional = 0;
                    $detalleSalud->ges = 0;
                }
                //$detalleSalud->save(); 

                $liquidacion->setRelation('detalleSalud', $detalleSalud);

                if($empresa->caja_id!=257){
                    $detalleCaja = new DetalleCaja();
                    $detalleCaja->liquidacion_id = $liquidacion->id;
                    $detalleCaja->caja_id = $empresa->caja_id;
                    $detalleCaja->renta_imponible = $rentaImponible;
                    $detalleCaja->creditos_personales = $prestamosCaja['caja'];
                    $detalleCaja->descuento_dental = $prestamosCaja['dental'];
                    $detalleCaja->descuentos_leasing = $prestamosCaja['leasing'];
                    $detalleCaja->descuentos_seguro = $prestamosCaja['seguro'];
                    $detalleCaja->otros_descuentos = $prestamosCaja['otros'];
                    $detalleCaja->cotizacion = $totalSalud['montoCaja'];
                    $detalleCaja->descuento_cargas = $prestamosCaja['cargas'];
                    $detalleCaja->otros_descuentos_1 = 0;
                    $detalleCaja->otros_descuentos_2 = 0;
                    $detalleCaja->bonos_gobierno = 0;
                    $detalleCaja->codigo_sucursal = '';                
                    //$detalleCaja->save();

                    $liquidacion->setRelation('detalleCaja', $detalleCaja);
                }

                if($empresa->mutual_id!=263){
                    $detalleMutual = new DetalleMutual();
                    $detalleMutual->liquidacion_id = $liquidacion->id;
                    $detalleMutual->mutual_id = $empresa->mutual_id;
                    $detalleMutual->renta_imponible = $rentaImponible;
                    $detalleMutual->cotizacion_accidentes = $totalMutual;
                    $detalleMutual->codigo_sucursal = 0;
                    //$detalleMutual->save();

                    $liquidacion->setRelation('detalleMutual', $detalleMutual);
                }

                if($empleado->seguro_desempleo==1){
                    $detalleSeguroCesantia = new DetalleSeguroCesantia();
                    $detalleSeguroCesantia->liquidacion_id = $liquidacion->id;
                    $detalleSeguroCesantia->renta_imponible = $totalSeguroCesantia['rentaImponible'];
                    $detalleSeguroCesantia->renta_imponible_ingresada = $totalSeguroCesantia['rentaImponibleIngresada'];
                    $detalleSeguroCesantia->afp_id = $empleado->afp_seguro_id ? $empleado->afp_seguro_id : $empleado->afp_id;
                    $detalleSeguroCesantia->aporte_trabajador = $totalSeguroCesantia['total'];
                    $detalleSeguroCesantia->afc_trabajador = $totalSeguroCesantia['afc'];
                    $detalleSeguroCesantia->aporte_empleador = $totalSeguroCesantia['totalEmpleador'];
                    $detalleSeguroCesantia->afc_empleador = $totalSeguroCesantia['afcEmpleador'];
                    //$detalleSeguroCesantia->save();

                    $liquidacion->setRelation('detalleSeguroCesantia', $detalleSeguroCesantia);
                }

                if($liquidacion->movimiento_personal==3){
                    $detallePagadorSubsidio = new DetallePagadorSubsidio();
                    $detallePagadorSubsidio->liquidacion_id = $liquidacion->id;
                    $detallePagadorSubsidio->rut = 0;
                    $detallePagadorSubsidio->digito = 0;
                    //$detallePagadorSubsidio->save();

                    $liquidacion->setRelation('detallePagadorSubsidio', $detallePagadorSubsidio);
                }

                $liquidacion->push();

                Logs::crearLog('#liquidaciones-de-sueldo', $documento->id, $documento->alias, 'Create', $documento->trabajador_id, $empleado->nombreCompleto(), 'Liquidaciones Trabajadores');

                $liquidaciones[] = $miLiquidacion;
                $miLiquidacion['sidDocumento'] = $documento->sid;
                $listaPDF[] = $miLiquidacion;
                $destination = public_path() . '/stories/' . $filename;
                //$dompdf->set_option('isHtml5ParserEnabled', true);
                //$html = View::make('pdf.liquidacion', array('liquidaciones' => $liquidaciones))->render();

                //File::put($destination, PDF::load(utf8_decode($html), 'A4', 'portrait')->output());
                $pdf = new \Thujohn\Pdf\Pdf();
                $content = $pdf->load(View::make('pdf.liquidacion', array('liquidacion' => $miLiquidacion,
                    'configuracion' => $configuracion, 'isValida' => $isValida)))->output();
                File::put($destination, $content);  
            }else{
                $empleado->rutFormato = $trabajador->rut_formato();
                $empleado->nombreCompleto = $empleado->nombreCompleto();
                $empleado->sis = 0;
                $empleado->sidTrabajador = $trabajador->sid;
                $empleado->motivo = 'Trabajador sin R.I. anterior con 30 días trabajados.';
                $empleado->isSIS = $totalAfp['isSIS'];
                $empleado->isSC = $totalSeguroCesantia['isSC'];
                $empleado->sc = $totalSeguroCesantia;
                $empleado->sisis = $totalAfp;
                $sinRentaImponibleAnterior[] = $empleado;
            }
        }
        
        PDF::clear();
        
        if(count($sinRentaImponibleAnterior)){                    
            if(count($liquidaciones)>0){
                $mensaje = "Las Liquidaciones de Sueldo fueron ingresadas parcialmente";
            }else{
                if(count($sinRentaImponibleAnterior)>1){
                    $mensaje = "Las Liquidación de Sueldo no pudieron ser generadas";       
                }else{
                    $mensaje = "La Liquidación de Sueldo fue no pudo ser generada";                    
                }
            }
        }else{
            if(count($liquidaciones)>1){
                $mensaje = "Las Liquidaciones de Sueldo fueron ingresadas correctamente";
            }else{
                $mensaje = "La Liquidación de Sueldo fue ingresada correctamente";
            }
        }
        $listaPDF = Funciones::ordenar($listaPDF, 'apellidos');
        $datos = array(
            'success' => true,
            'mensaje' => $mensaje,
            'datos' => $listaPDF,
            'sinRentaImponibleAnterior' => $sinRentaImponibleAnterior,
            'a' => $configuracion
        );
        
        return Response::json($datos);
    
    }
    
    
    public function documentoPDF($sid)
    {
        $name = Documento::whereSid($sid)->first()['nombre'];
        
        $destination = public_path() . '/stories/' . $name;
      
        return Response::make(file_get_contents($destination), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$name.'"'
        ]);      		
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
        $trabajador = Trabajador::whereSid($sid)->first();
        $datos = $this->get_datos_formulario();
        $idFicha = $datos['ficha_id'];
        $idMes = \Session::get('mesActivo')->id;
        $mes = \Session::get('mesActivo')->mes;
        $ficha = FichaTrabajador::find($idFicha);
        $errores = Trabajador::errores($datos);              
        $ficha = FichaTrabajador::find($idFicha);    
        $fichaAnterior = null;
        if($datos['nueva_ficha']){
            $fichaAnterior = FichaTrabajador::where('trabajador_id', $trabajador->id)->where('fecha', $mes)->first();
            if($fichaAnterior){
                $fichaAnterior->delete();
            }
            $ficha = new FichaTrabajador();
            $ficha->trabajador_id = $trabajador->id;            
            $ficha->fecha = $mes;
            $ficha->mes_id = $idMes;
        }
        
        if($ficha->estado==='Finiquitado' && $ficha->fecha_finiquito){
            $ficha->fecha_finiquito = null;            
        }
        if($datos['estado']=='Ingresado'){   
            $ficha->tramo_id = FichaTrabajador::calcularTramo(Funciones::convertir($datos['sueldo_base'], $datos['moneda_sueldo']));
            if($ficha->estado==='En Creación'){
                $fecha = $trabajador->fechaFicha($datos['fecha_ingreso']);
                $ficha->fecha = $fecha;
                $trabajador->calcularMisVacaciones($ficha->fecha_reconocimiento);
                $trabajador->crearUser();
                $ficha->tramo_id = FichaTrabajador::calcularTramo(Funciones::convertir($datos['sueldo_base'], $datos['moneda_sueldo']));         
                if($ficha->semana_corrida==1){
                    $trabajador->crearSemanaCorrida();
                }
            }
        }
        
        if(!$errores and $trabajador){
            $trabajador->rut = $datos['rut'];
            $trabajador->save();   
            
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
            $ficha->tipo_semana = $datos['tipo_semana'];
            $ficha->tipo_sueldo = $datos['tipo_sueldo'];
            $ficha->horas = $datos['horas'];
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
            $ficha->zona_id = $datos['zona_id'];
            $ficha->estado = $datos['estado'];            
            $ficha->save();              
            
            $misHaberes = $trabajador->comprobarHaberes($datos['haberes']);
            $misDescuentos = $trabajador->comprobarDescuentos($datos['descuentos']);
            
            Logs::crearLog('#trabajadores', $trabajador->id, $trabajador->rut_formato(), 'Update', $ficha->id, $ficha->nombreCompleto(), 'Trabajadores');  

            $respuesta = array(
            	'success' => true,
            	'mensaje' => "La Información fue actualizada correctamente",
                'hab' => $misHaberes,
                'des' => $misDescuentos,
                'sid' => $trabajador->sid
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
        $trabajador = Trabajador::whereSid($sid)->first();
        $empleado = $trabajador->ficha();
        Logs::crearLog('#trabajadores', $trabajador->id, $trabajador->rut_formato(), 'Delete', $empleado->id, $empleado->nombreCompleto(), 'Trabajadores'); 
        $trabajador->eliminarDatos();
        $trabajador->delete();        
        
        return Response::json(array('success' => true, 'mensaje' => $mensaje));
    }
    
    public function get_datos_formulario()
    {
        $datos = array(
            'rut' => Input::get('rut'),
            'id' => Input::get('id'),
            'nueva_ficha' => Input::get('nuevaFicha'),
            'ficha_id' => Input::get('idFicha'),
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
            'tipo_semana' => Input::get('tipoSemana'),
            'tipo_sueldo' => Input::get('tipoSueldo'),
            'horas' => Input::get('horas'),
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
            'gratificacion_proporcional_licencias' => Input::get('proporcionalLicencias'),
            'estado' => Input::get('estado'),
            'descuentos' => Input::get('descuentos'),
            'haberes' => Input::get('haberes'),
            'zona_id' => Input::get('zonaImpuestoUnico')['id'],
            'estadoUser' => Input::get('estadoUser')
        );
        
        return $datos;
    }

}
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">  
    #mes td 
    {
        text-align:center; 
        vertical-align:middle;
    }        
    #encabezado{
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
    }
    #mes{
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        text-align: center;
        margin-top: 40px;
        margin-bottom: 30px;
    }
    #libro{
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;        
        border-collapse: collapse;
    }  
    .head td{
        font-weight: 900;
    }
    #libro td{
        border-bottom: 1px solid black;       
    }
    #libro td{
        border-bottom: 1px solid black; 
        padding-top: 10px;
        padding-bottom: 10px;
    } 
    #totales td{
        border-bottom: 0;  
    } 
    #bottom td{
        border-bottom: 0;
    } 
  </style>
  <?php $secondPage = false; ?>
  <?php $totales = false; ?>
  <div class="page">
        <table id="encabezado">
            <tbody>
                <tr>
                    <td colspan="4">{{ $datos->empresa['razon_social'] }}</td>
                </tr>
                <tr>
                    <td colspan="4">RUT: {{ $datos->empresa['rutFormato'] }}</td>
                </tr>
                <tr>
                    <td colspan="4">{{ $datos->empresa['actividad_economica'] }}</td>
                </tr>
                <tr>
                    <td colspan="4">{{ $datos->empresa['domicilio'] }}</td>
                </tr>
            </tbody>
        </table>
        <div id="mes">
            <h3><b>LIBRO DE REMUNERACIONES</b></h3>
            <h4><b>{{ $datos->mes }}</b></h4>
        </div>
        <table id="libro">
            <tbody>
                <tr class="head">                    
                    <td>RUT</td>   
                    <td>NOMBRE</td>   
                    @if($datos->conceptos['sueldo_base'])<td>S. BASE</td>@endif
                    @if($datos->conceptos['dias_trabajados'])<td>DT</td>@endif  
                    @if($datos->conceptos['inasistencias'])<td>INASIST.</td>@endif       
                    @if($datos->conceptos['horas_extra'])<td>H. EXTRAS</td>@endif 
                    @if($datos->conceptos['sueldo'])<td>SUELDO</td>@endif        
                    @if($datos->conceptos['gratificacion'])<td>GRAT. LEGAL</td>@endif  
                    @if($datos->conceptos['total_imponibles'])<td>TOTAL IMP.</td>@endif  
                    @if($datos->conceptos['asignacion_familiar'])<td>ASIG. FAM.</td>@endif 
                    @if($datos->conceptos['no_imponibles'])<td>TOT. NO IMP.</td>@endif       
                    @if($datos->conceptos['total_haberes'])<td>TOT. HABERES</td>@endif  
                    @if($datos->conceptos['afp'])<td>AFP</td>@endif      
                    @if($datos->conceptos['apv'])<td>APV</td>@endif   
                    @if($datos->conceptos['salud'])<td>SALUD</td>@endif     
                    @if($datos->conceptos['seguro_cesantia'])<td>SEG. CES.</td>@endif   
                    @if($datos->conceptos['anticipos'])<td>ANTICIPOS</td>@endif   
                    @if($datos->conceptos['impuesto'])<td>IMP. ÚNICO</td>@endif   
                    @if($datos->conceptos['otros_descuentos'])<td>OTROS DESC.</td>@endif   
                    @if($datos->conceptos['total_descuentos'])<td>TOT. DESC.</td>@endif   
                    @if($datos->conceptos['sueldo_liquido'])<td>ALCANCE LÍQUIDO</td>@endif                    
                </tr>
                @foreach($datos->liquidaciones as $index => $dato)
                    <?php $ind = ($index + 1); ?>
                    @if(($index + 1)%6==0)
                        <?php $secondPage = true; ?>
                    @endif
                    @if(!$secondPage)
                        <tr>
                            <td>
                                {{ Funciones::formatear_rut($dato->trabajador_rut) }}
                            </td>     
                            <td>
                                {{ $dato->trabajador_nombres }} {{ $dato->trabajador_apellidos }}
                            </td>
                            @if($datos->conceptos['sueldo_base'])
                                <td>{{ Funciones::formatoPesos($dato->sueldo_base) }}</td>
                            @endif
                            @if($datos->conceptos['dias_trabajados'])
                                <td>{{ $dato->dias_trabajados }}</td>
                            @endif
                            @if($datos->conceptos['inasistencias'])
                                <td>{{ $dato->inasistencias }}</td>
                            @endif
                            @if($datos->conceptos['horas_extra'])
                                <td>{{ $dato->total_horas_extra }}</td>
                            @endif
                            @if($datos->conceptos['sueldo'])
                                <td>{{ Funciones::formatoPesos($dato->sueldo) }}</td>
                            @endif
                            @if($datos->conceptos['gratificacion'])
                                <td>{{ Funciones::formatoPesos($dato->gratificacion) }}</td>
                            @endif
                            @if($datos->conceptos['total_imponibles'])
                                <td>{{ Funciones::formatoPesos($dato->imponibles) }}</td>
                            @endif
                            @if($datos->conceptos['asignacion_familiar'])
                                <td>{{ Funciones::formatoPesos($dato->total_cargas) }}</td>
                            @endif    
                            @if($datos->conceptos['no_imponibles'])
                                <td>{{ Funciones::formatoPesos($dato->no_imponibles) }}</td>
                            @endif                       
                            @if($datos->conceptos['total_haberes'])
                                <td>{{ Funciones::formatoPesos($dato->total_haberes) }}</td>
                            @endif
                            @if($datos->conceptos['afp'])
                                <td>{{ Funciones::formatoPesos($dato->totalAfp) }}</td>
                            @endif
                            @if($datos->conceptos['apv'])
                                <td>{{ Funciones::formatoPesos($dato->totalApvs) }}</td>
                            @endif
                            @if($datos->conceptos['salud'])
                                <td>{{ Funciones::formatoPesos($dato->totalSalud) }}</td>
                            @endif
                            @if($datos->conceptos['seguro_cesantia'])
                                <td>{{ Funciones::formatoPesos($dato->totalSeguroCesantia) }}</td>
                            @endif
                            @if($datos->conceptos['anticipos'])
                                <td>{{ Funciones::formatoPesos($dato->total_anticipos) }}</td>
                            @endif
                            @if($datos->conceptos['impuesto'])
                                <td>{{ Funciones::formatoPesos($dato->impuesto_determinado) }}</td>
                            @endif
                            @if($datos->conceptos['otros_descuentos'])
                                <td>{{ Funciones::formatoPesos($dato->total_otros_descuentos) }}</td>
                            @endif                                            
                            @if($datos->conceptos['total_descuentos'])
                                <td>{{ Funciones::formatoPesos($dato->total_descuentos) }}</td>
                            @endif
                            @if($datos->conceptos['sueldo_liquido'])
                                <td>{{ Funciones::formatoPesos($dato->sueldo_liquido) }}</td>
                            @endif
                        </tr>    
                    @else
                        @if(($index - 5)%9!=0)
                            <tr>
                                <td>
                                    {{ Funciones::formatear_rut($dato->trabajador_rut) }}
                                </td>     
                                <td>
                                    {{ $dato->trabajador_nombres }} {{ $dato->trabajador_apellidos }}
                                </td>
                                @if($datos->conceptos['sueldo_base'])
                                    <td>{{ Funciones::formatoPesos($dato->sueldo_base) }}</td>
                                @endif
                                @if($datos->conceptos['dias_trabajados'])
                                    <td>{{ $dato->dias_trabajados }}</td>
                                @endif
                                @if($datos->conceptos['inasistencias'])
                                    <td>{{ $dato->inasistencias }}</td>
                                @endif
                                @if($datos->conceptos['horas_extra'])
                                    <td>{{ $dato->total_horas_extra }}</td>
                                @endif
                                @if($datos->conceptos['sueldo'])
                                    <td>{{ Funciones::formatoPesos($dato->sueldo) }}</td>
                                @endif
                                @if($datos->conceptos['gratificacion'])
                                    <td>{{ Funciones::formatoPesos($dato->gratificacion) }}</td>
                                @endif
                                @if($datos->conceptos['total_imponibles'])
                                    <td>{{ Funciones::formatoPesos($dato->imponibles) }}</td>
                                @endif
                                @if($datos->conceptos['asignacion_familiar'])
                                    <td>{{ Funciones::formatoPesos($dato->total_cargas) }}</td>
                                @endif    
                                @if($datos->conceptos['no_imponibles'])
                                    <td>{{ Funciones::formatoPesos($dato->no_imponibles) }}</td>
                                @endif                       
                                @if($datos->conceptos['total_haberes'])
                                    <td>{{ Funciones::formatoPesos($dato->total_haberes) }}</td>
                                @endif
                                @if($datos->conceptos['afp'])
                                    <td>{{ Funciones::formatoPesos($dato->totalAfp) }}</td>
                                @endif
                                @if($datos->conceptos['apv'])
                                    <td>{{ Funciones::formatoPesos($dato->totalApvs) }}</td>
                                @endif
                                @if($datos->conceptos['salud'])
                                    <td>{{ Funciones::formatoPesos($dato->totalSalud) }}</td>
                                @endif
                                @if($datos->conceptos['seguro_cesantia'])
                                    <td>{{ Funciones::formatoPesos($dato->totalSeguroCesantia) }}</td>
                                @endif
                                @if($datos->conceptos['anticipos'])
                                    <td>{{ Funciones::formatoPesos($dato->total_anticipos) }}</td>
                                @endif
                                @if($datos->conceptos['impuesto'])
                                    <td>{{ Funciones::formatoPesos($dato->impuesto_determinado) }}</td>
                                @endif
                                @if($datos->conceptos['otros_descuentos'])
                                    <td>{{ Funciones::formatoPesos($dato->total_otros_descuentos) }}</td>
                                @endif                                                
                                @if($datos->conceptos['total_descuentos'])
                                    <td>{{ Funciones::formatoPesos($dato->total_descuentos) }}</td>
                                @endif
                                @if($datos->conceptos['sueldo_liquido'])
                                    <td>{{ Funciones::formatoPesos($dato->sueldo_liquido) }}</td>
                                @endif
                            </tr>   
                        @else
                            </tbody>
        				</table>
                	</div>
                	<div style="page-break-after: always;"></div> 
                	<div class="page">
                	    <table id="libro">
                            <tbody>
                                <tr class="head">                    
                                    <td>RUT</td>   
                                    <td>NOMBRE</td>   
                                    @if($datos->conceptos['sueldo_base'])<td>S. BASE</td>@endif
                                    @if($datos->conceptos['dias_trabajados'])<td>DT</td>@endif  
                                    @if($datos->conceptos['inasistencias'])<td>INASIST.</td>@endif       
                                    @if($datos->conceptos['horas_extra'])<td>H. EXTRAS</td>@endif 
                                    @if($datos->conceptos['sueldo'])<td>SUELDO</td>@endif        
                                    @if($datos->conceptos['gratificacion'])<td>GRAT. LEGAL</td>@endif  
                                    @if($datos->conceptos['total_imponibles'])<td>TOTAL IMP.</td>@endif  
                                    @if($datos->conceptos['asignacion_familiar'])<td>ASIG. FAM.</td>@endif 
                                    @if($datos->conceptos['no_imponibles'])<td>TOT. NO IMP.</td>@endif       
                                    @if($datos->conceptos['total_haberes'])<td>TOT. HABERES</td>@endif  
                                    @if($datos->conceptos['afp'])<td>AFP</td>@endif      
                                    @if($datos->conceptos['apv'])<td>APV</td>@endif   
                                    @if($datos->conceptos['salud'])<td>SALUD</td>@endif     
                                    @if($datos->conceptos['seguro_cesantia'])<td>SEG. CES.</td>@endif   
                                    @if($datos->conceptos['anticipos'])<td>ANTICIPOS</td>@endif   
                                    @if($datos->conceptos['impuesto'])<td>IMP. ÚNICO</td>@endif   
                                    @if($datos->conceptos['otros_descuentos'])<td>OTROS DESC.</td>@endif   
                                    @if($datos->conceptos['total_descuentos'])<td>TOT. DESC.</td>@endif   
                                    @if($datos->conceptos['sueldo_liquido'])<td>ALCANCE LÍQUIDO</td>@endif                    
                                </tr>
                                <tr>
                                    <td>
                                        {{ Funciones::formatear_rut($dato->trabajador_rut) }}
                                    </td>     
                                    <td>
                                        {{ $dato->trabajador_nombres }} {{ $dato->trabajador_apellidos }}
                                    </td>
                                    @if($datos->conceptos['sueldo_base'])
                                        <td>{{ Funciones::formatoPesos($dato->sueldo_base) }}</td>
                                    @endif
                                    @if($datos->conceptos['dias_trabajados'])
                                        <td>{{ $dato->dias_trabajados }}</td>
                                    @endif
                                    @if($datos->conceptos['inasistencias'])
                                        <td>{{ $dato->inasistencias }}</td>
                                    @endif
                                    @if($datos->conceptos['horas_extra'])
                                        <td>{{ $dato->total_horas_extra }}</td>
                                    @endif
                                    @if($datos->conceptos['sueldo'])
                                        <td>{{ Funciones::formatoPesos($dato->sueldo) }}</td>
                                    @endif
                                    @if($datos->conceptos['gratificacion'])
                                        <td>{{ Funciones::formatoPesos($dato->gratificacion) }}</td>
                                    @endif
                                    @if($datos->conceptos['total_imponibles'])
                                        <td>{{ Funciones::formatoPesos($dato->imponibles) }}</td>
                                    @endif
                                    @if($datos->conceptos['asignacion_familiar'])
                                        <td>{{ Funciones::formatoPesos($dato->total_cargas) }}</td>
                                    @endif    
                                    @if($datos->conceptos['no_imponibles'])
                                        <td>{{ Funciones::formatoPesos($dato->no_imponibles) }}</td>
                                    @endif                       
                                    @if($datos->conceptos['total_haberes'])
                                        <td>{{ Funciones::formatoPesos($dato->total_haberes) }}</td>
                                    @endif
                                    @if($datos->conceptos['afp'])
                                        <td>{{ Funciones::formatoPesos($dato->totalAfp) }}</td>
                                    @endif
                                    @if($datos->conceptos['apv'])
                                        <td>{{ Funciones::formatoPesos($dato->totalApvs) }}</td>
                                    @endif
                                    @if($datos->conceptos['salud'])
                                        <td>{{ Funciones::formatoPesos($dato->totalSalud) }}</td>
                                    @endif
                                    @if($datos->conceptos['seguro_cesantia'])
                                        <td>{{ Funciones::formatoPesos($dato->totalSeguroCesantia) }}</td>
                                    @endif
                                    @if($datos->conceptos['anticipos'])
                                        <td>{{ Funciones::formatoPesos($dato->total_anticipos) }}</td>
                                    @endif
                                    @if($datos->conceptos['impuesto'])
                                        <td>{{ Funciones::formatoPesos($dato->impuesto_determinado) }}</td>
                                    @endif
                                    @if($datos->conceptos['otros_descuentos'])
                                        <td>{{ Funciones::formatoPesos($dato->total_otros_descuentos) }}</td>
                                    @endif                                                
                                    @if($datos->conceptos['total_descuentos'])
                                        <td>{{ Funciones::formatoPesos($dato->total_descuentos) }}</td>
                                    @endif
                                    @if($datos->conceptos['sueldo_liquido'])
                                        <td>{{ Funciones::formatoPesos($dato->sueldo_liquido) }}</td>
                                    @endif
                                </tr>
                        @endif
                    @endif
                @endforeach      
                @if($secondPage)
                    <?php 
                    $ind = ($ind - 5);
                    $totales = ($ind % 9==0); ?>
                @else
                    <?php $totales = ($ind % 5==0); ?>
                @endif
                @if($totales)
                    </tbody>
        				</table>
                	</div>
                	<div style="page-break-after: always;"></div> 
                	<div class="page">
                	    <table id="libro">
                            <tbody>
                                <tr class="head">                    
                                    <td></td>   
                                    <td></td>   
                                    @if($datos->conceptos['sueldo_base'])<td>S. BASE</td>@endif
                                    @if($datos->conceptos['dias_trabajados'])<td>DT</td>@endif  
                                    @if($datos->conceptos['inasistencias'])<td>INASIST.</td>@endif       
                                    @if($datos->conceptos['horas_extra'])<td>H. EXTRAS</td>@endif 
                                    @if($datos->conceptos['sueldo'])<td>SUELDO</td>@endif        
                                    @if($datos->conceptos['gratificacion'])<td>GRAT. LEGAL</td>@endif  
                                    @if($datos->conceptos['total_imponibles'])<td>TOTAL IMP.</td>@endif  
                                    @if($datos->conceptos['asignacion_familiar'])<td>ASIG. FAM.</td>@endif 
                                    @if($datos->conceptos['no_imponibles'])<td>TOT. NO IMP.</td>@endif       
                                    @if($datos->conceptos['total_haberes'])<td>TOT. HABERES</td>@endif  
                                    @if($datos->conceptos['afp'])<td>AFP</td>@endif      
                                    @if($datos->conceptos['apv'])<td>APV</td>@endif   
                                    @if($datos->conceptos['salud'])<td>SALUD</td>@endif     
                                    @if($datos->conceptos['seguro_cesantia'])<td>SEG. CES.</td>@endif   
                                    @if($datos->conceptos['anticipos'])<td>ANTICIPOS</td>@endif   
                                    @if($datos->conceptos['impuesto'])<td>IMP. ÚNICO</td>@endif   
                                    @if($datos->conceptos['otros_descuentos'])<td>OTROS DESC.</td>@endif   
                                    @if($datos->conceptos['total_descuentos'])<td>TOT. DESC.</td>@endif   
                                    @if($datos->conceptos['sueldo_liquido'])<td>ALCANCE LÍQUIDO</td>@endif                    
                                </tr>
                @endif
                <tr id="totales">
                    <td id="totalGeneral" colspan="2" rowspan="2"><b>TOTAL GENERAL:</b></td>
                    @if($datos->conceptos['sueldo_base'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSueldoBase']) }}</b></td>
                    @endif 
                    <td></td>
                    @if($datos->conceptos['inasistencias'])
                        <td><b>{{ $datos->totales['totalInasistenciasAtrasos'] }}</b></td>
                    @endif 
                    <td></td>
                    @if($datos->conceptos['sueldo'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSueldo']) }}</b></td>
                    @endif 
                    <td></td>
                    @if($datos->conceptos['total_imponibles'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalImponibles']) }}</b></td>
                    @endif 
                    <td></td>
                    @if($datos->conceptos['no_imponibles'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalNoImponibles']) }}</b></td>
                    @endif 
                    <td></td>
                    @if($datos->conceptos['afp'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalAfp']) }}</b></td>
                    @endif  
                    <td></td>
                    @if($datos->conceptos['salud'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalSalud']) }}</b></td>
                    @endif 
                    <td></td>
                    @if($datos->conceptos['anticipos'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalAnticipos']) }}</b></td>
                    @endif 
                    <td></td>
                    @if($datos->conceptos['otros_descuentos'])
                        <td><b>{{ Funciones::formatoPesos($datos->totales['totalOtrosDescuentos']) }}</b></td>
                    @endif  
                    <td></td>
                    @if($datos->conceptos['sueldo_liquido'])
                        <td id="liquido" rowspan="2"><b>{{ Funciones::formatoPesos($datos->totales['totalSueldoLiquido']) }}</b></td>
                    @endif 
                    <tr id="bottom">
                        <td></td>
                        @if(!$local)
                            <td></td>
                        @endif
                        @if($datos->conceptos['dias_trabajados'])
                            <td><b>{{ $datos->totales['totalDiasTrabajados'] }}</b></td>
                        @endif
                        <td></td>
                        @if($datos->conceptos['horas_extra'])
                            <td><b>{{ $datos->totales['totalHorasExtra'] }}</b></td>
                        @endif 
                        <td></td>
                        @if($datos->conceptos['gratificacion'])
                            <td><b>{{ Funciones::formatoPesos($datos->totales['totalGratificacion']) }}</b></td>
                        @endif
                        <td></td>
                        @if($datos->conceptos['asignacion_familiar'])
                            <td><b>{{ Funciones::formatoPesos($datos->totales['totalAsignacionFamiliar']) }}</b></td>
                        @endif 
                        <td></td>
                        @if($datos->conceptos['total_haberes'])
                            <td><b>{{ Funciones::formatoPesos($datos->totales['totalHaberes']) }}</b></td>
                        @endif 
                        <td></td>
                        @if($datos->conceptos['apv'])
                            <td><b>{{ Funciones::formatoPesos($datos->totales['totalApv']) }}</b></td>
                        @endif 
                        <td></td>
                        @if($datos->conceptos['seguro_cesantia'])
                            <td><b>{{ Funciones::formatoPesos($datos->totales['totalSeguroCesantia']) }}</b></td>
                        @endif 
                        <td></td>
                        @if($datos->conceptos['impuesto'])
                            <td><b>{{ Funciones::formatoPesos($datos->totales['totalImpuestoRenta']) }}</b></td>
                        @endif 
                        <td></td>
                        @if($datos->conceptos['total_descuentos'])
                            <td><b>{{ Funciones::formatoPesos($datos->totales['totalTotalDescuentos']) }}</b></td>
                        @endif        
                    </tr>
                </tr>
            </tbody> 
        </table>
</div>
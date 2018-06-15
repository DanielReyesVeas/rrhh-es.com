<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Archivo Previred</title>
  <style type="text/css">     
      table td{
          width: 12px;
      }
      .nombres{
          width: 20px;
      }
  </style>
    
</head>
<body>
    
    <table>
        <thead>
            <tr>
                <th>RUT</th>                     
                <th>Dígito</th>                     
                <th>Apellido Paterno</th>                     
                <th>Apellido Materno</th>                     
                <th>Nombres</th>                     
                <th>Sexo</th>                     
                <th>Nacionalidad</th>                     
                <th>Tipo Pago</th>                     
                <th>Periodo (desde)</th>                     
                <th>Periodo (hasta)</th>                     
                <th>Régimen Previsional</th>                     
                <th>Tipo Trabajador</th>                     
                <th>Días Trabajados</th>                     
                <th>Tipo de Línea</th>                     
                <th>Movimiento de Personal</th>                     
                <th>Fecha Desde</th>                     
                <th>Fecha Hasta</th>                     
                <th>Tramo</th>                     
                <th>N° Cargas Simples</th>                     
                <th>N° Cargas Maternales</th>                     
                <th>N° Cargas Inválidas</th>                     
                <th>Asignación Familiar</th>                     
                <th>Asignación Familiar Retroactiva</th>                     
                <th>Reintegro Cargas Familiares</th>                     
                <th>Solicitud Trabajador Joven</th>                     
                
                <th>Código de la AFP</th>                     
                <th>Renta Imponible AFP</th>                     
                <th>Cotización Obligatoria AFP</th>                     
                <th>SIS</th>                     
                <th>Cuenta de Ahorro Voluntario AFP</th>    
                <th>Renta Sustitutiva</th>    
                <th>Tasa Sustitutiva</th>    
                <th>Aporte Sustitutiva</th>    
                <th>Número Periodos</th>    
                <th>Período Desde</th>    
                <th>Período Hasta</th>    
                <th>Puesto Trabajo Pesado</th>    
                <th>% Trabajo Pesado</th>    
                <th>Cotizacion Trabajo Pesado</th>
                
                <th>Código APVI</th>
                <th>Número Contrato APVI</th>
                <th>Forma Pago APVI</th>
                <th>Cotización APVI</th>
                <th>Cotización Depósitos Convenidos</th>
                
                <th>Código APVC</th>
                <th>Número Contrato APVC</th>
                <th>Forma Pago APVC</th>
                <th>Cotización Trabajador APVC</th>
                <th>Cotización Empleador APVC</th>
                
                <th>RUT Afiliado Voluntario</th>
                <th>DV Afiliado Voluntario</th>
                <th>Apellido Paterno Afiliado Voluntario</th>
                <th>Apellido Materno Afiliado Voluntario</th>
                <th>Nombres Afiliado Voluntario</th>
                <th>Código Movimiento Personal</th>
                <th>Fecha Desde</th>
                <th>Fecha Hasta</th>
                <th>Código AFP</th>
                <th>Monto Capitalización Voluntaria</th>
                <th>Monto Ahorro Voluntaro</th>
                <th>Número Periodos Cotización</th>
                
                <th>Código Ex Caja</th>
                <th>Tasa Cotización Ex Caja</th>
                <th>Renta Imponible IPS</th>
                <th>Cotización Obligatoria IPS</th>
                <th>Renta Imponible Desahucio</th>
                <th>Código Ex Caja Desahucio</th>
                <th>Tasa Desahucio</th>
                <th>Cotización Desahucio</th>
                <th>Cotización Fonasa</th>
                <th>Cotización ISL</th>
                <th>Bonificación Ley 15.386</th>
                <th>Descuento Cargas ISL</th>
                <th>Bonos Gobierno</th>
                
                <th>Código Institución de Salud</th>
                <th>Número FUN</th>
                <th>Renta Imponible Isapre</th>
                <th>Moneda Plan Isapre</th>
                <th>Cotización Pactada</th>
                <th>Cotización Obligatoria</th>
                <th>Cotización Adicional</th>
                <th>Monto Garantía Explícita</th>
                
                <th>Código CCAF</th>
                <th>Renta ImponibleCCAF</th>
                <th>Creditos Personales CCAF</th>
                <th>Descuento Dental CCAF</th>
                <th>Descuentos Leasing</th>
                <th>Descuentos Seguro CCAF</th>
                <th>Otros Descuentos CCAF</th>
                <th>Cotización CCAF No Afiliados a Isapre</th>
                <th>Descuento Cargas Familiares CCAF</th>
                <th>Otros Descuentos CCAF 1</th>
                <th>Otros Descuentos CCAF 2</th>
                <th>Bonos Gobierno</th>
                <th>Código Sucursal</th>
                
                <th>Código Mutualidad</th>
                <th>Renta Imponible Mutual</th>
                <th>Cotizacion Accidente Trabajo</th>
                <th>Sucursal Pago Mutual</th>
                
                <th>Renta Imponible Seguro Cesantía</th>
                <th>Aporte Trabajador Seguro Cesantía</th>
                <th>Aporte Empleador Seguro Cesantía</th>
                
                <th>RUT Pagadora de Subsidio</th>
                <th>DV Pagadora de Subsidio</th>
                
                <th>Centro de Costos, Sucursal, Agencia, Obra, Región</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listaTrabajadores as $dato)
                <tr>
                    <td>{{ $dato['rutSinDigito'] }}</td>
                    <td>{{ $dato['rutDigito'] }}</td>
                    <td class="nombres">{{ $dato['apellidoPaterno'] }}</td>
                    <td class="nombres">{{ $dato['apellidoMaterno'] }}</td>
                    <td class="nombres">{{ $dato['nombres'] }}</td>
                    <td>{{ $dato['sexo'] }}</td>
                    <td>{{ $dato['nacionalidad'] }}</td>
                    <td>{{ $dato['tipoPago'] }}</td>
                    <td>{{ $dato['periodoDesde'] }}</td>
                    <td>{{ $dato['periodoHasta'] }}</td>
                    <td>{{ $dato['regimenPrevisional'] }}</td>
                    <td>{{ $dato['tipoTrabajador'] }}</td>
                    <td>{{ $dato['diasTrabajados'] }}</td>
                    <td>{{ $dato['tipoLinea'] }}</td>
                    <td>{{ $dato['movimientoPersonal'] }}</td>
                    <td>{{ $dato['movimientoPersonalDesde'] }}</td>
                    <td>{{ $dato['movimientoPersonalHasta'] }}</td>
                    <td>{{ $dato['tramo'] }}</td>
                    <td>{{ $dato['cargasSimples'] }}</td>
                    <td>{{ $dato['cargasMaternales'] }}</td>
                    <td>{{ $dato['cargasInvalidas'] }}</td>
                    <td>{{ $dato['asignacionFamiliar'] }}</td>
                    <td>{{ $dato['asignacionFamiliarRetroactiva'] }}</td>
                    <td>{{ $dato['reintegroCargasFamiliares'] }}</td>
                    <td>{{ $dato['solicitudTrabajadorJoven'] }}</td>
                    
                    <td>{{ $dato['codigoAfp'] }}</td>
                    <td>{{ $dato['rentaImponible'] }}</td>
                    <td>{{ $dato['cotizacionAfp'] }}</td>
                    <td>{{ $dato['sis'] }}</td>
                    <td>{{ $dato['cuentaAhorroVoluntario'] }}</td>
                    <td>{{ $dato['rentaSustitutiva'] }}</td>
                    <td>{{ $dato['tasaSustitutiva'] }}</td>
                    <td>{{ $dato['aporteSustitutiva'] }}</td>
                    <td>{{ $dato['numeroPeriodos'] }}</td>
                    <td>{{ $dato['periodoDesdeSustit'] }}</td>
                    <td>{{ $dato['periodoHastaSustit'] }}</td>
                    <td>{{ $dato['puestoTrabajoPesado'] }}</td>
                    <td>{{ $dato['porcentajeTrabajoPesado'] }}</td>
                    <td>{{ $dato['cotizacionTrabajoPesado'] }}</td>
                    
                    <td>{{ $dato['codigoAPVI'] }}</td>
                    <td>{{ $dato['numeroContratoAPVI'] }}</td>
                    <td>{{ $dato['formaPagoAPVI'] }}</td>
                    <td>{{ $dato['cotizacionAPVI'] }}</td>
                    <td>{{ $dato['cotizacionDepositosConvenidos'] }}</td>
                    
                    <td>{{ $dato['codigoAPVC'] }}</td>
                    <td>{{ $dato['numeroContratoAPVC'] }}</td>
                    <td>{{ $dato['formaPagoAPVC'] }}</td>
                    <td>{{ $dato['cotizacionTrabajadorAPVC'] }}</td>
                    <td>{{ $dato['cotizacionEmpleadorAPVC'] }}</td>                   
                    
                    <td>{{ $dato['rutAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['dvAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['apellidoPaternoAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['apellidoMaternoAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['nombresAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['codigoMovimientoPersonalAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['fechaDesdeAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['fechaHastaAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['codigoAfpAfiliadoVoluntario'] }}</td>
                    <td>{{ $dato['montoCapitalizacionVoluntaria'] }}</td>
                    <td>{{ $dato['montoAhorroVoluntario'] }}</td>
                    <td>{{ $dato['numeroPeriodosCotizacion'] }}</td> 
                    
                    <td>{{ $dato['codigoExCaja'] }}</td>
                    <td>{{ $dato['tasaCotizacionExCaja'] }}</td>
                    <td>{{ $dato['rentaImponibleIps'] }}</td>
                    <td>{{ $dato['cotizacionObligatoriaIps'] }}</td>
                    <td>{{ $dato['rentaImponibleDesahucio'] }}</td>
                    <td>{{ $dato['codigoExCajaDesahucio'] }}</td>
                    <td>{{ $dato['tasaDesahucio'] }}</td>
                    <td>{{ $dato['cotizacionDesahucio'] }}</td>
                    <td>{{ $dato['cotizacionFonasa'] }}</td>
                    <td>{{ $dato['cotizacionIsl'] }}</td>
                    <td>{{ $dato['bonificacion15386'] }}</td>
                    <td>{{ $dato['descuentoCargasIsl'] }}</td>
                    <td>{{ $dato['bonosGobierno'] }}</td>
                                        
                    <td>{{ $dato['codigoInstitucionSalud'] }}</td>
                    <td>{{ $dato['numeroFun'] }}</td>
                    <td>{{ $dato['rentaImponibleIsapre'] }}</td>
                    <td>{{ $dato['monedaPlanIsapre'] }}</td>
                    <td>{{ $dato['cotizacionPactada'] }}</td>
                    <td>{{ $dato['cotizacionObligatoria'] }}</td>
                    <td>{{ $dato['cotizacionAdicional'] }}</td>
                    <td>{{ $dato['montoGarantiaExplicita'] }}</td>
                                        
                    <td>{{ $dato['codigoCcaf'] }}</td>
                    <td>{{ $dato['rentaImponibleCcaf'] }}</td>
                    <td>{{ $dato['creditosPersonalesCcaf'] }}</td>
                    <td>{{ $dato['descuentoDentalCcaf'] }}</td>
                    <td>{{ $dato['descuentosLeasing'] }}</td>
                    <td>{{ $dato['descuentosSeguroCcaf'] }}</td>
                    <td>{{ $dato['otrosDescuentosCcaf'] }}</td>
                    <td>{{ $dato['cotizacionCcafNoAfiliadosIsapre'] }}</td>
                    <td>{{ $dato['descuentoCargasFamiliaresCcaf'] }}</td>
                    <td>{{ $dato['otrosDescuentosCcaf1'] }}</td>
                    <td>{{ $dato['otrosDescuentosCcaf2'] }}</td>
                    <td>{{ $dato['bonosGobierno'] }}</td>
                    <td>{{ $dato['codigoSucursal'] }}</td>
                                        
                    <td>{{ $dato['codigoMutualidad'] }}</td>
                    <td>{{ $dato['rentaImponibleMutual'] }}</td>
                    <td>{{ $dato['cotizacionAccidenteTrabajo'] }}</td>
                    <td>{{ $dato['sucursalPagoMutual'] }}</td>
                    <td>{{ $dato['rentaImponibleSeguroCesantia'] }}</td>
                    <td>{{ $dato['aporteTrabajadorSeguroCesantia'] }}</td>
                    <td>{{ $dato['aporteEmpleadorSeguroCesantia'] }}</td>
                    
                    <td>{{ $dato['rutPagadoraSubsidio'] }}</td>
                    <td>{{ $dato['dvPagadoraSubsidio'] }}</td>
                    <td>{{ $dato['centroCosto'] }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>
    
</body>
</html>
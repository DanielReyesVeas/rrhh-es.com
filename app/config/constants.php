<?php
return array(
    'BDEmpresa' => 'cmees_grupodaso_787022103',
    'mensajes' => array(
        'store' => array(
            'ok' => 'La información fue almacenada correctamente',
            'error' => 'Hubo un problema al momento de almacenar la información'
        ),
        'update' => array(
            'ok' => 'La información fue actualizada correctamente',
            'error' => 'Hubo un problema al momento de actualizar la información'
        ),
        'delete' => array(
            'ok' => 'La información fue eliminada correctamente',
            'error' => 'Hubo un problema al momento de eliminadar la información'
        )
    ),
    'columnasLibroCompraXML' => array(
        'MntExe' => array( 'subcampo' => false, 'campo' => 'mnt_exe', 'label' => 'MONTO EXENTO O NO GRAVADO', 'var' => 'CUENTAS_LIBRO_COMPRA_EXENTO'),
        'MntNeto' => array( 'subcampo' => false, 'campo' => 'mnt_neto', 'label' => 'MONTO NETO', 'var' => 'CUENTAS_LIBRO_COMPRA_NETO'),
        'MntIVA' => array( 'subcampo' => false, 'campo' => 'mnt_iva', 'label' => 'MONTO IVA (RECUPERABLE)', 'var' => 'CUENTAS_LIBRO_COMPRA_IVA'),
        'MntActivoFijo' => array( 'subcampo' => false, 'campo' => 'mnt_activo_fijo', 'label' => 'MONTO NETO ACTIVO FIJO', 'var' => 'CUENTAS_LIBRO_COMPRA_ACTIVO_FIJO'),
        'MntIVAActivoFijo' => array( 'subcampo' => false, 'campo' => 'mnt_iva_activo_fijo', 'label' => 'IVA ACTIVO FIJO', 'var' => 'CUENTAS_LIBRO_COMPRA_IVA_ACTIVO_FIJO'),
        'IVANoRec' => array( 'subcampo' => true, 'campo' => 'iva_no_rec', 'label' => 'IVA no recuperable', 'var' => 'CUENTAS_LIBRO_COMPRA_IVA_NO_RECUPERABLE'),
        'IVAUsoComun' => array( 'subcampo' => false, 'campo' => 'iva_uso_comun', 'label' => 'IVA USO COMUN', 'var' => 'CUENTAS_LIBRO_COMPRA_IVA_USO_COMUN'),
        'OtrosImp' => array( 'subcampo' => true, 'campo' => 'otros_imp', 'label' => 'Impuesto o recargo', 'var' => 'CUENTAS_LIBRO_COMPRA_OTROS_IMP'),
        'MntSinCred' => array( 'subcampo' => false, 'campo' => 'mnt_sin_cred', 'label' => 'Impuestos sin derecho a crédito', 'var' => 'CUENTAS_LIBRO_COMPRA_SIN_CREDITO'),
        'MntTotal' => array( 'subcampo' => false, 'campo' => 'mnt_total', 'label' => 'MONTO TOTAL', 'var' => 'CUENTAS_LIBRO_COMPRA_TOTAL'),
        'IVANoRetenido' => array( 'subcampo' => false, 'campo' => 'iva_no_retenido', 'label' => 'IVA no retenido', 'var' => 'CUENTAS_LIBRO_COMPRA_IVA_NO_RETENIDO'),
        'TabPuros' => array( 'subcampo' => false, 'campo' => 'tab_puros', 'label' => 'TABACOS (Cigarros puros)', 'var' => 'CUENTAS_LIBRO_COMPRA_TAB_PUROS'),
        'TabCigarrillos' => array( 'subcampo' => false, 'campo' => 'tab_cigarrillos', 'label' => 'TABACOS (Cigarrillos)', 'var' => 'CUENTAS_LIBRO_COMPRA_TAB_CIGARROS'),
        'TabElaborado' => array( 'subcampo' => false, 'campo' => 'tab_elaborado', 'label' => 'TABACOS (Tabaco elaborado)', 'var' => 'CUENTAS_LIBRO_COMPRA_TAB_ELABORADO'),
        'ImpVehiculo' => array( 'subcampo' => false, 'campo' => 'imp_vehiculo', 'label' => 'Impuesto a Los Vehículos Automóviles', 'var' => 'CUENTAS_LIBRO_COMPRA_IMP_VEHICULO')
    ),
    'columnasLibroVentaXML' => array(
        'MntExe' => array( 'subcampo' => false, 'campo' => 'mnt_exe', 'label' => 'MONTO EXENTO O NO GRAVADO', 'var' => 'CUENTAS_LIBRO_VENTA_EXENTO'),
        'MntNeto' => array( 'subcampo' => false, 'campo' => 'mnt_neto', 'label' => 'MONTO NETO', 'var' => 'CUENTAS_LIBRO_VENTA_NETO'),
        'MntIVA' => array( 'subcampo' => false, 'campo' => 'mnt_iva', 'label' => 'MONTO IVA', 'var' => 'CUENTAS_LIBRO_VENTA_IVA'),
        'IVAFueraPlazo' => array( 'subcampo' => false, 'campo' => 'iva_fuera_plazo', 'label' => 'IVA fuera plazo', 'var' => 'CUENTAS_LIBRO_VENTA_IVA_FUERA_PLAZO'),
        'IVAPropio' => array( 'subcampo' => false, 'campo' => 'iva_propio', 'label' => 'IVA Propio', 'var' => 'CUENTAS_LIBRO_VENTA_IVA_PROPIO'),
        'IVATerceros' => array( 'subcampo' => false, 'campo' => 'iva_terceros', 'label' => 'IVA Terceros', 'var' => 'CUENTAS_LIBRO_VENTA_IVA_TERCEROS'),
        'Ley18211' => array( 'subcampo' => false, 'campo' => 'ley_18211', 'label' => 'Ley 18211', 'var' => 'CUENTAS_LIBRO_VENTA_LEY_18211'),
        'OtrosImp' => array( 'subcampo' => true, 'campo' => 'otros_imp', 'label' => 'Impuesto o recargo', 'var' => 'CUENTAS_LIBRO_VENTA_OTROS_IMP'),
        'IVARetTotal' => array( 'subcampo' => false, 'campo' => 'iva_ret_total', 'label' => 'IVA Retenido Total', 'var' => 'CUENTAS_LIBRO_VENTA_IVA_RET_TOTAL'),
        'IVARetParcial' => array( 'subcampo' => false, 'campo' => 'iva_ret_parcial', 'label' => 'IVA Retenido Parcial', 'var' => 'CUENTAS_LIBRO_VENTA_IVA_RET_PARCIAL'),
        'CredEC' => array( 'subcampo' => false, 'campo' => 'cred_ec', 'label' => 'Crédito 65% Empresas Constructoras', 'var' => 'CUENTAS_LIBRO_VENTA_CRED_EC'),
        'DepEnvase' => array( 'subcampo' => false, 'campo' => 'dep_envase', 'label' => 'Garantía Dep. Envases', 'var' => 'CUENTAS_LIBRO_VENTA_DEP_ENVASE'),
        'Liquidaciones' => array( 'subcampo' => false, 'campo' => 'liquidaciones', 'label' => 'Liquidaciones', 'var' => 'CUENTAS_LIBRO_VENTA_LIQUIDACIONES'),
        'MntTotal' => array( 'subcampo' => false, 'campo' => 'mnt_total', 'label' => 'MONTO TOTAL', 'var' => 'CUENTAS_LIBRO_VENTA_TOTAL'),
        'IVANoRetenido' => array( 'subcampo' => false, 'campo' => 'iva_no_retenido', 'label' => 'IVA no retenido', 'var' => 'CUENTAS_LIBRO_VENTA_IVA_NO_RETENIDO'),
        'MntNoFact' => array( 'subcampo' => false, 'campo' => 'mnt_no_fact', 'label' => 'Total Monto no facturable', 'var' => 'CUENTAS_LIBRO_VENTA_NO_FACTURABLE'),
    /*
        'MntPeriodo' => array( 'campo' => 'mnt_periodo', 'label' => 'Total Monto Período', 'var' => 'CUENTAS_LIBRO_VENTA_PERIODO'),
    */
        'PsjNac' => array( 'campo' => 'psj_nac', 'label' => 'Venta de pasajes Transporte nacional', 'var' => 'CUENTAS_LIBRO_VENTA_PSJ_NAC'),
        'PsjInt' => array( 'campo' => 'psj_int', 'label' => 'Venta de pasajes Transporte internacional', 'var' => 'CUENTAS_LIBRO_VENTA_PSJ_INT')
    ),
    'columnasLibroBoleta' => array(
        'MntExe' => array( 'campo' => 'mnt_exe', 'label' => 'MONTO EXENTO O NO GRAVADO', 'var' => 'CUENTAS_LIBRO_BOLETA_EXENTO'),
        'MntNeto' => array( 'campo' => 'mnt_neto', 'label' => 'MONTO NETO', 'var' => 'CUENTAS_LIBRO_BOLETA_NETO'),
        'MntIVA' => array( 'campo' => 'mnt_iva', 'label' => 'MONTO IVA', 'var' => 'CUENTAS_LIBRO_BOLETA_IVA'),
        'MntTotal' => array( 'campo' => 'mnt_total', 'label' => 'MONTO TOTAL', 'var' => 'CUENTAS_LIBRO_BOLETA_TOTAL')
    ),
    'codigosImpuestos' => array(
        '1' => 'Compras destinadas a IVA a generar operaciones no gravadas o exentas',
        '2' => 'Facturas de proveedores registradas fuera de plazo',
        '3' => 'Gastos rechazados',
        '4' => 'Entregas gratuitas (premios, bonificaciones etc.) recibidas',
        '9' => 'Otros',

        '14' => 'IVA de margen de comercialización',
        '15' => 'IVA retenido total',
        '17' => 'IVA ANTICIPADO FAENAMIENTO CARNE',
        '18' => 'IVA ANTICIPADO CARNE',
        '19' => 'IVA ANTICIPADO HARINA',
        '30' => 'IVA RETENIDO LEGUMBRES',
        '301' => 'IVA RETENIDO LEGUMBRES',
        '31' => 'IVA RETENIDO SILVESTRES',
        '32' => 'IVA RETENIDO GANADO',
        '321' => 'IVA RETENIDO GANADO',
        '33' => 'IVA RETENIDO MADERA',
        '331' => 'IVA RETENIDO MADERA',
        '34' => 'IVA RETENIDO TRIGO',
        '341' => 'IVA RETENIDO TRIGO',
        '36' => 'IVA RETENIDO ARROZ',
        '361' => 'IVA RETENIDO ARROZ',
        '37' => 'IVA RETENIDO HIDROBIOLOGICAS',
        '371' => 'IVA RETENIDO HIDROBIOLOGICAS',
        '38' => 'IVA RETENIDO CHATARRA',
        '39' => 'IVA RETENIDO PPA',
        '41' => 'IVA RETENIDO CONSTRUCCION',
        '23' => 'IMPUESTO ADICIONAL ART 37 LETRAS A, B, C',
        '44' => 'IMPUESTO ADICIONAL ART 37 LETRAS E, H, I, L',
        '45' => 'IMPUESTO ADICIONAL ART 37 LETRAS J',
        '24' => 'IMPUESTO ART. 42, LEY DE IVA LETRA B)',
        '25' => 'IMPUESTO ART. 42, LEY DE IVA LETRA C)',
        '26' => 'IMPUESTO ART. 42, LEY DE IVA LETRA C)',
        '27' => 'IMPUESTO ART. 42, LEY DE IVA LETRA A)',
        '271' => 'IMPUESTO ART. 42, LEY DE IVA LETRA A) PÁRRAFO 2°',
        '28' => 'IMPUESTO ESPECÍFICO DIÉSEL',
        '29' => 'RECUPERACIÓN IMPUESTO ESPECÍFICO RESULTANTE AL DIÉSEL TRANSPORTISTAS.',
        '35' => 'IMPUESTO ESPECÍFICO GASOLINA',
        '47' => 'IVA RETENIDO CARTONES',
        '48' => 'IVA RETENIDO FRAMBUESAS Y PASAS',
        '481' => 'IVA RETENIDO FRAMBUESAS Y PASAS',
        '49' => 'FACTURA DE COMPRA SIN RETENCIÓN (SÓLO BOLSA DE PRODUCTOS DE CHILE)',
        '50' => 'IVA DE MARGEN DE COMERCIALIZACIÓN DE INSTRUMENTOS DE PREPAGO',
        '51' => 'IMPUESTO GAS NATURAL COMPRIMIDO',
        '52' => 'IMPUESTO GAS LICUADO DE PETRÓLEO',
        '53' => 'IMPUESTO RETENIDO SUPLEMENTEROS ART 74 N°5 LEY DE LA RENTA',
        '60' => 'IMPUESTO RETENIDO FACTURA DE INICIO'
    ),
    'grupos_segmentaciones' => array(
        array( 'id' => 1, 'value' => 'Segmentación 1' ),
        array( 'id' => 2, 'value' => 'Segmentación 2' ),
        array( 'id' => 3, 'value' => 'Segmentación LP' )
    ),
    'tipos_pagos' => array(
        array('id' => 1, 'tipo' => 'Efectivo', 'campos' => array(
            array(
                'campo' => 'Ninguno',
                'descripcion' => 'Ninguno'
            )
        )),
        array('id' => 2, 'tipo' => 'Cheque / Vale Vista', 'campos' => array(
            array(
                'campo' => 'Fecha',
                'descripcion' => 'Fecha del Cheque'
            ),
            array(
                'campo' => 'Numero',
                'descripcion' => 'Numero del Cheque'
            ),
            array(
                'campo' => 'Banco',
                'descripcion' => 'Banco del Cheque, según lista ingresada en <b>Parámetros &#9658; Bancos</b>'
            )
        )),
        array('id' => 4, 'tipo' => 'Transferencia', 'campos' => array(
            array(
                'campo' => 'Fecha',
                'descripcion' => 'Fecha de Transacción'
            ),
            array(
                'campo' => 'Numero',
                'descripcion' => 'Numero de Transacción'
            ),
            array(
                'campo' => 'Cuenta Deposito',
                'descripcion' => 'Cuenta de Deposito, según lista ingresada en <b>Parámetros &#9658; Cuentas Depósitos</b>'
            )
        )),
        array('id' => 5, 'tipo' => 'Nómina', 'campos' => array(
            array(
                'campo' => 'Nomina de Pago',
                'descripcion' => 'Nomina de Pagos, según lista ingresada en <b>Parámetros &#9658; Nóminas de Pagos</b>'
            )
        ))
    ),
    'tipos_productos' => array(
        array( 'id' => 1, 'value' => 'Inventariable' ),
        array( 'id' => 2, 'value' => 'Activo Fijo' ),
        array( 'id' => 3, 'value' => 'Consumo' )
    ),
    'tipos_departamento' => array(
        array( 'id' => 2, 'value' => 'Departamento' ),
        array( 'id' => 3, 'value' => 'Oficina' )
    ),
    'tipos_contacto' => array(
        array( 'id' => 1, 'value' => 'Comercial' ),
        array( 'id' => 2, 'value' => 'Cobranza' ),
        array( 'id' => 3, 'value' => 'Despacho' )
    ),
    'condiciones_pago' => array(
        array( 'id' => 1, 'value' => 'Efectivo' ),
        array( 'id' => 2, 'value' => 'Credito a 30 dias' ),
        array( 'id' => 3, 'value' => 'Credito a 60 dias' ),
        array( 'id' => 4, 'value' => 'Credito a 90 dias' )
    ),
    'meses' =>  array(
        array('id'=>1, 'mes' => '01', 'value'=>'Enero'),
        array('id'=>2, 'mes' => '02', 'value'=>'Febrero'),
        array('id'=>3, 'mes' => '03', 'value'=>'Marzo'),
        array('id'=>4, 'mes' => '04', 'value'=>'Abril'),
        array('id'=>5, 'mes' => '05', 'value'=>'Mayo'),
        array('id'=>6, 'mes' => '06', 'value'=>'Junio'),
        array('id'=>7, 'mes' => '07', 'value'=>'Julio'),
        array('id'=>8, 'mes' => '08', 'value'=>'Agosto'),
        array('id'=>9, 'mes' => '09', 'value'=>'Septiembre'),
        array('id'=>10, 'mes' => '10', 'value'=>'Octubre'),
        array('id'=>11, 'mes' => '11', 'value'=>'Noviembre'),
        array('id'=>12, 'mes' => '12', 'value'=>'Diciembre')
    ),
    'dias' =>  array(
        array('id'=>1, 'dia' => 'lunes', 'value'=>'Lunes'),
        array('id'=>2, 'dia' => 'martes', 'value'=>'Martes'),
        array('id'=>3, 'dia' => 'miercoles', 'value'=>'Miércoles'),
        array('id'=>4, 'dia' => 'jueves', 'value'=>'Jueves'),
        array('id'=>5, 'dia' => 'viernes', 'value'=>'Viernes'),
        array('id'=>6, 'dia' => 'sabado', 'value'=>'Sábado'),
        array('id'=>7, 'dia' => 'domingo', 'value'=>'Domingo')   
    ),
    'tipo_menu'=> array(
        array( 'id' => 1, 'value' => 'Titulo'),
        array( 'id' => 2, 'value' => 'Opción')
    ),
    'categoria_menu'=> array(
        array( 'id' => 1, 'value' => 'Administración'),
        array( 'id' => 2, 'value' => 'Empresas'),
        array( 'id' => 3, 'value' => 'Todos')
    ),
    'DIRECCION_FE' => 'dimet.fe-es.com',
    'DIRECCION_ERP' => 'dimet.erp-es.com',
    'COD_AUTORIZACION' => 'FE5206996DIMET'
);


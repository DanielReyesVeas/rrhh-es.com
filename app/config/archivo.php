<?php


/* MENU DEL SISTEMA */
Route::resource('menu', 'MenuController');

/*  EMPRESAS    */
Route::resource('empresas', 'EmpresasController');

/*  PERFILES DE USUARIOS    */
Route::resource('perfiles', 'PerfilesController');

/*  FUNCIONARIOS    */
Route::resource('usuarios', 'UsuariosController');

/*  EMPLEADOS    */
Route::resource('empleados', 'EmpleadosController');

/*  JORNADAS    */
Route::resource('jornadas', 'JornadasController');

/*  TIPOS_CONTRATO    */
Route::resource('tipos-contrato', 'TiposContratoController');

/*  APORTES    */
Route::resource('aportes', 'AportesController');
Route::post('aportes/cuentas/actualizar', 'AportesController@updateCuenta');//actualizar cuentas sólo aportes
Route::post('aportes/cuentas-masivo/actualizar', 'AportesController@updateCuentaMasivo');//actualizar cuentas sólo aportes masivo
Route::post('aportes/cuentas-centros-costos/actualizar', 'AportesController@updateCuentaCentroCosto');//actualizar cuentas sólo aportes por centro de costo
Route::get('aportes/centro-costo/obtener/{sid}', 'AportesController@cuentaAporteCentroCosto');//obtener cuenta asociada al aporte por centro de costo

/*  TRABAJADORES    */
Route::resource('trabajadores', 'TrabajadoresController');

/*   NACIONALIDADES    */
Route::resource('nacionalidades', 'NacionalidadesController');

/*   ESTADOS_CIVILES    */
Route::resource('estados-civiles', 'EstadosCivilesController');

/*   CARGOS    */
Route::resource('cargos', 'CargosController');

/*   AREAS_A_CARGO    */
Route::resource('areas-a-cargo', 'AreasACargoController');

/*   TÍTULOS    */
Route::resource('titulos', 'TitulosController');

/*   CUENTAS    */
Route::resource('cuentas', 'CuentasController');

/*   BANCOS    */
Route::resource('bancos', 'BancosController');

/*   AFPS    */
Route::resource('afps', 'AfpsController');

/*   ISAPRES    */
Route::resource('isapres', 'IsapresController');

/*   TIPOS_CARGA    */
Route::resource('tipos-carga', 'TiposCargaController');

/*   TIPOS_HABER    */
Route::resource('tipos-haber', 'TiposHaberController');

/*   TIPOS_DESCUENTO    */
Route::resource('tipos-descuento', 'TiposDescuentoController');

/*   TIPOS_DOCUMENTO    */
Route::resource('tipos-documento', 'TiposDocumentoController');

/*   APVS    */
Route::resource('apvs', 'ApvsController');
Route::get('trabajadores/total-apvs/obtener', 'TrabajadoresController@trabajadoresApvs');// listado todos trabajadores APVs
Route::get('trabajadores/apvs/obtener/{sid}', 'TrabajadoresController@trabajadorApvs');//detalle APVs trabajador.

/*   HABERES    */
Route::resource('haberes', 'HaberesController');

/*   DESCUENTOS    */
Route::resource('descuentos', 'DescuentosController');

/*   INASISTENCIAS    */
Route::resource('inasistencias', 'InasistenciasController');
Route::get('trabajadores/total-inasistencias/obtener', 'TrabajadoresController@trabajadoresInasistencias');// listado todos trabajadores inasistencias
Route::get('trabajadores/inasistencias/obtener/{sid}', 'TrabajadoresController@trabajadorInasistencias');//detalle inasistencias trabajador

/*   ATRASOS    */
Route::resource('atrasos', 'AtrasosController');
Route::get('trabajadores/total-atrasos/obtener', 'TrabajadoresController@trabajadoresAtrasos');// listado todos trabajadores atrasos
Route::get('trabajadores/atrasos/obtener/{sid}', 'TrabajadoresController@trabajadorAtrasos');//detalle atrasos trabajador

/*   DESCUENTOS_HORA    */
Route::resource('descuentos-horas', 'DescuentosHorasController');

/*   LICENCIAS    */
Route::resource('licencias', 'LicenciasController');
Route::get('trabajadores/total-licencias/obtener', 'TrabajadoresController@trabajadoresLicencias');// listado todos trabajadores licencias
Route::get('trabajadores/licencias/obtener/{sid}', 'TrabajadoresController@trabajadorLicencias');//detalle licencias trabajador


/*   HORAS_EXTRA    */
Route::resource('horas-extra', 'HorasExtraController');
Route::get('trabajadores/total-horas-extra/obtener', 'TrabajadoresController@trabajadoresHorasExtra');// listado todos trabajadores horas-extra
Route::get('trabajadores/horas-extra/obtener/{sid}', 'TrabajadoresController@trabajadorHorasExtra');//detalle horas-extra trabajador

/*   TIPO_HORAS_EXTRA    */
Route::resource('tipos-hora-extra', 'TiposHoraExtraController');

/*   PRESTAMOS    */
Route::resource('prestamos', 'PrestamosController');
Route::get('trabajadores/total-prestamos/obtener', 'TrabajadoresController@trabajadoresPrestamos');// listado todos trabajadores préstamos
Route::get('trabajadores/prestamos/obtener/{sid}', 'TrabajadoresController@trabajadorPrestamos');//detalle préstamos trabajador

/*   CUOTAS    */
Route::resource('cuotas', 'CuotasController');

/*   CARGAS    */
Route::resource('cargas', 'CargasController');
Route::get('trabajadores/total-cargas-familiares/obtener', 'TrabajadoresController@trabajadoresCargas');// listado todos trabajadores Cargas Familiares
Route::get('trabajadores/cargas-familiares/obtener/{sid}', 'TrabajadoresController@trabajadorCargas');//detalle Cargas Familiares
Route::get('trabajadores/cargas-familiares-autorizar/obtener/{sid}', 'TrabajadoresController@trabajadorCargasAutorizar');//detalle Cargas Familiares autorizadas
Route::post('trabajadores/autorizar-cargas-familiares/generar', 'TrabajadoresController@trabajadorAutorizarCargas');//autorizar Carga Familiar
Route::post('trabajadores/tramo/cambiar', 'TrabajadoresController@cambiarTramo');//cambiar tramo asignación familiar trabajador

/*   SECCIONES    */
Route::resource('secciones', 'SeccionesController');

/*   TABLAS    */
Route::resource('tablas', 'TablasController');

/*   TABLA_IMPUESTO_UNICO    */
Route::resource('tabla-impuesto-unico', 'TablaImpuestoUnicoController');

/*   FACTORES_ACTUALIZACIÓN    */
Route::resource('factores-actualizacion', 'FactorActualizacionController');

/*   TASAS_CAJAS_EX_REGIMEN   */
Route::resource('tasas-cajas-ex-regimen', 'TasasCajasExRegimenController');

/*   RECAUDADORES  (no se utiliza)  */
Route::resource('recaudadores', 'RecaudadoresController');

/*   CODIGOS    */
Route::resource('codigos', 'CodigosController');

/*   GLOSAS    */
Route::resource('glosas', 'GlosasController');

/*   MES_DE_TRABAJO    */
Route::resource('mes-de-trabajo', 'MesDeTrabajoController');  

/*   ANIOS    */
Route::resource('anios', 'AniosRemuneracionesController');
Route::get('anio-remuneracion/datos-cierre/obtener', 'AniosRemuneracionesController@datosCierre');
Route::post('anio-remuneracion/cerrar-meses/generar', 'AniosRemuneracionesController@cerrarMeses');
Route::post('anio-remuneracion/feriados/generar', 'AniosRemuneracionesController@feriados');
Route::get('anio-remuneracion/calendario/obtener', 'AniosRemuneracionesController@calendario');
Route::post('anio-remuneracion/feriados-vacaciones/generar', 'AniosRemuneracionesController@feriadosVacaciones');
Route::post('anio-remuneracion/feriados-semana-corrida/modificar', 'AniosRemuneracionesController@modificarFestivosSemanaCorrida');
Route::get('anio-remuneracion/calendario-vacaciones/obtener', 'AniosRemuneracionesController@calendarioVacaciones');
Route::post('anio-remuneracion/gratificacion/generar', 'AniosRemuneracionesController@gratificacion');
Route::get('anio-remuneracion/datos-centralizacion/obtener/{sid}', 'AniosRemuneracionesController@datosCentralizacion');

/*   VALORES_INDICADORES    */

//tabla valores_indicadores BD principal
Route::resource('valores-indicadores', 'ValoresIndicadoresController');
Route::post('valor-indicador/ingreso/masivo', 'ValoresIndicadoresController@ingresoMasivo');//ingresar indicadores masivamente
Route::post('valor-indicador/modificar/masivo', 'ValoresIndicadoresController@modificar');//modificar indicadores
Route::get('valores-indicadores/indicadores/obtener/{fecha}', 'ValoresIndicadoresController@indicadores');//obtener indicadores mes

/*   LIQUIDACIONES    */
Route::resource('liquidaciones', 'LiquidacionesController');
Route::get('trabajadores/trabajadores-liquidaciones/obtener', 'TrabajadoresController@trabajadoresLiquidaciones');//listado trabajadores liquidaciones (sinLiquidacion, conLiquidacion, finiquitadosSinLiquidacion, finiquitadosConLiquidacion)
Route::post('trabajadores/liquidacion/generar', 'TrabajadoresController@miLiquidacion');//generar liquidaciones
Route::post('trabajadores/liquidacion/registro-observaciones', 'TrabajadoresController@miLiquidacionObservaciones_store');//observaciones liquidaciones
Route::post('liquidaciones/eliminar/masivo', 'LiquidacionesController@eliminarMasivo');//eliminar liquidaciones masivo
Route::post('liquidaciones/imprimir/masivo', 'LiquidacionesController@imprimirMasivo');//impresion masiva campo html tabla liquidaciones, si no existe genera un nuevo cuerpo (generarCuerpo())

/*   LIBRO_REMUNERACIONES    */
Route::resource('libro-remuneraciones', 'LibrosRemuneracionesController');   
Route::resource('liquidaciones/libro-remuneraciones/obtener', 'LiquidacionesController@libroRemuneraciones');//obtener libro remuneraciones (vista)
Route::post('trabajadores/libro-remuneraciones/generar-excel', 'TrabajadoresController@generarLibroExcel');//generar libro de remuneraciones (desde liquidaciones)
Route::get('trabajadores/libro-remuneraciones/descargar-excel/{nombre}', 'TrabajadoresController@descargarLibroExcel');//descargar libro

/*   DECLARACIONES    */
Route::resource('declaraciones-trabajadores', 'DeclaracionesTrabajadoresController');
Route::post('declaraciones-trabajadores/eliminar/masivo', 'DeclaracionesTrabajadoresController@eliminarMasivo');//eliminar masivamente declaraciones de los trabajadores

/*  CAUSALES_FINIQUITO    */
Route::resource('causales-finiquito', 'CausalesFiniquitoController');

/*  CAUSALES_NOTIFICACION    */
Route::resource('causales-notificacion', 'CausalesNotificacionController');

/*  CLAUSULAS_CONTRATO    */
Route::resource('clausulas-contrato', 'ClausulasContratoController');
Route::get('clausulas-contrato/plantilla-contrato/obtener/{sid}', 'ClausulasContratoController@listaClausulasContrato');//listado clausulas

/*  CLAUSULAS_FINIQUITO    */
Route::resource('clausulas-finiquito', 'ClausulasFiniquitoController');
Route::get('clausulas-finiquito/plantilla-finiquito/obtener/{sid}', 'ClausulasFiniquitoController@listaClausulasFiniquito');//listado clausulas
/*  TRAMOS_HORAS_EXTRA    */
Route::resource('tramos-horas-extra', 'TramosHorasExtraController');

/*  PLANTILLAS_CARTAS_NOTIFICACION    */
Route::resource('plantillas-cartas-notificacion', 'PlantillasCartasNotificacionController');

/*  CARTAS_NOTIFICACION    */
Route::resource('cartas-notificacion', 'CartasNotificacionController');
Route::get('trabajadores/trabajadores-cartas-notificacion/obtener', 'TrabajadoresController@trabajadoresCartasNotificacion');// listado todos trabajadores Cartas de Notificación
Route::get('trabajadores/cartas-notificacion/obtener/{sid}', 'TrabajadoresController@trabajadorCartasNotificacion');//detalle Cartas de Notificación trabajador.
Route::post('trabajadores/carta-notificacion/generar', 'TrabajadoresController@cartaNotificacion');//generar carta de notificación

/*  CONTRATOS    */
Route::resource('contratos', 'ContratosController');
Route::get('trabajadores/contratos/obtener/{sid}', 'TrabajadoresController@trabajadorContratos');//detalle contratos trabajador
Route::post('trabajadores/contrato/generar', 'TrabajadoresController@contrato');//generar contrato


/*  CERTIFICADOS    */
Route::resource('certificados', 'CertificadosController');
Route::get('trabajadores/trabajadores-certificados/obtener', 'TrabajadoresController@trabajadoresCertificados');// listado todos trabajadores Certificados
Route::get('trabajadores/certificados/obtener/{sid}', 'TrabajadoresController@trabajadorCertificados');//detalle Certificados trabajador.
Route::post('trabajadores/certificado/generar', 'TrabajadoresController@certificado');//generar Certificado


/*  PLANTILLAS_CONTRATOS    */
Route::resource('plantillas-contratos', 'PlantillasContratosController');

/*  PLANTILLAS_FINIQUITOS    */
Route::resource('plantillas-finiquitos', 'PlantillasFiniquitosController');

/*  PLANTILLAS_CERTIFICADOS    */
Route::resource('plantillas-certificados', 'PlantillasCertificadosController');

/*  FINIQUITOS    */
Route::resource('finiquitos', 'FiniquitosController');
Route::post('finiquitos/calculo/obtener', 'FiniquitosController@calcular');//calcular finiquitos
Route::get('trabajadores/trabajadores-finiquitos/obtener', 'TrabajadoresController@trabajadoresFiniquitos');//listado trabajadores finiquitos (activos, finiquitados)
Route::get('trabajadores/finiquitos/obtener/{sid}', 'TrabajadoresController@trabajadorFiniquitos');//detalle finiquitos trabajador
Route::post('trabajadores/finiquitar/generar', 'TrabajadoresController@finiquitar');//finiquitar trabajador (cambiar estado y agregar fecha finiquito)


/*  DOCUMENTOS    */
Route::resource('documentos', 'DocumentosController');
Route::get('trabajadores/trabajadores-documentos/obtener', 'TrabajadoresController@trabajadoresDocumentos');//listado documentos trabajadores
Route::get('trabajadores/documentos/obtener/{sid}', 'TrabajadoresController@trabajadorDocumentos');//detalle documentos trabajador
Route::get('trabajadores/documento/obtener/{sid}', 'DocumentosController@documentoPDF');//obtener documento
Route::get('trabajadores/documento/descargar-pdf/{nombre}', 'TrabajadoresController@documentoPDF');//obtener documento
Route::post('documentos/archivo/importar', 'DocumentosController@importarDocumento');//importación documentos externos, validación
Route::post('documentos/archivo/subir', 'DocumentosController@subirDocumento');//subir documento    
Route::post('documentos/archivo/eliminar', 'DocumentosController@eliminarDocumento');//eliminar documento

/*  DOCUMENTOS_EMPRESA    */
Route::resource('documentos-empresa', 'DocumentosEmpresaController');
Route::post('documentos-empresa/archivo/importar', 'DocumentosEmpresaController@importarDocumento');//importación documentos, validación
Route::post('documentos-empresa/archivo/subir', 'DocumentosEmpresaController@subirDocumento');//subir un documento
Route::get('documentos-empresa/documento/descargar-documento/{sid}', 'DocumentosEmpresaController@documentoPDF');//descar documentos (PDFs)
Route::get('documentos-empresa/publicos/obtener', 'DocumentosEmpresaController@publicos');//listado documentos públicos

/*  VACACIONES    */
Route::resource('vacaciones', 'VacacionesController');
Route::get('trabajadores/trabajadores-vacaciones/obtener', 'TrabajadoresController@trabajadoresVacaciones');// listado todos trabajadores y sus vacaciones
Route::get('trabajadores/vacaciones/obtener/{sid}', 'TrabajadoresController@trabajadorVacaciones');//detalle vacaciones y toma de vacaciones trabajador.
Route::post('trabajadores/provision-vacaciones/obtener', 'TrabajadoresController@provisionVacaciones');//generar provisión vacaciones todos los trabajadores
Route::get('trabajadores/provision-vacaciones/descargar', 'TrabajadoresController@descargarProvision');//descargar provisión vacaciones 
Route::post('vacaciones/recalculo/obtener', 'VacacionesController@recalcularVacaciones');  //Recalcular Vacaciones
Route::post('vacaciones/toma-vacaciones/obtener', 'VacacionesController@tomaVacaciones');  //obtener tomas de vacaciones
Route::post('vacaciones/toma-vacaciones/eliminar', 'VacacionesController@eliminarTomaVacaciones');//eliminar toma de vacaciones

/*   CENTROS_COSTO    */
Route::resource('centros-costo', 'CentrosCostoController');

/*   TIENDAS    */
Route::resource('tiendas', 'TiendasController');

/*   CUENTAS    */
Route::resource('cuentas', 'CuentasController');
Route::get('cuentas/plan-cuentas/obtener', 'CuentasController@planCuentas');//obtener plan de cuentas (desde CME o local)

/*   REPORTES / LOGS    */
Route::resource('reportes', 'LogsController');    



/*   PORTAL_EMPLEADOS    */

    /*  EMPLEADOS    */
Route::post('empleados/permisos/cambiar', "EmpleadosController@cambiarPermisos");//cambio permisos empleado portal
Route::post('empleados/permisos/cambiar-masivo', "EmpleadosController@cambiarPermisosMasivo");//cambio permisos empleados portal en forma masiva
Route::post('empleados/portal/activar', "EmpleadosController@activarUsuario");//activar empleado
Route::post('empleados/portal/activar-masivo', "EmpleadosController@activarMasivo");//activación masiva de empleados
Route::post('empleados/portal/desactivar-masivo', "EmpleadosController@desactivarMasivo");//desactivación masiva de empleados
Route::post('empleados/portal/reactivar', "EmpleadosController@reactivarUsuario");//reactivación masiva de empleados
Route::post('empleados/portal/generar-clave', "EmpleadosController@generarClave");//generar clave empleado y enviar al correo ingresado 
Route::post('empleados/portal/generar-clave-masivo', "EmpleadosController@generarClaveMasivo");//generar clave masiva empleados y enviar al correo de la ficha

/*   MIS_LIQUIDACIONES    */
Route::resource('mis-liquidaciones', 'MisLiquidacionesController');

/*   MIS_CARTAS_NOTIFICACIÓN    */
Route::resource('mis-cartas-notificacion', 'MisCartasNotificacionController');

/*   MIS_CERTIFICADOS    */
Route::resource('mis-certificados', 'MisCertificadosController');










/*  TRABAJADORES    */
Route::get('trabajadores/planilla-trabajadores/descargar', 'TrabajadoresController@descargarPlantillaTrabajadores');//generar planilla importación
Route::post('trabajadores/planilla/importar', 'TrabajadoresController@importarPlanilla');//importación planilla (comprobación de errores)
Route::post('trabajadores/generar-ingreso/masivo', 'TrabajadoresController@generarIngresoMasivo');//importación masivo por planilla



Route::get('trabajadores/reajuste/obtener', 'TrabajadoresController@reajuste');//listado trabajadores bajo la RMI
Route::post('trabajadores/reajuste/masivo', 'TrabajadoresController@reajustarRMI');//reajustar renta trabajadores (masivo)


Route::get('trabajadores/trabajadores-semana-corrida/obtener', 'TrabajadoresController@trabajadoresSemanaCorrida');// listado todos trabajadores con semana corrida
Route::post('trabajadores/semana-corrida/actualizar', 'TrabajadoresController@updateSemanaCorrida');//actualizar montos comisiones semana corrida



Route::get('trabajadores/trabajadores-sueldo-hora/obtener', 'TrabajadoresController@trabajadoresSueldoHora');// listado todos trabajadores con Sueldo por Hora
Route::get('trabajadores/sueldo-hora/obtener/{sid}', 'TrabajadoresController@trabajadorSueldoHora');//detalle trabajador con Sueldo por Hora.



Route::get('trabajadores/descuentos/obtener/{sid}', 'TrabajadoresController@descuentos');//detalle descuentos trabajador.
Route::get('trabajadores/haberes/obtener/{sid}', 'TrabajadoresController@haberes');//detalle haberes trabajador.
Route::get('trabajadores/planilla/descargar-excel/{tipo}', 'TrabajadoresController@descargarPlantilla');//generar planilla {tipo}=haber, descuento
Route::get('trabajadores/planilla-masivo/descargar-excel/{tipo}', 'TrabajadoresController@descargarPlantillaMasivo');//generar planilla ingreso masivo  {tipo}=haber, descuento


//datos para formulario
Route::get('trabajadores/opciones/afps', 'TrabajadoresController@listaAfps');//AFPs
Route::get('trabajadores/input/obtener', 'TrabajadoresController@input');//trabajadores (para ingresar haberes, descuentos, horas-extra, etc)
Route::get('trabajadores/input-activos/obtener', 'TrabajadoresController@inputActivos');//trabajadores (para ingresar haberes, descuentos, horas-extra, etc)
Route::get('trabajadores/secciones/formulario', 'TrabajadoresController@seccionesFormulario');//secciones
Route::get('trabajadores/seccion/obtener/{sid}', 'TrabajadoresController@seccion');//sección y sus trabajadores
Route::get('trabajadores/ingresados/obtener', 'TrabajadoresController@ingresados');


Route::get('trabajadores/archivo-previred/obtener', 'TrabajadoresController@archivoPrevired');//listado trabajadores para archivo previred (estado de liquidaciones)
Route::post('trabajadores/archivo-previred/generar', 'TrabajadoresController@generarArchivoPreviredExcel');//generar archivo previred a partir de las liquidaciones y generar reporte detallado
Route::get('trabajadores/archivo-previred/descargar', 'TrabajadoresController@descargarPrevired');//descargar archivo

Route::get('trabajadores/vigentes/obtener', 'TrabajadoresController@vigentes');//lsitado trabajadores vigentes

Route::get('trabajadores/trabajadores-f1887/obtener/{sid}', 'TrabajadoresController@trabajadoresF1887');//listado trabajadores archivo F1887
Route::post('trabajadores/f1887-trabajadores/generar', 'TrabajadoresController@generarF1887Trabajadores');//generar declaración trabajadores (masivo)
Route::get('trabajadores/f1887/generar/{anio}', 'TrabajadoresController@generarF1887');//generar archivo F1887 (con todas las declaraciones generadas)
Route::get('trabajadores/f1887/ver/{anio}', 'TrabajadoresController@verF1887');//ver archivo F1887




Route::get('trabajadores/fichas/obtener/{sid}', 'TrabajadoresController@trabajadorFichas');//detalle fichas trabajador (gestión de fichas)
Route::resource('fichas/unificar/obtener', 'FichasTrabajadoresController@unificar');//unificar fichas trabajador

Route::post('trabajadores/nomina-bancaria/generar-excel', 'TrabajadoresController@generarNominaExcel');//generar nómina bancaria (desde fichas)
Route::get('trabajadores/nomina-bancaria/descargar-excel/{nombre}', 'TrabajadoresController@descargarNominaExcel');//descargar nómina


Route::get('trabajadores/planilla-costo-empresa/obtener', 'TrabajadoresController@planillaCostoEmpresa');//listado trabajadores planilla costo (desde liqudiaciones)
Route::post('trabajadores/planilla-costo-empresa/generar-excel', 'TrabajadoresController@generarPlanillaExcel');//generar excel planilla
Route::get('trabajadores/planilla-costo-empresa/descargar-excel/{nombre}', 'TrabajadoresController@descargarPlanillaExcel');//descargar planilla excel








-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-04-2017 a las 17:25:27
-- Versión del servidor: 5.5.36
-- Versión de PHP: 5.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `easysyst_rrhh`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anios_remuneraciones`
--

CREATE TABLE IF NOT EXISTS `anios_remuneraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anio` int(11) NOT NULL,
  `enero` tinyint(4) NOT NULL,
  `febrero` tinyint(4) NOT NULL,
  `marzo` tinyint(4) NOT NULL,
  `abril` tinyint(4) NOT NULL,
  `mayo` tinyint(4) NOT NULL,
  `junio` tinyint(4) NOT NULL,
  `julio` tinyint(4) NOT NULL,
  `agosto` tinyint(4) NOT NULL,
  `septiembre` tinyint(4) NOT NULL,
  `octubre` tinyint(4) NOT NULL,
  `noviembre` tinyint(4) NOT NULL,
  `diciembre` tinyint(4) NOT NULL,
  `gratificacion` DATE NULL DEFAULT NULL,
  `pagar` TINYINT(1) NOT NULL,
  `utilidad` INT NULL DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `sid` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aportes_cuentas`
--

CREATE TABLE IF NOT EXISTS `aportes_cuentas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `cuenta_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Volcado de datos para la tabla `aportes_cuentas`
--

INSERT INTO `aportes_cuentas` (`id`, `sid`, `nombre`, `alias`, `cuenta_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 'V20170816091241FUQ5017', '40', '40', NULL, '2017-08-16 09:12:42', '2017-08-16 09:53:40', NULL),
(1, 'J20170816091631ENN1864', 'Caja de Compensación', 'caja', NULL, '2017-08-16 09:16:32', '2017-08-16 10:42:24', NULL),
(2, 'D20170816091732CQY8275', 'Fonasa', 'fonasa', NULL, '2017-08-16 09:17:33', '2017-08-16 09:17:33', NULL),
(3, 'V20170816091744VVA2056', 'Seguro de Cesantía', 'seguro', NULL, '2017-08-16 09:17:45', '2017-08-16 09:17:45', NULL),
(4, 'B20170816091949HWQ6320', 'Mutual de Seguridad', 'mutual', NULL, '2017-08-16 09:19:50', '2017-08-16 09:19:50', NULL),
(7, 'G20170816095349KKX6004', '36', '36', NULL, '2017-08-16 09:53:50', '2017-08-16 09:53:50', NULL),
(8, 'A20170816095358XNI9783', '37', '37', NULL, '2017-08-16 09:53:59', '2017-08-16 09:53:59', NULL),
(9, 'L20170816095408YVE3504', '39', '39', NULL, '2017-08-16 09:54:09', '2017-08-16 09:54:09', NULL),
(10, 'Y20170816095421JCZ9082', '38', '38', NULL, '2017-08-16 09:54:22', '2017-08-16 09:54:22', NULL),
(11, 'Y20170816095431AGP2905', '41', '41', NULL, '2017-08-16 09:54:32', '2017-08-16 09:54:32', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apvs`
--

CREATE TABLE IF NOT EXISTS `apvs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `ficha_trabajador_id` int(11) NOT NULL,
  `afp_id` int(11) NOT NULL,
  `moneda` varchar(50) NOT NULL,
  `monto` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargas_familiares`
--

CREATE TABLE IF NOT EXISTS `cargas_familiares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `ficha_trabajador_id` int(11) NOT NULL,
  `tipo_carga_id` int(11) NOT NULL,
  `parentesco` varchar(255) NOT NULL,
  `es_carga` tinyint(1) DEFAULT '0',
  `es_autorizada` tinyint(1) DEFAULT NULL,
  `rut` varchar(15) NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `fecha_autorizacion` date DEFAULT NULL,
  `sexo` char(1) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE IF NOT EXISTS `cargos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `sid` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `nombre`, `sid`, `updated_at`, `created_at`) VALUES
(1, 'Vendedor', 'H20170036487200BID6522', '2017-07-03 20:12:03', '2015-07-04 00:57:09'),
(2, 'Jefe de Ventas', 'F20170213847674BFX8881', '2017-07-03 20:12:03', '2015-07-04 00:57:09'),
(3, 'Administrador', 'Y20151031648752UUD1861', '2017-07-03 20:12:03', '2015-07-04 00:57:09'),
(4, 'Encargado de Bodega', 'B20136714850343KJS7565', '2017-07-03 20:12:03', '2015-07-04 00:57:09'),
(5, 'Asistente de Bodega', 'U20170316482076OHT5940', '2017-07-03 20:12:03', '2015-07-04 00:57:09'),
(6, 'Auxiliar de Aseo', 'L20151027846197UUD1861', '2017-07-03 20:12:03', '2015-07-04 00:57:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cartas_notificacion`
--

CREATE TABLE IF NOT EXISTS `cartas_notificacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `plantilla_carta_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `encargado_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `empresa_razon_social` varchar(255) NOT NULL,
  `empresa_rut` varchar(255) NOT NULL,
  `empresa_direccion` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `folio` int(50) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `trabajador_rut` varchar(255) NOT NULL,
  `trabajador_nombre_completo` varchar(255) NOT NULL,
  `trabajador_cargo` varchar(255) NOT NULL,
  `trabajador_seccion` varchar(255) NOT NULL,
  `trabajador_fecha_ingreso` date NOT NULL,
  `trabajador_direccion` varchar(255) NOT NULL,
  `trabajador_comuna` varchar(255) NOT NULL,
  `trabajador_provincia` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `causales_finiquito`
--

CREATE TABLE IF NOT EXISTS `causales_finiquito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `articulo` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

--
-- Volcado de datos para la tabla `causales_finiquito`
--

INSERT INTO `causales_finiquito` (`id`, `sid`, `codigo`, `articulo`, `nombre`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'F20170412192726EQC7203', '1', '159', 'Mutuo acuerdo de las partes', '2017-04-12 19:27:27', '2017-04-12 19:29:40', NULL),
(2, 'P20170412192956LXS7428', '2', '159', 'Renuncia del trabajador, dando aviso a su empleador con treinta días de anticipación, a lo menos', '2017-04-12 19:29:57', '2017-04-12 19:29:57', NULL),
(3, 'E20170412193010XKJ7121', '3', '159', 'Muerte del trabajador', '2017-04-12 19:30:11', '2017-04-12 19:30:11', NULL),
(4, 'Q20170417182623PWH9452', '4', '159', 'Vencimiento del plazo convenido en el contrato', '2017-04-17 18:26:24', '2017-04-17 18:26:24', NULL),
(5, 'C20170417182641IUJ7498', '5', '159', 'Conclusión del trabajo o servicio que dio origen al contrato', '2017-04-17 18:26:42', '2017-04-17 18:26:42', NULL),
(6, 'Q20170417182649TPT8581', '6', '159', 'Caso fortuito o fuerza mayor', '2017-04-17 18:26:50', '2017-04-17 18:27:03', NULL),
(7, 'N20170417182732DCQ5846', '1a', '160', 'Falta de probidad del trabajador en el desempeño de sus funciones', '2017-04-17 18:27:33', '2017-04-17 18:27:33', NULL),
(8, 'Q20170417182751SEH4622', '1b', '160', 'Conductas de acoso sexual', '2017-04-17 18:27:52', '2017-04-17 18:27:52', NULL),
(9, 'R20170417182808GAA9817', '1c', '160', 'Vías de hecho ejercidas por el trabajador en contra del empleador o de cualquier trabajador que se desempeñe en la misma empresa', '2017-04-17 18:28:10', '2017-04-17 18:31:48', NULL),
(10, 'E20170417183248JWB1068', '1d', '160', 'Injurias proferidas por el trabajador al empleador', '2017-04-17 18:32:49', '2017-04-17 18:32:49', NULL),
(11, 'C20170417183301BIN9132', '1e', '160', 'Conducta inmoral del trabajador que afecte a la empresa donde se desempeña', '2017-04-17 18:33:02', '2017-04-17 18:33:02', NULL),
(12, 'C20170417183311UAG5408', '1f', '160', 'Conductas de acoso laboral', '2017-04-17 18:33:12', '2017-04-17 18:33:12', NULL),
(13, 'L20170417183348EJD3801', '2', '160', 'Negociaciones que ejecute el trabajador dentro del giro del negocio y que hubieren sido prohibidas por escrito en el respectivo contrato por el empleador', '2017-04-17 18:33:49', '2017-04-17 18:33:49', NULL),
(14, 'O20170417183414TWK3427', '3', '160', 'No concurrencia del trabajador a sus labores sin causa justificada durante dos días seguidos', '2017-04-17 18:34:15', '2017-04-17 18:34:15', NULL),
(15, 'O20170417183657YJV3085', '4a', '160', 'Abandono del trabajo por parte del trabajador: la salida intempestiva e injustificada del trabajador del sitio de la faena y durante las horas de trabajo, sin permiso del empleador', '2017-04-17 18:36:58', '2017-04-17 18:36:58', NULL),
(16, 'B20170417183731XKT6141', '4b', '160', 'Abandono del trabajo por parte del trabajador: la negativa a trabajar sin causa justificada en las faenas convenidas en el contrato', '2017-04-17 18:37:32', '2017-04-17 18:37:32', NULL),
(17, 'D20170417183751RSR1251', '5', '160', 'Actos, omisiones o imprudencias temerarias que afecten a la seguridad o al funcionamiento del establecimiento', '2017-04-17 18:37:52', '2017-04-17 18:37:52', NULL),
(18, 'G20170417183808BYV9470', '6', '160', 'El perjuicio material causado intencionalmente en las instalaciones, maquinarias, herramientas, útiles de trabajo, productos o mercaderías', '2017-04-17 18:38:09', '2017-04-17 18:38:09', NULL),
(19, 'M20170417183820ELH5732', '7', '160', 'Incumplimiento grave de las obligaciones que impone el contrato', '2017-04-17 18:38:21', '2017-04-17 18:38:21', NULL),
(20, 'F20170417183857JFN7290', '1', '161', 'Necesidades de la Empresa: el empleador podrá poner término al contrato invocando como causal las necesidades de la empresa', '2017-04-17 18:38:58', '2017-04-17 18:38:58', NULL),
(21, 'L20170417183932QZN3321', '1', '163bis', 'Haber sido sometido el empleador, mediante resolución judicial, a un procedimiento concursal de liquidación de sus bienes', '2017-04-17 18:39:34', '2017-04-17 18:39:34', NULL);

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `causales_notificacion`
--

CREATE TABLE IF NOT EXISTS `causales_notificacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `articulo` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



--
-- Estructura de tabla para la tabla `certificados`
--

CREATE TABLE IF NOT EXISTS `certificados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `plantilla_certificado_id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `encargado_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `empresa_razon_social` varchar(255) NOT NULL,
  `empresa_rut` varchar(255) NOT NULL,
  `empresa_direccion` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `folio` int(50) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `trabajador_rut` varchar(255) NOT NULL,
  `trabajador_nombre_completo` varchar(255) NOT NULL,
  `trabajador_cargo` varchar(255) NOT NULL,
  `trabajador_seccion` varchar(255) NOT NULL,
  `trabajador_fecha_ingreso` date NOT NULL,
  `trabajador_direccion` varchar(255) NOT NULL,
  `trabajador_comuna` varchar(255) NOT NULL,
  `trabajador_provincia` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clausulas_contrato`
--

CREATE TABLE IF NOT EXISTS `clausulas_contrato` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plantilla_contrato_id` int(11) NOT NULL,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `clausula` text NOT NULL,
  `clausula_notificacion` text,
  `created_at` datetime NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15;

--
-- Volcado de datos para la tabla `clausulas_contrato`
--

INSERT INTO `clausulas_contrato` (`id`, `plantilla_contrato_id`, `sid`, `nombre`, `clausula`, `clausula_notificacion`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Z20170424195417IVP3630', 'FUNCIÓN O LABOR', '<p>El trabajador se compromete a desempe&ntilde;ar el trabajo que se le encomienda de ${cargoTrabajador}.</p>', NULL, '2017-04-24 19:54:18', 2017, NULL),
(2, 1, 'K20170424195656VIW4472', 'LUGAR DE PRESTACIÓN DE SERVICIOS', '<p>El trabajador cumplir&aacute; las labores para las cuales fue contratado en las instalaciones de la empresa ubicada en ${domicilioEmpresa}. Sin embargo, el empleador podr&aacute;, por causa justificada, naturaleza de los servicios, destinarle a cualquiera de las Sucursales, Locales u Oficinas de la empresa, dentro de la misma ciudad o comuna, con la sola limitaci&oacute;n de que se trate de labores similares, para lo cual, el empleador comunicar&aacute; tal circunstancia.</p>\n<p>Efectuada la comunicaci&oacute;n respectiva, por cualquier medio, el trabajador deber&aacute; presentarse a prestar servicios en el lugar y fecha se&ntilde;alada, obligaci&oacute;n que acepta en este acto, configurando su incumplimiento inasistencia injustificada al trabajo. Lo pactado precedentemente, es sin perjuicio del ejercicio del derecho que confiere el art&iacute;culo 12 del C&oacute;digo del Trabajo.</p>', NULL, '2017-04-24 19:56:57', 2017, NULL),
(3, 1, 'J20170424200634AAZ9703', 'JORNADA DE TRABAJO', '<p>La jornada de trabajo ser&aacute; de 45 horas semanales, distribuidas de lunes a viernes, de 8:30 a 13:00 horas; y de 14:00 a 18:30 horas. Como consecuencia, la jornada diaria ser&aacute; interrumpida por un descanso de 60 minutos, destinados a colaci&oacute;n, tiempo el cual no es imputable a la jornada.</p>\n<p>El empleador, en conformidad con la ley y de acuerdo con las necesidades de funcionamiento de la empresa, podr&aacute; alterar el horario de inicio y de t&eacute;rmino de la jornada diaria de trabajo. El trabajador se compromete a laborar con dedicaci&oacute;n, durante toda la jornada convenida.</p>', 'La jornada de trabajo será la siguiente de Lunes a Viernes, el horario será de 08:30 a 13:00 Hrs. y de 14:00 a 18:30 Hrs.', '2017-04-24 20:06:35', 2017, NULL),
(4, 1, 'F20170424201100ELJ1631', 'REMUNERACIONES', '<p>La remuneraci&oacute;n del trabajador ser&aacute; la suma mensual de ${sueldoBase} (${sueldoBasePalabras}), por mes calendario, que ser&aacute; liquidada y pagada por per&iacute;odos vencidos, en las Oficinas del empleador, el &uacute;ltimo d&iacute;a h&aacute;bil de cada mes.</p>\n<p>La gratificaci&oacute;n obligatoria, se pagar&aacute; de acuerdo a la modalidad del art&iacute;culo 50 del C&oacute;digo del Trabajo, esto es, el 25 % (veinticinco por ciento) de la remuneraci&oacute;n devengada por el trabajador con un tope de 4,75 Ingresos M&iacute;nimos Mensuales. La empresa otorgar&aacute; anticipos mensuales equivalentes a un duod&eacute;cimo de los 4,75 Ingresos M&iacute;nimos Mensuales. Con este pago se entender&aacute; cumplida la obligaci&oacute;n de la empresa de pagar gratificaci&oacute;n legal. La gratificaci&oacute;n as&iacute; convenida es incompatible y sustituye a la que resulte de la aplicaci&oacute;n de los art&iacute;culos 47 y siguientes del C&oacute;digo del Trabajo.</p>\n<p>De la remuneraci&oacute;n se deducir&aacute;n los impuestos, las cotizaciones de previsi&oacute;n o seguridad social, las cuotas sindicales ordinarias y extraordinarias, los dividendos hipotecarios para adquisici&oacute;n de vivienda y las obligaciones que se deban a los Institutos de Previsi&oacute;n e Isapre.</p>\n<p>No se podr&aacute;n hacer otras deducciones, salvo que est&eacute;n autorizadas por la ley, por el Reglamento Interno de la Empresa; o las que hayan sido ordenadas judicialmente; o que sean autorizadas por el trabajador, por escrito.</p>\n<p>Todo, sin perjuicio de los anticipos de remuneraci&oacute;n, dentro de cada per&iacute;odo, que se autoriza realizar, por el trabajador, de antemano. Lo propio, para descontar el tiempo no trabajado, debido a inasistencias, permisos y atrasos; y el monto de las multas reglamentarias, en su caso.</p>\n<p>Las partes dejan expresamente establecido que cualquier beneficio que la empresa otorgue al trabajador, sea en dinero o en especie, que no figure expresamente consignado o se&ntilde;alado en el presente contrato de trabajo, se entender&aacute; para todos los efectos legales y contractuales, otorgado a t&iacute;tulo de mera liberalidad por parte del empleador, no generando para el trabajador el derecho de exigirlo, en los per&iacute;odos que la empresa decida suspender o terminar su otorgamiento, en consecuencia, tales beneficios tendr&aacute;n el car&aacute;cter de &uacute;nico, voluntario y exclusivo cada vez que se otorgue.</p>', NULL, '2017-04-24 20:11:01', 2017, NULL),
(5, 1, 'H20170424201353OOC3098', 'BENEFICIOS', '<p>El empleador se compromete a otorgar o a suministrar, al trabajador, los siguientes beneficios:</p>\n<p><strong>a)</strong> El trabajador percibir&aacute; la suma de ${colacion} (${colacionPalabras}) mensuales por concepto de colaci&oacute;n.</p>\n<p><strong>b)</strong> El trabajador percibir&aacute; la suma de ${movilizacion} (${movilizacionPalabras}) mensuales por concepto de movilizaci&oacute;n. Cualquier otra prestaci&oacute;n o beneficio -ocasional o peri&oacute;dico- que el empleador conceda, al trabajador, distinto que el que le corresponde por este contrato y sus ajustes legales o contractuales se entender&aacute; conferido a t&iacute;tulo de mera liberalidad; no dar&aacute; derecho alguno; y el empleador podr&aacute; suspenderlo o modificarlo a su arbitrio.</p>\n<p><strong>c)</strong> Cumplir las instrucciones y las &oacute;rdenes que le imparta cualquiera de sus superiores;</p>\n<p><strong>d)</strong> Timbrar la tarjeta del reloj control o en su efecto firmar el registro de firma que destine la empresa, tanto a la entrada, como a la salida de la empresa. Se presumir&aacute; que el trabajador ha faltado o que ha llegado atrasado, en su caso, por la sola circunstancia de no marcar la tarjeta. Si el trabajador fuere sorprendido marcando la tarjeta de otro o aceptara que otro marque la suya, terminar&aacute; ipso facto, este contrato, por acci&oacute;n dolosa y grave. Bastar&aacute;, el testimonio del portero, del encargado del reloj control o del funcionario a quien se cometa esta inspecci&oacute;n, al respecto;</p>\n<p><strong>e)</strong> Trabajar horas extraordinarias cada vez que, por razones de producci&oacute;n, la Gerencia lo determine, las que ser&aacute;n pagadas con recargo de un 50%. La negativa de cumplir esta obligaci&oacute;n, se entender&aacute; como negativa, del trabajador, de desempe&ntilde;ar su labor; y como incumplimiento grave de las obligaciones que le impone el contrato;</p>\n<p><strong>f)</strong> En casos de inasistencia al trabajo, por enfermedad, el trabajador deber&aacute; justificarla -&uacute;nicamente- con el correspondiente certificado m&eacute;dico, otorgado por un facultativo del &aacute;rea m&eacute;dica, dentro del plazo de 24 horas, desde que aqu&eacute;l dej&oacute; de asistir al trabajo.</p>\n<p><strong>g)&nbsp;</strong>El trabajador se obliga a desarrollar su trabajo con el debido cuidado, evitando comprometer la seguridad y la salud del resto de los trabajadores de la empresa y el medio ambiente. La infracci&oacute;n o incumplimiento de cualquiera de las obligaciones antes mencionadas se estimar&aacute; como incumplimiento grave de las obligaciones que impone el contrato y, cuando proceda, la empresa se reserva el derecho de hacer declarar el t&eacute;rmino de la convenci&oacute;n, sin indemnizaci&oacute;n alguna.</p>', NULL, '2017-04-24 20:13:54', 2017, NULL),
(6, 1, 'Q20170424201608YSM1274', 'OBLIGACIONES DEL TRABAJADOR', '<p>Son obligaciones esenciales, del trabajador, cuya infracci&oacute;n las partes entienden como causa justificada de terminaci&oacute;n del presente contrato, las siguientes:</p>\n<p><strong>a)</strong> Cumplir, &iacute;ntegramente, la jornada de trabajo. Las partes acuerdan en este acto que los atrasos reiterados, sin causa justificada, de parte del trabajador, se considerar&aacute;n incumplimiento grave de las obligaciones que impone el presente contrato y dar&aacute;n lugar a la aplicaci&oacute;n de la caducidad del contrato, contemplada en el art. .160 N&ordm;7 del C&oacute;digo del Trabajo. Se entender&aacute; por atraso reiterado el llegar despu&eacute;s de la hora de ingreso durante tres d&iacute;as seguidos o no, en cada mes calendario. Bastar&aacute; para acreditar esta situaci&oacute;n la constancia en el respectivo Control de Asistencia.</p>\n<p><strong>b)</strong> Cuidar y mantener, en perfecto estado de conservaci&oacute;n, las m&aacute;quinas, &uacute;tiles y otros bienes de la empresa;</p>\n<p><strong>c)</strong> Cumplir las instrucciones y las &oacute;rdenes que le imparta cualquiera de sus superiores;</p>\n<p><strong>d)</strong> Timbrar la tarjeta del reloj control o en su efecto firmar el registro de firma que destine la empresa, tanto a la entrada, como a la salida de la empresa. Se presumir&aacute; que el trabajador ha faltado o que ha llegado atrasado, en su caso, por la sola circunstancia de no marcar la tarjeta. Si el trabajador fuere sorprendido marcando la tarjeta de otro o aceptara que otro marque la suya, terminar&aacute; ipso facto, este contrato, por acci&oacute;n dolosa y grave. Bastar&aacute;, el testimonio del portero, del encargado del reloj control o del funcionario a quien se cometa esta inspecci&oacute;n, al respecto;</p>\n<p><strong>e)</strong> Trabajar horas extraordinarias cada vez que, por razones de producci&oacute;n, la Gerencia lo determine, las que ser&aacute;n pagadas con recargo de un 50%. La negativa de cumplir esta obligaci&oacute;n, se entender&aacute; como negativa, del trabajador, de desempe&ntilde;ar su labor; y como incumplimiento grave de las obligaciones que le impone el contrato;</p>\n<p><strong>f)</strong> En casos de inasistencia al trabajo, por enfermedad, el trabajador deber&aacute; justificarla -&uacute;nicamente- con el correspondiente certificado m&eacute;dico, otorgado por un facultativo del &aacute;rea m&eacute;dica, dentro del plazo de 24 horas, desde que aqu&eacute;l dej&oacute; de asistir al trabajo.</p>\n<p><strong>g)</strong> El trabajador se obliga a desarrollar su trabajo con el debido cuidado, evitando comprometer la seguridad y la salud del resto de los trabajadores de la empresa y el medio ambiente. La infracci&oacute;n o incumplimiento de cualquiera de las obligaciones antes mencionadas se estimar&aacute; como incumplimiento grave de las obligaciones que impone el contrato y, cuando proceda, la empresa se reserva el derecho de hacer declarar el t&eacute;rmino de la convenci&oacute;n, sin indemnizaci&oacute;n alguna.</p>', NULL, '2017-04-24 20:16:09', 2017, NULL),
(7, 1, 'J20170424201658IFL1740', 'REGLAMENTO INTERNO', '<p>El trabajador respetar&aacute;, celosamente, el Reglamento Interno, cuyo texto ha recibido, que declara conocer y que se entiende como parte integrante de este contrato.</p>', 'El trabajador respetará, celosamente, el Reglamento Interno, cuyo texto ha recibido, que declara conocer y que se entiende como parte integrante de este contrato.', '2017-04-24 20:16:59', 2017, NULL),
(8, 1, 'M20170424201748HCY9789', 'PROHIBICIONES', '<p>El trabajador estar&aacute; afecto a las siguientes prohibiciones:</p>\n<p><strong>a)</strong> Registrar la asistencia de otro trabajador.</p>\n<p><strong>b)</strong> Retirarse de su lugar de trabajo antes del t&eacute;rmino de la jornada pactada en el contrato de trabajo.</p>\n<p><strong>c)</strong> Realizar en el transcurso de su jornada de trabajo y/o en el recinto del empleador, cualquier actividad que no tenga relaci&oacute;n con las labores para las cuales fue contratado.</p>\n<p><strong>d)</strong> Sacar fuera de la empresa, cualquier tipo de elemento que pertenezcan a ella, ya sea personalmente o por medio de terceras personas. El incumplimiento de est&aacute; prohibici&oacute;n, que se califica de grave, dar&aacute; lugar a las acciones legales pertinentes.</p>\n<p><strong>e)</strong> Ejecutar, durante las horas de trabajo y en el desempe&ntilde;o de sus funciones, actividades ajenas a la labor y al establecimiento o dedicarse a atender asuntos particulares.</p>\n<p><strong>f)</strong> Ejecutar negociaciones que se encuentren comprendidas dentro del giro de la empresa.</p>\n<p><strong>g)</strong> Realizar cualquier conducta que se encuentre re&ntilde;ida con la moral.</p>', NULL, '2017-04-24 20:17:49', 2017, NULL),
(9, 1, 'P20170424201905GMO2204', 'VIGENCIA', '<p>Se eleva a la calidad de esencial de este contrato, el que las partes, de consuno, consideran que &eacute;ste tiene un plazo fijo, desde el d&iacute;a ${fechaInicialPalabras} hasta el d&iacute;a ${fechaTerminoPalabras}.</p>\n<p>Las partes pueden ponerle t&eacute;rmino, adem&aacute;s, de com&uacute;n acuerdo; y una de ellas, en la forma, las condiciones y las causales que se&ntilde;alan los art&iacute;culos 159, 160 y 161 del C&oacute;digo del Trabajo; especialmente, por infracciones al contrato; o por la conclusi&oacute;n de los trabajos que dieron origen a &eacute;ste.</p>', NULL, '2017-04-24 20:19:06', 2017, NULL),
(10, 1, 'V20170424201957SBV7746', 'CONSTANCIA', '<p>Se deja constancia que el trabajador ingres&oacute;, al Servicio del empleador, el d&iacute;a <strong>${fechaInicialPalabras}.-</strong></p>', NULL, '2017-04-24 20:19:58', 2017, NULL),
(11, 1, 'K20170424202052RVB7492', 'DOMICILIO Y JURISDICCIÓN', '<p>Para todos los efectos derivados de este contrato, las partes fijan su domicilio en la ciudad de Santiago y se someten a la Jurisdicci&oacute;n de sus Tribunales.</p>', NULL, '2017-04-24 20:20:53', 2017, NULL),
(12, 1, 'X20170424202122ZGC6118', 'NÚMERO DE EJEMPLARES', '<p>El presente contrato se firma en tres ejemplares, declarando, el trabajador, haber recibido un ejemplar de &eacute;l y que &eacute;ste es fiel reflejo de la relaci&oacute;n laboral existente entre las partes.</p>', NULL, '2017-04-24 20:21:23', 2017, NULL),
(13, 2, 'E20170516162518HPP5152', 'FUNCIÓN O LABOR', '<p>El trabajador se compromete a desempe&ntilde;ar el trabajo que se le encomienda de ${cargoTrabajador}.</p>', NULL, '2017-05-16 16:25:19', 2017, NULL),
(14, 2, 'V20170516164447NES1123', 'LUGAR DE PRESTACIÓN DE SERVICIOS', '<p>El trabajador cumplir&aacute; las labores para las cuales fue contratado en las instalaciones de la empresa ubicada en ${domicilioEmpresa}. Sin embargo, el empleador podr&aacute;, por causa justificada, naturaleza de los servicios, destinarle a cualquiera de las Sucursales, Locales u Oficinas de la empresa, dentro de la misma ciudad o comuna, con la sola limitaci&oacute;n de que se trate de labores similares, para lo cual, el empleador comunicar&aacute; tal circunstancia.</p>\n<p>Efectuada la comunicaci&oacute;n respectiva, por cualquier medio, el trabajador deber&aacute; presentarse a prestar servicios en el lugar y fecha se&ntilde;alada, obligaci&oacute;n que acepta en este acto, configurando su incumplimiento inasistencia injustificada al trabajo. Lo pactado precedentemente, es sin perjuicio del ejercicio del derecho que confiere el art&iacute;culo 12 del C&oacute;digo del Trabajo.</p>', NULL, '2017-05-16 16:44:48', 2017, NULL);

-- --------------------------------------------------------


--
-- Estructura de tabla para la tabla `clausulas_finiquito`
--

CREATE TABLE IF NOT EXISTS `clausulas_finiquito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plantilla_finiquito_id` int(11) NOT NULL,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `clausula` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `clausulas_finiquito`
--

INSERT INTO `clausulas_finiquito` (`id`, `plantilla_finiquito_id`, `sid`, `nombre`, `clausula`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'A20170613170427JTX7485', 'FUNCIÓN O LABOR', '<p>Don(&ntilde;a) ${nombreTrabajador} declara haber prestado servicios a ${nombreEmpresa} en calidad de ${cargoTrabajador} desde el ${fechaInicialPalabras}, hasta el ${fechaFiniquitoPalabras}, fecha esta &uacute;ltima de terminaci&oacute;n de sus servicios por la causa que se indica a continuaci&oacute;n: <em>"${causalFiniquito}"</em>, seg&uacute;n lo dispuesto en el art&iacute;culo N&deg; ${numeroArticulo}, c&oacute;digo N&deg; ${numeroCodigo} del C&oacute;digo del Trabajo.</p>', '2017-06-13 17:04:28', 2017, NULL),
(2, 1, 'W20170613175155EMP7808', 'DETALLE VALORES', '<p>Don(&ntilde;a) ${nombreTrabajador} declara recibir en este acto, a su entera satisfacci&oacute;n de parte de ${nombreEmpresa} las sumas que a continuaci&oacute;n se indican, por los siguientes conceptos:</p>\n<p>&nbsp;</p>\n<p style="text-align: center;">${detalleFiniquito}</p>\n<p>&nbsp;</p>\n<p>En total ${totalFiniquito} son (${totalFiniquitoPalabras}).</p>', '2017-06-13 17:51:56', 2017, NULL),
(3, 1, 'G20170613175549COK7324', 'CONSTANCIA', '<p>Don(&ntilde;a) ${nombreTrabajador} deja en constancia que durante todo el tiempo que le prest&oacute; servicios a ${nombreEmpresa}, recibi&oacute; correcta y oportunamente el total de las remuneraciones convenidas de acuerdo a su contrato de trabajo, clase de trabajo ejecutado, reajustes legales, pago de asignaciones familiares autorizadas por la respectiva Instituci&oacute;n Previsional, feriados legales, en conformidad a la ley, y que nada se le adeuda por los conceptos antes indicados ni por ning&uacute;n otro, sea de origen legal o contractual derivado de la prestaci&oacute;n de sus servicios, y motivo por el cual no teniendo reclamo ni cargo alguno que formular en contra del empleador, le otorga el m&aacute;s amplio y total finiquito, declaraci&oacute;n que formula libre y espont&aacute;neamente, en perfecto y cabal conocimiento de todos y cada uno de sus derechos.</p>\n<p>Para constancia firman las partes el presente finiquito en dos ejemplares, quedando uno de ellos en poder del empleador y el otro en poder del trabajador.</p>', '2017-06-13 17:55:50', 2017, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE IF NOT EXISTS `contratos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `tipo_contrato_id` varchar(255) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `trabajador_id` int(11) NOT NULL,
  `trabajador_nombre_completo` varchar(255) NOT NULL,
  `trabajador_rut` varchar(255) NOT NULL,
  `trabajador_cargo` varchar(255) NOT NULL,
  `trabajador_seccion` varchar(255) NOT NULL,
  `trabajador_domicilio` varchar(255) NOT NULL,
  `trabajador_estado_civil` varchar(255) NOT NULL,
  `trabajador_fecha_nacimiento` date NOT NULL,
  `encargado_id` int(11) NOT NULL,
  `trabajador_fecha_ingreso` date NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `empresa_rut` varchar(255) NOT NULL,
  `empresa_razon_social` varchar(255) NOT NULL,
  `empresa_domicilio` varchar(255) NOT NULL,
  `empresa_representante_nombre_completo` varchar(255) NOT NULL,
  `empresa_representante_rut` varchar(255) NOT NULL,
  `empresa_representante_domicilio` varchar(255) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas`
--

CREATE TABLE IF NOT EXISTS `cuotas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `prestamo_id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `moneda` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `mes` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuentos`
--

CREATE TABLE IF NOT EXISTS `descuentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `tipo_descuento_id` int(11) NOT NULL,
  `mes_id` int(11) DEFAULT NULL,
  `moneda` varchar(50) NOT NULL,
  `monto` int(11) NOT NULL,
  `por_mes` tinyint(1) NOT NULL,
  `rango_meses` tinyint(1) NOT NULL,
  `permanente` tinyint(1) NOT NULL,
  `todos_anios` tinyint(4) NOT NULL,
  `mes` date DEFAULT NULL,
  `desde` date DEFAULT NULL,
  `hasta` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_liquidacion`
--

CREATE TABLE IF NOT EXISTS `detalle_liquidacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `liquidacion_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `valor` int(11) NOT NULL,
  `valor_2` decimal(10,2) DEFAULT NULL,
  `valor_3` varchar(255) DEFAULT NULL,
  `valor_4` int(11) DEFAULT NULL,
  `valor_5` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE IF NOT EXISTS `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `tipo_documento_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `descripcion` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `feriados`
--

CREATE TABLE IF NOT EXISTS `feriados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `anio_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichas_trabajadores`
--

CREATE TABLE IF NOT EXISTS `fichas_trabajadores` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `trabajador_id` int(11) NOT NULL,
  `mes_id` int(11) NOT NULL DEFAULT '1',
  `fecha` date NOT NULL DEFAULT '2017-01-01',
  `nombres` varchar(255) DEFAULT NULL,
  `apellidos` varchar(255) DEFAULT NULL,
  `nacionalidad_id` int(11) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `estado_civil_id` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` varchar(250) DEFAULT NULL,
  `comuna_id` int(10) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `celular` varchar(255) DEFAULT NULL,
  `celular_empresa` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_empresa` varchar(255) DEFAULT NULL,
  `cargo_id` int(11) DEFAULT NULL,
  `titulo_id` int(11) DEFAULT NULL,
  `seccion_id` int(11) DEFAULT NULL,
  `tipo_cuenta_id` int(11) DEFAULT NULL,
  `banco_id` int(11) DEFAULT NULL,
  `numero_cuenta` varchar(255) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `fecha_reconocimiento` date DEFAULT NULL,
  `tipo_contrato_id` int(11) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_finiquito` date DEFAULT NULL,
  `tipo_jornada_id` int(11) DEFAULT NULL,
  `semana_corrida` tinyint(1) DEFAULT '0',
  `moneda_sueldo` varchar(50) DEFAULT NULL,
  `sueldo_base` decimal(13,3) DEFAULT '0.000',
  `tipo_trabajador` varchar(50) DEFAULT NULL,
  `exceso_retiro` tinyint(1) DEFAULT NULL,
  `gratificacion_mensual` tinyint(1) DEFAULT '0',
  `gratificacion_anual` tinyint(1) DEFAULT '0',
  `moneda_colacion` varchar(50) DEFAULT NULL,
  `proporcional_colacion` tinyint(4) DEFAULT '1',
  `monto_colacion` decimal(13,3) DEFAULT '0.000',
  `moneda_movilizacion` varchar(50) DEFAULT NULL,
  `proporcional_movilizacion` tinyint(4) DEFAULT '1',
  `monto_movilizacion` decimal(13,3) DEFAULT '0.000',
  `moneda_viatico` varchar(50) DEFAULT NULL,
  `proporcional_viatico` int(11) DEFAULT '1',
  `monto_viatico` decimal(13,3) DEFAULT '0.000',
  `afp_id` int(11) DEFAULT NULL,
  `seguro_desempleo` varchar(255) DEFAULT NULL,
  `afp_seguro_id` int(11) DEFAULT NULL,
  `isapre_id` int(11) DEFAULT NULL,
  `cotizacion_isapre` varchar(50) DEFAULT NULL,
  `monto_isapre` decimal(13,3) DEFAULT NULL,
  `sindicato` tinyint(1) DEFAULT NULL,
  `moneda_sindicato` varchar(50) DEFAULT NULL,
  `monto_sindicato` decimal(13,3) DEFAULT '0.000',
  `vacaciones` decimal(6,2) NOT NULL DEFAULT '15.00',
  `tramo_id`  TINYTEXT NULL DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `finiquitos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `folio` int(50) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `encargado_id` int(11) NOT NULL,
  `causal_finiquito_id` int(11) NOT NULL,
  `plantilla_finiquito_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `empresa_razon_social` varchar(255) NOT NULL,
  `empresa_rut` varchar(255) NOT NULL,
  `empresa_direccion` varchar(255) NOT NULL,
  `trabajador_rut` varchar(255) NOT NULL,
  `trabajador_nombre_completo` varchar(255) NOT NULL,
  `trabajador_cargo` varchar(255) NOT NULL,
  `trabajador_seccion` varchar(255) NOT NULL,
  `trabajador_fecha_ingreso` date NOT NULL,
  `trabajador_direccion` varchar(255) NOT NULL,
  `trabajador_provincia` varchar(255) NOT NULL,
  `trabajador_comuna` varchar(255) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `vacaciones` decimal(6,3) NOT NULL,
  `monto_vacaciones` int(11) NOT NULL,
  `sueldo_normal` tinyint(1) NOT NULL,
  `sueldo_variable` tinyint(1) NOT NULL,
  `mes_aviso` int(11) NOT NULL,
  `no_imponibles` int(11) NOT NULL,
  `indemnizacion` int(11) NOT NULL,
  `monto_indemnizacion` int(11) NOT NULL,
  `total_finiquito` int(11) NOT NULL,
  `recibido` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `haberes`
--

CREATE TABLE IF NOT EXISTS `haberes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `tipo_haber_id` int(11) NOT NULL,
  `mes_id` int(11) DEFAULT NULL,
  `moneda` varchar(50) NOT NULL,
  `monto` int(11) NOT NULL,
  `por_mes` tinyint(1) NOT NULL DEFAULT '0',
  `rango_meses` tinyint(1) NOT NULL DEFAULT '0',
  `permanente` tinyint(1) NOT NULL DEFAULT '0',
  `todos_anios` tinyint(1) NOT NULL DEFAULT '0',
  `mes` date DEFAULT NULL,
  `desde` date DEFAULT NULL,
  `hasta` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horas_extra`
--

CREATE TABLE IF NOT EXISTS `horas_extra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `mes_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `cantidad` int(11) NOT NULL,
  `jornada` varchar(50) NOT NULL,
  `observacion` text,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inasistencias`
--

CREATE TABLE IF NOT EXISTS `inasistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `mes_id` int(11) NOT NULL,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `dias` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `observacion` text,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jornadas`
--

CREATE TABLE IF NOT EXISTS `jornadas` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `tramo_hora_extra_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `numero_horas` int(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `jornadas`
--

INSERT INTO `jornadas` (`id`, `sid`, `tramo_hora_extra_id`, `nombre`, `numero_horas`, `updated_at`, `created_at`) VALUES
(1, 'T20170307145827XFT1183', 1, 'Jornada reducida', 30, '2017-03-07 17:58:28', '2017-03-07 17:58:28'),
(2, 'R20170307145832ADJ2555', 2, 'Jornada continuada', 45, '2017-03-07 17:58:33', '2017-03-07 17:58:33'),
(3, 'I20170307145836ABX3588', 1, 'Jornada partida', 20, '2017-03-07 17:58:37', '2017-03-07 17:58:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros_remuneraciones`
--

CREATE TABLE IF NOT EXISTS `libros_remuneraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `empresa_razon_social` varchar(255) NOT NULL,
  `empresa_rut` varchar(10) NOT NULL,
  `empresa_direccion` varchar(255) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `trabajador_nombre` varchar(255) NOT NULL,
  `trabajador_rut` int(11) NOT NULL,
  `sueldo_base` int(11) NOT NULL,
  `total_haberes` int(11) NOT NULL,
  `dias_trabajados` int(11) NOT NULL,
  `sueldo` int(11) NOT NULL,
  `total_afp` int(11) NOT NULL,
  `inasistencias_atrasos` int(11) NOT NULL,
  `total_apv` int(11) NOT NULL,
  `gratificacion` int(11) NOT NULL,
  `total_salud` int(11) NOT NULL,
  `imponibles` int(11) NOT NULL,
  `impuesto_renta` int(11) NOT NULL,
  `horas_extra` decimal(5,2) NOT NULL,
  `total_otros_descuentos` int(11) NOT NULL,
  `total_imponibles` int(11) NOT NULL,
  `anticipos` int(11) NOT NULL,
  `asignacion_familiar` int(11) NOT NULL,
  `seguro_desempleo` int(11) NOT NULL,
  `total_descuentos` int(11) NOT NULL,
  `haberes_no_imponibles` int(11) NOT NULL,
  `sueldo_liquido` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencias`
--

CREATE TABLE IF NOT EXISTS `licencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `mes_id` int(11) NOT NULL,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `dias` int(11) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `observacion` text,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liquidaciones`
--

CREATE TABLE `liquidaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `encargado_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `empresa_razon_social` varchar(255) NOT NULL,
  `empresa_rut` varchar(255) NOT NULL,
  `empresa_direccion` varchar(255) NOT NULL,
  `mes` date NOT NULL,
  `folio` int(50) NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `trabajador_rut` varchar(255) NOT NULL,
  `trabajador_nombres` varchar(255) NOT NULL,
  `trabajador_apellidos` varchar(255) NOT NULL,
  `trabajador_cargo` varchar(255) NOT NULL,
  `trabajador_seccion` varchar(255) NOT NULL,
  `trabajador_fecha_ingreso` date NOT NULL,
  `uf` decimal(8,2) NOT NULL,
  `utm` decimal(8,2) NOT NULL,
  `inasistencias` decimal(5,2) NOT NULL,
  `dias_trabajados` int(11) NOT NULL,
  `horas_extra` decimal(5,2) NOT NULL,
  `total_horas_extra` int(11) NOT NULL,
  `tipo_contrato` varchar(255) NOT NULL,
  `sueldo_base` int(11) NOT NULL,
  `sueldo` int(11) NOT NULL,
  `sueldo_diario` int(11) NOT NULL,
  `sueldo_liquido` int(11) NOT NULL,
  `gratificacion` int(11) NOT NULL,
  `colacion` int(11) NOT NULL,
  `movilizacion` int(11) NOT NULL,
  `viatico` int(11) NOT NULL,
  `total_cargas` int(11) NOT NULL,
  `cantidad_cargas` int(11) NOT NULL,
  `cantidad_cargas_simples` int(11) NOT NULL,
  `cantidad_cargas_maternales` int(11) NOT NULL,
  `cantidad_cargas_invalidas` int(11) NOT NULL,
  `total_apv` int(11) NOT NULL,
  `total_afp_trabajador` int(11) NOT NULL,
  `total_afp_empleador` int(11) NOT NULL,
  `afp_id` int(11) NOT NULL,
  `nombre_afp` varchar(255) NOT NULL,
  `tasa_afp` decimal(10,2) NOT NULL,
  `total_salud` int(11) NOT NULL,
  `total_salud_caja` int(11) NOT NULL,
  `total_salud_fonasa` int(11) NOT NULL,
  `base_salud` int(11) NOT NULL,
  `id_salud` int(11) NOT NULL,
  `nombre_salud` varchar(255) NOT NULL,
  `seguro_cesantia` tinyint(1) NOT NULL,
  `total_seguro_cesantia` int(11) NOT NULL,
  `total_seguro_cesantia_empleador` int(11) NOT NULL,
  `afc_seguro_cesantia` decimal(6,2) NOT NULL,
  `afc_seguro_cesantia_empleador` decimal(6,2) NOT NULL,
  `total_mutual` decimal(6,2) NOT NULL,
  `base_impuesto_unico` int(11) NOT NULL,
  `impuesto_determinado` int(11) NOT NULL,
  `tramo_impuesto` decimal(5,2) NOT NULL,
  `imponibles` int(11) NOT NULL,
  `no_imponibles` int(11) NOT NULL,
  `total_anticipos` int(11) NOT NULL,
  `total_otros_descuentos` int(11) NOT NULL,
  `total_descuentos_previsionales` int(11) NOT NULL,
  `total_descuentos` int(11) NOT NULL,
  `total_haberes` int(11) NOT NULL,
  `total_aportes` int(11) NOT NULL,
  `renta_imponible` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `meses_de_trabajo`
--

CREATE TABLE IF NOT EXISTS `meses_de_trabajo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `mes` date NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `fecha_remuneracion` date NOT NULL,
  `anio_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- Estructura de tabla para la tabla `plantillas_cartas_notificacion`

CREATE TABLE IF NOT EXISTS `plantillas_cartas_notificacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `plantillas_cartas_notificacion`
--

INSERT INTO `plantillas_cartas_notificacion` (`id`, `sid`, `nombre`, `cuerpo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'F20170854793726EQC7203', 'Inasistencias', '<p style="text-align: right;" align="right"><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${comunaEmpresa}, ${fechaPalabras}</span></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">Se&ntilde;or:</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${nombreTrabajador}</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">RUT: ${rutTrabajador}</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${direccionTrabajador}, ${comunaTrabajador}</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${ciudadTrabajador}</span></strong></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp;</span></p>\n<p><strong><u><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">Presente</span></u></strong></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp;</span></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">De nuestra consideraci&oacute;n:</span></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp; &nbsp; Mediante la presente, comunico a Usted que se ha resuelto amonestarlo formalmente, debido a que usted ha faltado sin causa justificada los siguientes d&iacute;as:</span></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${faltas}</span></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp; &nbsp; Le recordamos que de acuerdo a lo estipulado en su contrato de trabajo en la cl&aacute;usula TERCERA&nbsp;dice lo siguiente:</span></p>\n<p style="text-align: center;" align="center"><span style="font-family: Verdana, sans-serif;">&ldquo;La jornada de trabajo ser&aacute; la siguiente de Lunes a Viernes, el horario ser&aacute; de&nbsp;</span>08:30 a 13:00 Hrs. Y de 14:00 a 18:30 Hrs.&rdquo;</p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp; &nbsp; Por lo anterior, le instamos a que supere esta situaci&oacute;n y que en lo sucesivo cumpla las disposiciones establecidas por la empresa a este respecto.</span></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp;</span></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp; &nbsp; Sin otro particular, saluda Atte a Ud.</span></p>\n<p><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">&nbsp;</span></p>\n<p style="text-align: right;" align="right"><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${nombreEmpresa}</span></strong></p>\n<p style="text-align: right;" align="right"><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">R.U.T. ${rutEmpresa}</span></strong></p>', '2017-04-24 00:00:00', '2017-05-03 13:35:19', NULL),
(2, 'P20170502015820WHI4173', 'Término de Contrato', '<p style="text-align: right;"><span style="font-family: Verdana, sans-serif;">${comunaEmpresa}, ${fechaPalabras}</span></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">Se&ntilde;or:</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${nombreTrabajador}</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">RUT: ${rutTrabajador}</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${direccionTrabajador}, ${comunaTrabajador}</span></strong></p>\n<p><strong><span style="font-size: 10.5pt; font-family: Verdana, sans-serif;">${ciudadTrabajador}</span></strong></p>\n<p>&nbsp;</p>\n<p><span style="text-decoration: underline;"><strong>Presente</strong></span></p>\n<p>&nbsp;</p>\n<p style="text-align: left;">&nbsp; &nbsp; Comunico a Ud. que con fecha ${fechaPalabras}, se ha resuelto poner t&eacute;rmino al Contrato de Trabajo que la vinculaba con&nbsp;<strong>${nombreEmpresa}</strong>&nbsp;desde el ${fechaInicialPalabras}&nbsp;en su calidad de ${cargoTrabajador}, de acuerdo a lo establecido en el Art&iacute;culo 160 N&deg; 3 del C&oacute;digo del Trabajo, esto es, &ldquo;No concurrencia del trabajador a sus labores sin causa justificada durante dos d&iacute;as seguidos, dos lunes en el mes o un total de tres d&iacute;as durante igual periodo de tiempo&rdquo;.</p>\n<p style="text-align: left;">&nbsp; &nbsp; Los hechos en que se funda la causal invocada se sustentan, en que usted ha faltado sin causa justificada dos d&iacute;as seguidos y un total de tres en igual per&iacute;odo de tiempo, esto es, ${faltasLineal}, hasta la fecha usted no se ha comunicado con la empresa. Seg&uacute;n consta en su contrato de trabajo, por turno le correspond&iacute;a presentarse los d&iacute;as:</p>\n<p>&nbsp;</p>\n<p><span style="font-family: Verdana, sans-serif;">${faltas}</span></p>\n<p>&nbsp;</p>\n<p>&nbsp; &nbsp; Informo a Ud. que sus cotizaciones previsionales, de salud y seguro de cesant&iacute;a han sido integras y oportunamente enteradas en los organismos pertinentes.</p>\n<p>&nbsp; &nbsp; Asimismo se hace entrega del certificado correspondiente de acuerdo a la Ley N&deg; 19.631.</p>\n<p>&nbsp; &nbsp; Se deja constancia que su finiquito estar&aacute; a su disposici&oacute;n en nuestras oficinas ubicada en ${domicilioEmpresa}&nbsp;Santiago el d&iacute;a FECHA. Saluda atentamente a Ud.,</p>\n<p style="text-align: center;"><strong>${nombreEmpresa}</strong></p>\n<p style="text-align: center;"><strong>RUT:&nbsp;</strong><strong>${rutEmpresa}</strong></p>', '2017-05-02 01:58:21', '2017-05-02 20:16:33', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_certificados`
--

CREATE TABLE IF NOT EXISTS `plantillas_certificados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `plantillas_certificados`
--

INSERT INTO `plantillas_certificados` (`id`, `sid`, `nombre`, `cuerpo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'H20170503141834UJK3074', 'Certificado de Antigüedad Laboral', '<p style="text-align: center;">EMPRESA</p>\n<p style="text-align: center;">&nbsp;</p>\n<p style="text-align: center;">${nombreEmpresa}</p>\n<p style="text-align: center;">&nbsp;</p>\n<p style="text-align: center;"><strong>CERTIFICADO DE ANTIG&Uuml;EDAD</strong></p>\n<p style="text-align: center;">&nbsp;</p>\n<p>El suscrito, certifica que el Sr. (a) ${nombreTrabajador}, RUT: ${rutTrabajador}, es funcionario de la Empresa ${nombreEmpresa} desde el ${fechaInicialPalabras}, ocupando el cargo de ${cargoTrabajador}.</p>\n<p>&nbsp;</p>\n<p>Su contrato de trabajo es de car&aacute;cter ${contratoTrabajador}.</p>\n<p>&nbsp;</p>\n<p>Se otorga el presente certificado a pedido del interesado para los fines que estime conveniente.</p>\n<p>&nbsp;</p>\n<p>${ciudadEmpresa}, ${fechaPalabras}.</p>\n<p>&nbsp;</p>\n<p>&nbsp;</p>\n<p style="text-align: center;">${nombreRepresentanteEmpresa}</p>\n<p style="text-align: center;">Gerente General</p>\n<p style="text-align: center;">${nombreEmpresa}</p>\n<p style="text-align: center;">&nbsp;</p>', '2017-05-03 14:18:35', '2017-05-03 15:58:49', NULL),
(2, 'O20170503142149UHJ6212', 'Certificado de Remuneraciones', '<p style="text-align: center;">EMPRESA</p>\n<p style="text-align: center;">&nbsp;</p>\n<p style="text-align: center;">${nombreEmpresa}</p>\n<p style="text-align: center;">&nbsp;</p>\n<p style="text-align: center;"><strong>CERTIFICADO DE ACREDITACI&Oacute;N DE RENTA MENSUAL</strong></p>\n<p style="text-align: center;">&nbsp;</p>\n<p>El suscrito, certifica que el Sr. (a) ${nombreTrabajador}, RUT: ${rutTrabajador}, es funcionario de la Empresa ${nombreEmpresa}, dispone de una renta l&iacute;quida mensual de ${sueldoBase} (${sueldoBasePalabras}) esto sin descontar pr&eacute;stamos.</p>\n<p>&nbsp;&nbsp;</p>\n<p>Se otorga el presente certificado a pedido del interesado para los fines que estime conveniente.</p>\n<p>&nbsp;</p>\n<p>${ciudadEmpresa}, ${fechaPalabras}.</p>\n<p>&nbsp;</p>\n<p>&nbsp;</p>\n<p style="text-align: center;">${nombreRepresentanteEmpresa}</p>\n<p style="text-align: center;">Gerente General</p>\n<p style="text-align: center;">${nombreEmpresa}</p>\n<p style="text-align: center;">&nbsp;</p>', '2017-05-03 14:21:50', '2017-05-03 16:04:43', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_contratos`
--

CREATE TABLE IF NOT EXISTS `plantillas_contratos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `plantillas_contratos`
--

INSERT INTO `plantillas_contratos` (`id`, `sid`, `nombre`, `cuerpo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'G20172547893540EQC7203', 'Contrato Normal', '<p style="text-align: center;">&nbsp;</p>\n<p style="text-align: center;"><strong>Contrato Individual de&nbsp;Trabajo</strong></p>\n<p style="text-align: center;">&nbsp;</p>\n<p>En ${comunaEmpresa}, a ${fechaPalabras}, entre la empresa <strong>${nombreEmpresa}</strong> RUT: <strong>${rutEmpresa}</strong>, representada por don <strong>${nombreRepresentanteEmpresa}</strong> en su calidad de Representante legal c&eacute;dula de identidad N&deg; <strong>${rutRepresentanteEmpresa}</strong>, con domicilio en ${domicilioRepresentanteEmpresa}, en adelante, "el empleador"; y don <strong>${nombreTrabajador}</strong> de nacionalidad ${nacionalidadTrabajador}, c&eacute;dula de identidad N&deg; <strong>${rutTrabajador}</strong>, de estado civil ${estadoCivilTrabajador}, fecha de nacimiento ${fechaNacimientoPalabrasTrabajador}, con el cargo de ${cargoTrabajador}, domiciliado en ${domicilioTrabajador}, y, en consecuencia, capaz de celebrar contrato de trabajo en adelante "el trabajador"; las partes han convenido celebrar el presente contrato de trabajo al tenor de las siguientes:</p>\n<p><strong>CL&Aacute;USULAS:</strong></p>\n<p>${clausulas}</p>\n<p><span style="text-decoration: underline;"><strong>Se deja constancia que el trabajador:</strong></span></p>\n<p>Est&aacute; afiliado a Instituci&oacute;n Previsional AFP o R&eacute;gimen Antiguo: <strong>AFP ${trabajadorAfp}</strong></p>\n<p>Est&aacute; afiliado a Instituci&oacute;n de Salud ISAPREo FONASA: <strong>${trabajadorIsapre}</strong></p>', '2017-04-26 00:00:00', '2017-04-28 16:03:59', NULL),
(2, 'X20170502163651OVU2843', 'Contrato a extranjeros', '<p style="text-align: center;">&nbsp;</p>\n<p style="text-align: center;"><span style="text-decoration: underline;"><strong>CONTRATO DE TRABAJO EXTRANJERO VISA TEMPORARIA POR MOTIVOS LABORALES</strong></span></p>\n<p style="text-align: center;">&nbsp;</p>\n<p>En ${comunaEmpresa}, al <strong>${fechaPalabras}</strong>, entre&nbsp;<strong>${nombreEmpresa}</strong>&nbsp;RUT:&nbsp;<strong>${rutEmpresa}</strong>, representado(a) por&nbsp;<strong>${nombreRepresentanteEmpresa}</strong>, c&eacute;dula de identidad <strong>${rutRepresentanteEmpresa}</strong>&nbsp;domiciliado en <strong>${domicilioRepresentanteEmpresa}</strong>, que en adelante se denominar&aacute; "el(la) empleador(a)"; y don&nbsp;<strong>${nombreTrabajador}</strong>, de nacionalidad <strong>${nacionalidadTrabajador}</strong>, nacido el <strong>${fechaNacimientoPalabrasTrabajador}</strong>, c&eacute;dula nacional de identidad&nbsp;<strong>${rutTrabajador}</strong>, de profesi&oacute;n u oficio <strong>${cargoTrabajador}</strong>, de estado civil <strong>${estadoCivilTrabajador}</strong>, domiciliado(a) en <strong>${domicilioTrabajador}</strong>, que en adelante se denominar&aacute; "el trabajador", se ha convenido en el siguiente contrato de trabajo:</p>\n<p>&nbsp;</p>\n<p><strong>CL&Aacute;USULAS:</strong></p>\n<p>${clausulas}</p>\n<p>&nbsp;</p>\n<p>&nbsp;</p>', '2017-05-02 16:36:52', '2017-05-02 16:36:52', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_finiquitos`
--

CREATE TABLE IF NOT EXISTS `plantillas_finiquitos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cuerpo` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `plantillas_finiquitos`
--

INSERT INTO `plantillas_finiquitos` (`id`, `sid`, `nombre`, `cuerpo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'J20170613165602UTX9296', 'Finiquito Normal', '<p style="text-align: center;"> </p>\n<p style="text-align: center;"><strong>Finiquito</strong></p>\n<p style="text-align: center;"> </p>\n<p>En ${comunaEmpresa}, a ${fechaPalabras}, entre <strong>${nombreEmpresa}</strong> RUT: <strong>${rutEmpresa}</strong>, con domicilio en ${domicilioEmpresa}, en adelante, "el empleador"; y don(ña) <strong>${nombreTrabajador}</strong>, RUT: <strong>${rutTrabajador}</strong>, con domicilio en ${domicilioTrabajador}, de nacionalidad ${nacionalidadTrabajador}, nacido(a) el ${fechaNacimientoPalabrasTrabajador}, en adelante "el trabajador", por otra parte, se conviene el siguiente finiquito:</p>\n<p> </p>\n<p><strong>CLÁUSULAS:</strong></p>\n<p>${clausulas}</p>\n<p> </p>', '2017-06-13 16:56:03', '2017-06-13 17:44:36', NULL);

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `prestamos`


CREATE TABLE IF NOT EXISTS `prestamos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `glosa` varchar(255) NOT NULL,
  `nombre_liquidacion` varchar(50) NOT NULL,
  `prestamo_caja` tinyint(1) DEFAULT NULL,
  `leassing_caja` tinyint(1) DEFAULT NULL,
  `moneda` varchar(50) NOT NULL,
  `monto` int(11) NOT NULL,
  `cuotas` int(11) NOT NULL,
  `primera_cuota` date NOT NULL,
  `ultima_cuota` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

CREATE TABLE IF NOT EXISTS `secciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `dependencia_id` int(11) DEFAULT NULL,
  `encargado_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id`, `sid`, `nombre`, `dependencia_id`, `encargado_id`, `created_at`, `updated_at`) VALUES
(1, 'E20170322172444LCN3635', 'Gerencia', 0, NULL, '2017-03-22 00:00:00', '2017-03-22 00:00:00'),
(2, 'W20170322173457CLF8136', 'Gerencia Finanzas', 1, NULL, '2017-03-22 00:00:00', '2017-03-22 00:00:00'),
(3, 'X20170322173527CHJ4287', 'Gerencia Desarrollo', 1, NULL, '2017-03-22 00:00:00', '2017-03-24 00:00:00'),
(4, 'K20170322173650QIS2033', 'Gerencia Operaciones', 1, NULL, '2017-03-22 00:00:00', '2017-03-22 00:00:00');


-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `semana_corrida`
--

CREATE TABLE IF NOT EXISTS `semana_corrida` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `mes` date NOT NULL,
  `semana_1` int(11) NOT NULL,
  `semana_2` int(11) NOT NULL,
  `semana_3` int(11) NOT NULL,
  `semana_4` int(11) NOT NULL,
  `semana_5` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Estructura de tabla para la tabla `tipos_carga`
--

CREATE TABLE IF NOT EXISTS `tipos_carga` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `sid` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


INSERT INTO `tipos_carga` (`id`, `nombre`, `sid`, `updated_at`, `created_at`) VALUES
(1, 'Carga Simple', 'X20170330153548TLU6143', '2017-04-06 23:48:31', '2017-03-30 18:35:49'),
(2, 'Carga Maternal', 'X20170330153548TLU6143', '2017-04-06 23:48:31', '2017-03-30 18:35:49'),
(3, 'Carga Inválida', 'X20170330153548TLU6143', '2017-04-06 23:48:31', '2017-03-30 18:35:49');


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_contrato`
--

CREATE TABLE IF NOT EXISTS `tipos_contrato` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Volcado de datos para la tabla `tipos_contrato`
--

INSERT INTO `tipos_contrato` (`id`, `sid`, `nombre`, `updated_at`, `created_at`) VALUES
(1, 'U20170307145806OHT5940', 'Indefinido', '2017-03-07 17:58:07', '2017-03-07 17:58:07'),
(2, 'S20170206132453THF2224', 'Plazo fijo', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(3, 'H20170012125415BID6522', 'Tácito', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(4, 'Z20151006010307NZA2853', 'De prueba', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(5, 'B20170212102343KJS7565', 'Por obra cierta', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(6, 'F20170207105402BFX8881', 'Por tarea', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(7, 'Y20170206131557LMK4414', 'Por destajo', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(8, 'Y20170206124456RKU2865', 'Eventual', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(9, 'N20170206112358GLP8680', 'Por temporada', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(10, 'M20170206112338TQY6202', 'Ocacional', '2017-07-03 16:12:03', '2015-07-03 21:57:09'),
(11, 'Y20151000022335UUD1861', 'Parcial permanente', '2017-07-03 16:12:03', '2015-07-03 21:57:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_descuento`
--

CREATE TABLE IF NOT EXISTS `tipos_descuento` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cuenta_id` int(10) NULL DEFAULT NULL,
  `sid` varchar(50) NOT NULL,
  `codigo` int(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `caja` tinyint(1) DEFAULT NULL,
  `descripcion` text,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

INSERT INTO `tipos_descuento` (`id`, `cuenta_id`, `sid`, `codigo`, `nombre`, `caja`, `descripcion`, `updated_at`, `created_at`) VALUES
(1, NULL, 'K20170309190412CWL7448', 1, 'APVC', 1, 'Ahorro Previsional Voluntario Colectivo', '2017-08-21 18:05:45', '2017-03-09 22:04:13'),
(2, NULL, 'Y20170309190530OKA1024', 2, 'Cuenta de Ahorro AFP', 1, 'Cuenta de Ahorro AFP', '2017-08-17 12:21:37', '2017-03-09 22:05:31'),
(3, NULL, 'L20170309190616GCZ2827', 3, 'Sobregiro Mes Anterior', 1, 'Sobregiro Remuneración del Mes Anterior', '2017-08-17 12:22:13', '2017-03-09 22:06:17'),
(4, NULL, 'Z20170822155813OOJ4352', 4, 'Impuesto Determinado', 1, 'Impuesto Determinado', '2017-08-22 18:58:14', '2017-08-22 18:58:14'),
(5, NULL, 'B20170407131109SUM7022', 5, 'Anticipo', 1, NULL, '2017-04-13 01:37:51', '2017-04-07 16:11:10'),
(6, NULL, 'J20170508172723POM7298', 123, 'Anticipo Bono Vacaciones', 0, NULL, '2017-08-17 12:09:02', '2017-05-08 20:27:23'),
(7, NULL, 'K20170309190412CWL7447', 123, 'Cuota Sindical', 1, 'Cuota fijada por el Sindicato de Trabajadores', '2017-03-09 22:04:13', '2017-03-09 22:04:13'),
(8, NULL, 'Y20170309190530OKA1021', 1112, 'Celular', 1, 'Celular de la Empresa', '2017-04-13 03:26:59', '2017-03-09 22:05:31'),
(9, NULL, 'L20170309190616GCZ2867', 853, 'Seguro Médico', 0, 'Seguro médico contratado por el trabajador de forma adicional al seguro de la empresa', '2017-03-18 06:18:45', '2017-03-09 22:06:17'),
(10, NULL, 'V20170412232326TER7208', 4564, 'Autopista', 0, 'Tag', '2017-08-17 12:08:06', '2017-04-13 02:23:27');


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_documento`
--

CREATE TABLE IF NOT EXISTS `tipos_documento` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `sid` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Volcado de datos para la tabla `tipos_documento`
--

INSERT INTO `tipos_documento` (`id`, `nombre`, `sid`, `updated_at`, `created_at`) VALUES
(1, 'Contrato de Trabajo', 'W20170526140303QZD7867', '2017-05-26 18:03:04', '2017-05-26 18:03:04'),
(2, 'Certificado', 'X20170330153548TLU6143', '2017-04-06 23:48:31', '2017-03-30 18:35:49'),
(3, 'Carta de Notificación', 'J20170526140329GBW5284', '2017-05-26 18:03:30', '2017-05-26 18:03:30'),
(4, 'Liquidación de Sueldo', 'Q20170607211128XOD9638', '2017-06-08 01:11:29', '2017-06-08 01:11:29'),
(5, 'Finiquito', 'V20170619131724RIK1431', '2017-06-19 17:17:25', '2017-06-19 17:17:25'),
(6, 'Licencia Médica', 'W20170526140317IQP3935', '2017-05-26 18:03:19', '2017-05-26 18:03:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_haber`
--

CREATE TABLE IF NOT EXISTS `tipos_haber` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cuenta_id` int(10) NULL DEFAULT NULL,
  `codigo` int(20) NOT NULL,
  `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tributable` tinyint(1) DEFAULT '0',
  `calcula_horas_extras` tinyint(1) DEFAULT '0',
  `proporcional_dias_trabajados` tinyint(1) DEFAULT '0',
  `calcula_semana_corrida` tinyint(1) DEFAULT '0',
  `imponible` tinyint(1) DEFAULT '0',
  `gratificacion` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;


INSERT INTO `tipos_haber` (`id`, `cuenta_id`, `codigo`, `sid`, `nombre`, `tributable`, `calcula_horas_extras`, `proporcional_dias_trabajados`, `calcula_semana_corrida`, `imponible`, `gratificacion`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 1, 'F20170822161948ZFK3154', 'Gratificación', NULL, NULL, NULL, NULL, 1, NULL, '2017-08-22 19:19:49', '2017-08-22 19:19:49', NULL),
(2, NULL, 78, 'V20170309184513UVS6237', 'Comisiones por Ventas', 0, 0, NULL, NULL, 1, 0, '2017-03-09 21:45:14', '2017-08-18 19:50:52', NULL),
(3, NULL, 108, 'B20170309184539DPH2907', 'Comisiones Productos', 0, 0, NULL, NULL, 1, 0, '2017-03-09 21:45:40', '2017-04-13 03:27:30', NULL),
(4, NULL, 121, 'M20170313201430ZQX3718', 'Bono por Metas', 1, 1, NULL, NULL, 0, 0, '2017-03-14 00:14:31', '2017-08-21 18:15:51', NULL),
(5, NULL, 78975, 'B20170412235220VDS7547', 'Bono Vacaciones', NULL, NULL, NULL, NULL, 0, NULL, '2017-04-13 02:52:21', '2017-04-13 02:52:21', NULL),
(6, NULL, 88, 'P20170413135040AUD1153', 'Otros No Imponible', NULL, NULL, NULL, NULL, 0, NULL, '2017-04-13 16:50:41', '2017-04-13 16:50:41', NULL),
(7, NULL, 58, 'E20170309184434AUW3466', 'Com. Cobranza Sucursa', 1, 0, NULL, NULL, 1, 0, '2017-03-09 21:44:35', '2017-08-21 18:14:17', NULL),
(8, NULL, 3, 'U20170511111154RAR1734', 'Asignación Telefono', NULL, NULL, NULL, NULL, 0, NULL, '2017-05-11 14:11:54', '2017-05-11 14:11:54', NULL),
(9, NULL, 4, 'B20170511122150SDF2586', 'Comision Bodega y Distribución', NULL, NULL, NULL, NULL, 1, NULL, '2017-05-11 15:21:50', '2017-05-11 15:21:50', NULL),
(10, NULL, 5, 'Y20170511122554DIY9550', 'Comisiones aseguradas', NULL, NULL, NULL, NULL, 1, NULL, '2017-05-11 15:25:54', '2017-05-11 15:25:54', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `titulos`
--

CREATE TABLE IF NOT EXISTS `titulos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
    `sid` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Volcado de datos para la tabla `titulos`
--
INSERT INTO `titulos` (`id`, `sid`, `nombre`, `updated_at`, `created_at`) VALUES
(1, 'H20137648199415BID6522', 'Administración de Empresas', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'M20170508125358QUX7204', 'Ingeniero Comercial', '2017-05-08 15:53:58', '2017-05-08 15:53:58'),
(3, 'J20170508152415YDU1192', 'Programador Computación', '2017-05-08 18:24:15', '2017-05-08 18:24:15'),
(4, 'D20170508153458VHH5597', 'Técnico Automatización Control Industrial', '2017-05-08 18:34:58', '2017-05-08 18:34:58'),
(5, 'K20170508155430DAR8358', 'Ingeniero en Prevención de Riesgos', '2017-05-08 18:54:30', '2017-05-08 18:54:30'),
(6, 'V20170508160540JOK1670', 'Secretaria', '2017-05-08 19:05:40', '2017-05-08 19:05:40'),
(7, 'K20170508161448QDG1176', 'Técnico Superior en Electricidad', '2017-05-08 19:14:48', '2017-05-08 19:14:48'),
(8, 'X20170508162736DHV8656', 'Técnico en Contabilidad', '2017-05-08 19:27:36', '2017-05-08 19:27:36'),
(9, 'J20170508165328BDQ2419', 'Técnico Electromecánico', '2017-05-08 19:53:28', '2017-05-08 19:53:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE IF NOT EXISTS `trabajadores` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `rut` varchar(15) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tramos_horas_extra`
--

CREATE TABLE IF NOT EXISTS `tramos_horas_extra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `jornada` varchar(255) NOT NULL,
  `factor` decimal(10,9) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Volcado de datos para la tabla `tramos_horas_extra`
--

INSERT INTO `tramos_horas_extra` (`id`, `sid`, `jornada`, `factor`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'F20170420134048DNG7742', '4x3', '0.007777700', '2017-04-20 13:40:49', '2017-04-20 13:40:49', NULL),
(2, 'E20170420134154AZZ6361', '4x4', '0.008333300', '2017-04-20 13:41:55', '2017-04-20 13:41:55', NULL),
(3, 'I20170420134208SDW5655', '7x7', '0.008333300', '2017-04-20 13:42:09', '2017-04-20 13:42:09', NULL),
(4, 'K20170420134220OSL2037', '8x6', '0.007777700', '2017-04-20 13:42:22', '2017-04-20 13:42:22', NULL),
(5, 'B20170420134239GUP5892', '10x10', '0.008333300', '2017-04-20 13:42:40', '2017-04-20 13:42:40', NULL),
(6, 'Y20170420134318HER2877', '14x7', '0.007812500', '2017-04-20 13:43:19', '2017-04-20 13:43:19', NULL),
(7, 'B20170420134327SAH7447', '20x10', '0.007812500', '2017-04-20 13:43:28', '2017-04-20 14:51:16', NULL);

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `vacaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `mes_id` int(11) NOT NULL,
  `dias` decimal(5,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variables_sistema`
--

CREATE TABLE IF NOT EXISTS `variables_sistema` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `variable` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `valor1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `valor2` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `valor3` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `valor4` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `valor5` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:GestionCuentasCtrl
 * @description
 * # GestionCuentasCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('GestionCuentasCtrl', function ($scope, $uibModal, tipoHoraExtra, cuenta, $filter, tipoHaber, tipoDescuento, $anchorScroll, aporte, constantes, mesDeTrabajo, $rootScope, Notification) {
    $anchorScroll();

    $scope.empresa = $rootScope.globals.currentUser.empresa;
    $scope.constantes = constantes;
    $scope.cargado = false;    
    
    $scope.tabGeneral = true;
    $scope.tabAportes = false;
    $scope.tabAfpEmpleador = false;
    $scope.tabAfpTrabajador = false;
    $scope.tabSalud = false;
    $scope.tabSeguroCesantiaEmpleador = false;
    $scope.tabSeguroCesantiaTrabajador = false;
    $scope.tabCuentasAhorroAfp = false;
    $scope.tabApvA = false;
    $scope.tabApvB = false;
    $scope.tabApvc = false;
    $scope.tabCCAF = false;
    $scope.tabExCaja = false;
    $scope.tabHaberesImp = false;
    $scope.tabHaberesNoImp = false;
    $scope.tabDescuentos = false;
    $scope.tabDescuentosLegales = false;

    $scope.openTab = function(tab){
      switch (tab) {
        case 'generales':
          $scope.tabGeneral = true;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'generales', datos : $scope.generales, nombre : 'General', tipo : 'aporte' };
          break;
        case 'aportes':
          $scope.tabGeneral = false;
          $scope.tabAportes = true;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'aportes', datos : $scope.aportes, nombre : 'Aportes', tipo : 'aporte' };
          break;
        case 'afpEmpleador':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = true;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'afpEmpleador', datos : $scope.afpsEmpleador, nombre : 'AFP Empleador', tipo : 'aporte' };
          break;
        case 'afpTrabajador':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = true;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'afpTrabajador', datos : $scope.afpsTrabajador, nombre : 'AFP Trabajador', tipo : 'aporte' };
          break;
        case 'cuentasAhorroAfp':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = true;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'cuentasAhorroAfp', datos : $scope.cuentasAhorroAfps, nombre : 'Cuentas de Ahorro', tipo : 'descuento' };
          break;
        case 'salud':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = true;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'salud', datos : $scope.salud, nombre : 'Salud', tipo : 'descuento' };
          break;
        case 'seguroCesantiaEmpleador':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = true;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'seguroCesantiaEmpleador', datos : $scope.seguroCesantiaEmpleador, nombre : 'Seguro Cesantía Empleador', tipo : 'aporte' };
          break;
        case 'seguroCesantiaTrabajador':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = true;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'seguroCesantiaTrabajador', datos : $scope.seguroCesantiaTrabajador, nombre : 'Seguro Cesantía Trabajador', tipo : 'aporte' };
          break;
        case 'apvA':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = true;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'apvA', datos : $scope.apvsA, nombre : 'APVs Régimen A', tipo : 'descuento' };
          break;
        case 'apvB':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = true;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'apvB', datos : $scope.apvsB, nombre : 'APVs Régimen B', tipo : 'descuento' };
          break;
        case 'apvc':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = true;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'apvc', datos : $scope.apvcs, nombre : 'APVCs', tipo : 'descuento' };
          break;
        case 'exCaja':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = true;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'exCaja', datos : $scope.exCajas, nombre : 'Ex Cajas', tipo : 'aporte' };
          break;
        case 'ccaf':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = true;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'ccaf', datos : $scope.ccafs, nombre : 'CCAF', tipo : 'descuento' };
          break;
        case 'imp':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = true;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'imp', datos : $scope.haberesImp, nombre : 'Haberes Imponibles', tipo : 'haber' };
          break;
        case 'noImp':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = true;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales= false;
          $scope.select = { tab : 'noImp', datos : $scope.haberesNoImp, nombre : 'Haberes No Imponibles', tipo : 'haber' };
          break;
        case 'descuentos':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = true;
          $scope.tabDescuentosLegales = false;
          $scope.select = { tab : 'descuentos', datos : $scope.descuentos, nombre : 'Descuentos', tipo : 'descuento' };
          break;
        case 'descuentosLegales':
          $scope.tabGeneral = false;
          $scope.tabAportes = false;
          $scope.tabAfpEmpleador = false;
          $scope.tabAfpTrabajador = false;
          $scope.tabSalud = false;
          $scope.tabSeguroCesantiaEmpleador = false;
          $scope.tabSeguroCesantiaTrabajador = false;
          $scope.tabCuentasAhorroAfp = false;
          $scope.tabApvA = false;
          $scope.tabApvB = false;
          $scope.tabApvc = false;
          $scope.tabCCAF = false;
          $scope.tabExCaja = false;
          $scope.tabHaberesImp = false;
          $scope.tabHaberesNoImp = false;
          $scope.tabDescuentos = false;
          $scope.tabDescuentosLegales = true;
          $scope.select = { tab : 'descuentosLegales', datos : $scope.descuentosLegales, nombre : 'Descuentos Legales', tipo : 'descuento' };
          break;
      }
    }
    
    $scope.editar = function(apo, tab){
      $rootScope.cargando = true;
      if($scope.empresa.centroCosto.isCentroCosto){
        var datos = aporte.centroCosto().get({ sid: apo.sid });
        datos.$promise.then(function(response){
          openFormCentrosCosto(response.datos, response.cuentas, 'aporte', tab, response.centrosCostos);
          $rootScope.cargando = false;      
        }); 
      }else{
        var datos = aporte.datos().get({ sid: apo.sid });
        datos.$promise.then(function(response){
          openForm(response.datos, response.cuentas, 'aporte', tab);            
          $rootScope.cargando = false;      
        }); 
      }
    }

    $scope.editarHaber = function(apo, tab){
      console.log(apo)
      $rootScope.cargando = true;
      if($scope.empresa.centroCosto.isCentroCosto){
        if(apo.isHoraExtra){
          var datos = tipoHoraExtra.centroCosto().get({ sid: apo.sid });          
        }else{
          var datos = tipoHaber.centroCosto().get({ sid: apo.sid });          
        }
        datos.$promise.then(function(response){
          openFormCentrosCosto(response.datos, response.cuentas, 'haber', tab, response.centrosCostos);
          $rootScope.cargando = false;      
        }); 
      }else{
        if(apo.isHoraExtra){
          var datos = tipoHoraExtra.cuenta().get({ sid: apo.sid });          
        }else{
          var datos = tipoHaber.cuenta().get({ sid: apo.sid });          
        }
        datos.$promise.then(function(response){
          openForm(response.datos, response.cuentas, 'haber', tab);
          $rootScope.cargando = false;      
        }); 
      }
    }

    $scope.editarDescuento = function(apo, tab){
      $rootScope.cargando = true;
      if($scope.empresa.centroCosto.isCentroCosto){
        var datos = tipoDescuento.centroCosto().get({ sid: apo.sid });
        datos.$promise.then(function(response){
          openFormCentrosCosto(response.datos, response.cuentas, 'descuento', tab, response.centrosCostos);
          $rootScope.cargando = false;      
        }); 
      }else{
        var datos = tipoDescuento.cuenta().get({ sid: apo.sid });
        datos.$promise.then(function(response){
          openForm(response.datos, response.cuentas, 'descuento', tab); 
          $rootScope.cargando = false;      
        }); 
      }
    }

    $scope.masivo = function(){
      $rootScope.cargando = true;
      var datos = cuenta.obtener().get();
      datos.$promise.then(function(response){
        openMasivo($scope.select, response.datos); 
        $rootScope.cargando = false;      
      });
    }

    function openMasivo(select, cuentas){
      var miModal = $uibModal.open({
        animation: $scope.animationsEnabled,
        templateUrl: 'views/forms/form-cuentas-masivo.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormAsignacionMasiva',
        resolve: {
          objeto: function() {
            return select;
          },
          cuentas: function() {
            return cuentas;
          }
        }
      });
      miModal.result.then(function(obj) {
        Notification.success({message: obj.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos();
        $scope.openTab(obj.tab);
      }, function () {
        javascript:void(0);
      });
    }

    function openForm(obj, cuentas, tipo, tab) {
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-cuenta.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormCuentaCtrl',
        resolve: {
          objeto: function () {
            return obj;
          },
          cuentas: function () {
            return cuentas;
          },
          tipo: function () {
            return tipo;
          },
          tab: function () {
            return tab;
          }
        }
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos();
        $scope.openTab(object.tab);
      }, function () {
        javascript:void(0);
      });
    };

    function openFormCentrosCosto(obj, cuentas, tipo, tab, centrosCostos) {
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-cuenta-centro-costo.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormCuentaCentrosCostoCtrl',
        size: 'lg',
        resolve: {
          objeto: function () {
            return obj;
          },
          cuentas: function () {
            return cuentas;
          },
          centrosCostos: function () {
            return centrosCostos;
          },
          tipo: function () {
            return tipo;
          },
          tab: function () {
            return tab;
          }
        }
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos();
        //$scope.openTab(object.tab);
      }, function () {
        cargarDatos();
      });
    };

    function cargarDatos(){
      $scope.cargado = false;
      $rootScope.cargando = true;
      var datos = aporte.datos().get();
      datos.$promise.then(function(response){
        $scope.accesos = response.accesos;
        //$scope.isCuentas = response.isCuentas;
        $scope.aportes = response.aportes;
        $scope.afpsEmpleador = response.afpsEmpleador;
        $scope.afpsTrabajador = response.afpsTrabajador;
        $scope.salud = response.salud;
        $scope.seguroCesantiaEmpleador = response.seguroCesantiaEmpleador;
        $scope.seguroCesantiaTrabajador = response.seguroCesantiaTrabajador;
        $scope.cuentasAhorroAfps = response.cuentasAhorroAfps;
        $scope.apvsA = response.apvsA;
        $scope.apvsB = response.apvsB;
        $scope.apvcs = response.apvcs;
        $scope.ccafs = response.ccafs;
        $scope.exCajas = response.exCajas;
        $scope.haberesImp = response.haberesImp;
        $scope.haberesNoImp = response.haberesNoImp;
        $scope.descuentos = response.descuentos;
        $scope.descuentosLegales = response.descuentosLegales;
        $scope.generales = response.generales;
        $scope.centrosCostos = response.centrosCostos;
        $rootScope.cargando = false;
        $scope.cargado = true;
        $scope.select = { tab : 'generales', datos : $scope.generales, nombre : 'General', tipo : 'aporte' };
      });
    };

    cargarDatos();

  })
  .controller('FormBuscarCuentaCtrl', function ($scope, $uibModalInstance, objeto, Notification, $rootScope, tipoHaber) {
    $scope.datos = angular.copy(objeto);    

    $scope.tiposCuenta = [
      { id:0, tipo : 'Todos'},
      { id:1, tipo : 'Activos'},
      { id:2, tipo : 'Pasivos'},
      { id:3, tipo : 'Perdidas'},
      { id:4, tipo : 'Ganancias'}
    ];

    $scope.filtro={
      filtro:'',
      tipoCuenta : $scope.tiposCuenta[0]
    };

    $scope.tipoCodigo = function(cuenta){
      if( $scope.filtro.tipoCuenta.id > 0 ){
        if( cuenta.codigo.indexOf($scope.filtro.tipoCuenta.id) === 0 ){
          return true;
        }else{
          return false;
        }
      }else{
        return true;
      }
    };

    $scope.seleccionar=function(cta){
      $uibModalInstance.close(cta);
    };


  })
  .controller('FormCuentaCentrosCostoCtrl', function ($scope, tipo, centrosCostos, tipoHoraExtra, tipoHaber, tab, tipoDescuento, $uibModalInstance, $filter, $uibModal, mesDeTrabajo, cuentas, objeto, Notification, $rootScope, aporte) {

    $scope.empresa = $rootScope.globals.currentUser.empresa;
    $scope.centrosCostos = angular.copy(centrosCostos);
    $scope.cuentas = angular.copy(cuentas);
    $scope.objeto = angular.copy(objeto);
    $scope.tipo = angular.copy(tipo);    
    $scope.titulo = 'Modificación de Cuentas de Aportes';
    $scope.encabezado = $scope.objeto.nombre;
    $scope.isSelect = false;
    $scope.cargado = true;console.log(objeto)

    $scope.guardar = function () {
      $rootScope.cargando=true;
      var cc = { 'centrosCosto' : $scope.centrosCostos, 'concepto' : $scope.tipo, 'idConcepto' : $scope.objeto.id }

      if($scope.tipo==='aporte'){
        var response = aporte.updateCuentaCentrosCosto().post({}, cc);
      }else if($scope.tipo==='haber'){
        if(objeto.isHoraExtra){
          var response = tipoHoraExtra.updateCuentaCentrosCosto().post({}, cc);
        }else{
          var response = tipoHaber.updateCuentaCentrosCosto().post({}, cc);          
        }
      }else if($scope.tipo==='descuento'){
        var response = tipoDescuento.updateCuentaCentrosCosto().post({}, cc);
      }

      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, tab : tab});
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    };

    $scope.seleccionar = function(select){
      $scope.isSelect = select;
    }

    $scope.openCuentas = function(obj){
      var miModal = $uibModal.open({
        animation: $scope.animationsEnabled,
        templateUrl: 'views/forms/form-buscar-cuenta.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormBuscarCuentaCtrl',
        size: '700',
        resolve: {
          objeto: function() {
            return $scope.cuentas;
          }
        }
      });
      miModal.result.then(function(cuenta) {
        obj.cuenta = cuenta;
        $scope.objeto.cuenta = cuenta;
      }, function () {
        javascript:void(0);
      });
    }

    $scope.openMasivo = function(){
      var miModal = $uibModal.open({
        animation: $scope.animationsEnabled,
        templateUrl: 'views/forms/form-cuentas-masivo.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormCuentasMasivo',
        resolve: {
          objeto: function() {
            return $scope.objeto;
          },
          tipo: function() {
            return $scope.tipo;
          },
          centrosCostos: function() {
            return $scope.centrosCostos;
          },
          cuentas: function() {
            return $scope.cuentas;
          }
        }
      });
      miModal.result.then(function(obj) {
        Notification.success({message: obj.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos(obj.sid);
      }, function () {
        javascript:void(0);
      });
    }

    function cargarDatos(sid){
      $rootScope.cargando = true;
      $scope.cargado = false;
      if($scope.tipo==='aporte'){
        var datos = aporte.centroCosto().get({ sid: sid });
      }else if($scope.tipo==='haber'){
        if(objeto.isHoraExtra){
          var datos = tipoHoraExtra.centroCosto().get({ sid: sid });
        }else{
          var datos = tipoHaber.centroCosto().get({ sid: sid });
        }
      }else if($scope.tipo==='descuento'){
        var datos = tipoDescuento.centroCosto().get({ sid: sid });
      }      
      datos.$promise.then(function(response){
        $scope.centrosCostos = response.centrosCostos;
        $scope.cuentas = response.cuentas;
        $scope.objeto = response.datos;
        $scope.encabezado = $scope.objeto.nombre;
        $scope.isSelect = false;
        $scope.cargado = true;
        $rootScope.cargando = false;      
      }); 
    }

  })
  .controller('FormCuentasMasivo', function ($scope, tipo, centrosCostos, tipoHoraExtra, tipoHaber, tipoDescuento, $uibModalInstance, $filter, $uibModal, mesDeTrabajo, cuentas, objeto, Notification, $rootScope, aporte) {

    $scope.centrosCostos = angular.copy(centrosCostos);
    $scope.cuentas = angular.copy(cuentas);
    $scope.objeto = angular.copy(objeto);
    $scope.tipo = angular.copy(tipo);
    $scope.isSelect = false;
    $scope.todos = { cuenta : null };    

    $scope.guardar = function () {
      $rootScope.cargando=true;

      for(var i=0,len=$scope.centrosCostos.length; i<len; i++){
        $scope.centrosCostos[i].cuenta = $scope.todos.cuenta;
      }

      var cc = { 'centrosCosto' : $scope.centrosCostos, 'concepto' : $scope.tipo, 'idConcepto' : $scope.objeto.id }

      if($scope.tipo==='aporte'){
        var response = aporte.updateCuentaCentrosCosto().post({}, cc);
      }else if($scope.tipo==='haber'){
        if(objeto.isHoraExtra){
          var response = tipoHoraExtra.updateCuentaCentrosCosto().post({}, cc);
        }else{
          var response = tipoHaber.updateCuentaCentrosCosto().post({}, cc);          
        }
      }else if($scope.tipo==='descuento'){
        var response = tipoDescuento.updateCuentaCentrosCosto().post({}, cc);
      }

      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje , sid : $scope.objeto.sid });
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    };

    $scope.seleccionar = function(select){
      $scope.isSelect = select;
    }

    $scope.openCuentas = function(obj){
      var miModal = $uibModal.open({
        animation: $scope.animationsEnabled,
        templateUrl: 'views/forms/form-buscar-cuenta.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormBuscarCuentaCtrl',
        size: '700',
        resolve: {
          objeto: function() {
            return $scope.cuentas;
          }
        }
      });
      miModal.result.then(function(cuenta) {
        $scope.todos.cuenta = cuenta;
      }, function () {
        javascript:void(0);
      });
    }


  })
  .controller('FormAsignacionMasiva', function ($scope, aporte, tipoDescuento, tipoHoraExtra, tipoHaber, $uibModalInstance, $filter, $uibModal, mesDeTrabajo, cuentas, objeto, Notification, $rootScope) {

    $scope.cuentas = angular.copy(cuentas);
    $scope.objeto = angular.copy(objeto);
    $scope.isSelect = false;
    $scope.todos = { cuenta : null };  
    
    $scope.guardar = function () {
      console.log($scope.objeto)
      console.log($scope.cuentas)
      $rootScope.cargando=true;
      var sid = [];
      for(var i=0,len=$scope.objeto.datos.length; i<len; i++){
        sid.push($scope.objeto.datos[i].sid);
      }
      var obj = { sid : sid, idCuenta : $scope.objeto.cuenta.id };

      console.log(obj);

      if($scope.objeto.tipo==='aporte'){
        var response = aporte.updateCuentaMasivo().post({}, obj);
      }else if($scope.objeto.tipo==='haber'){
        if(objeto.isHoraExtra){
          var response = tipoHoraExtra.updateCuentaMasivo().post({}, obj);
        }else{          
          var response = tipoHaber.updateCuentaMasivo().post({}, obj);
        }
      }else if($scope.objeto.tipo==='descuento'){
        var response = tipoDescuento.updateCuentaMasivo().post({}, obj);
      }

      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje , tab : $scope.objeto.tab });
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    };

    $scope.seleccionar = function(select){
      $scope.isSelect = select;
    }

    $scope.openCuentas = function(obj){
      var miModal = $uibModal.open({
        animation: $scope.animationsEnabled,
        templateUrl: 'views/forms/form-buscar-cuenta.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormBuscarCuentaCtrl',
        size: '700',
        resolve: {
          objeto: function() {
            return $scope.cuentas;
          }
        }
      });
      miModal.result.then(function(cuenta) {
        $scope.todos.cuenta = cuenta;
      }, function () {
        javascript:void(0);
      });
    }

  })
  .controller('FormCuentaCtrl', function ($scope, tipo, tipoHaber, tipoHoraExtra, tab, tipoDescuento, $uibModalInstance, $filter, $uibModal, mesDeTrabajo, cuentas, objeto, Notification, $rootScope, aporte) {

    $scope.empresa = $rootScope.globals.currentUser.empresa;
    $scope.cuentas = angular.copy(cuentas);
    $scope.objeto = angular.copy(objeto);
    $scope.tipo = angular.copy(tipo);
    $scope.titulo = 'Modificación de Cuentas de Aportes';
    $scope.encabezado = $scope.objeto.nombre;

    $scope.guardar = function () {
      $rootScope.cargando=true;
      if($scope.tipo==='aporte'){
        var response = aporte.updateCuenta().post({}, $scope.objeto);
      }else if($scope.tipo==='haber'){
        if(objeto.isHoraExtra){
          var response = tipoHoraExtra.updateCuenta().post({}, $scope.objeto);
        }else{
          var response = tipoHaber.updateCuenta().post({}, $scope.objeto);          
        }
      }else if($scope.tipo==='descuento'){
        var response = tipoDescuento.updateCuenta().post({}, $scope.objeto);
      }

      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, tab : tab});
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    };

    $scope.openCuentas = function(obj){
      var miModal = $uibModal.open({
        animation: $scope.animationsEnabled,
        templateUrl: 'views/forms/form-buscar-cuenta.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormBuscarCuentaCtrl',
        size: '700',
        resolve: {
          objeto: function() {
            return $scope.cuentas;
          }
        }
      });
      miModal.result.then(function(cuenta) {
        $scope.objeto.cuenta = cuenta;
      }, function () {
        javascript:void(0);
      });
    }
    
});

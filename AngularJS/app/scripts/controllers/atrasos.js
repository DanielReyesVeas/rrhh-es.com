'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:AtrasosCtrl
 * @description
 * # AtrasosCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('AtrasosCtrl', function ($scope, $uibModal, $filter, $anchorScroll, atraso, trabajador, constantes, $rootScope, Notification) {
    $anchorScroll();
    
    $scope.datos = [];
    $scope.constantes = constantes;
    $scope.cargado = false;

    function cargarDatos(){
      $rootScope.cargando=true;
      var datos = trabajador.totalAtrasos().get();
      datos.$promise.then(function(response){
        $scope.datos = response.datos;
        $scope.accesos = response.accesos;
        $rootScope.cargando=false;
        $scope.cargado = true;
      });
    };

    cargarDatos();

    $scope.open = function(){
      $rootScope.cargando=true;
      var datos = atraso.datos().get({sid: 0});
      datos.$promise.then(function(response){        
        $rootScope.cargando=false;
        $scope.openAtraso(response);
      })
    }

    $scope.openAtraso = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-nuevo-atraso.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormAtrasoCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          }
        }
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos();         
      }, function () {
        javascript:void(0)
      });
    };

    $scope.openDetalleAtrasos = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-detalle-atrasos.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormDetalleAtrasosCtrl',
        size: 'lg',
        resolve: {
          objeto: function () {
            return obj;          
          }
        }
      });
     miModal.result.then(function (mensaje) {
        Notification.success({message: mensaje, title: 'Mensaje del Sistema'});
        cargarDatos();         
      }, function () {
        cargarDatos();      
      });
    };    

    $scope.detalle = function(sid){
      $rootScope.cargando=true;
      var datos = trabajador.atrasos().get({sid: sid});
      datos.$promise.then(function(response){
        $scope.openDetalleAtrasos( response );
        $rootScope.cargando=false;
      });
    };

    $scope.toolTipDetalle = function( nombre ){
      return 'Gestionar atrasos del trabajador <b>' + nombre + '</b>';
    };

  })
  .controller('FormDetalleAtrasosCtrl', function ($rootScope, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, atraso, trabajador) { 
    
    $scope.trabajador = angular.copy(objeto.datos);
    $scope.accesos = angular.copy(objeto.accesos);

    function cargarDatos(tra){
      $rootScope.cargando=true;
      var datos = trabajador.atrasos().get({sid: tra});
      datos.$promise.then(function(response){
        $scope.trabajador = response.datos;
        $scope.accesos = response.accesos;
        $rootScope.cargando=false;
      });
    };

    $scope.editar = function(ina, tra){
      $rootScope.cargando=true;
      var datos = atraso.datos().get({sid: ina.sid});
      datos.$promise.then(function(response){
        $scope.openAtraso( response );
        $rootScope.cargando=false;
      });
    };

    $scope.eliminar = function(ina, tra){
      $rootScope.cargando=true;
      $scope.result = atraso.datos().delete({ sid: ina.sid });
      $scope.result.$promise.then( function(response){
        if(response.success){
          Notification.success({message: response.mensaje, title:'Notificación del Sistema'});
          cargarDatos(tra);
        }
      });
    }

    $scope.openAtraso = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-nuevo-atraso.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormAtrasoCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          }
        }
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos(object.sidTrabajador);         
      }, function () {
        javascript:void(0)
      });
    };

  })
  .controller('FormAtrasoCtrl', function ($rootScope, Notification, trabajador, $scope, $uibModalInstance, objeto, atraso, fecha) {

    $scope.isTrabajador = false;

    if(objeto.datos){
      $scope.trabajador = angular.copy(objeto.datos.trabajador);
      var hora = new Date(objeto.datos.fecha);
      hora.setHours(objeto.datos.horas, objeto.datos.minutos)
      $scope.atraso = { id : objeto.datos.id, sid : objeto.datos.sid, fecha : fecha.convertirFecha(objeto.datos.fecha), hora : hora, observacion : objeto.datos.observacion };
      $scope.isEdit = true;
      $scope.titulo = 'Atrasos';
      $scope.encabezado = 'Modificación Atraso';
    }else{
      $scope.trabajador = angular.copy(objeto);
      $scope.trabajadores = angular.copy(objeto.trabajadores);  
      $scope.isEdit = false;
      $scope.titulo = 'Atrasos';
      $scope.encabezado = 'Ingreso Atraso';
      $scope.atraso = { fecha : fecha.fechaActiva(), hora : fecha.fechaActiva(), observacion : null };
    }

    var mesActual = objeto.mesActual;

    $scope.selectTrabajador = function(){
      $rootScope.cargando=true;
      $scope.isTrabajador = false;
      var datos = trabajador.atrasos().get({sid: $scope.atraso.trabajador.sid});
      datos.$promise.then(function(response){
        $scope.isTrabajador = true;
        $scope.trabajador = response.datos;
        $rootScope.cargando=false;
      });
    }

    $scope.guardar = function(atr, trabajador){
      $rootScope.cargando=true;
      var response;
      var Atraso = { idTrabajador : trabajador.id, fecha : fecha.convertirFechaFormato(atr.fecha), horas : atr.hora.getHours(), minutos : atr.hora.getMinutes(), observacion : atr.observacion };
      console.log(Atraso)
      if( $scope.atraso.sid ){
        response = atraso.datos().update({sid:$scope.atraso.sid}, Atraso);
      }else{
        response = atraso.datos().create({}, Atraso);
      }
      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, sidTrabajador : trabajador.sid });
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    }
    
    // Fecha

    $scope.dateOptions = {
      formatYear: 'yy',
      maxDate: fecha.convertirFecha(mesActual.fechaRemuneracion),
      minDate: fecha.convertirFecha(mesActual.mes),
      startingDay: 1
    };  

    $scope.openFecha = function() {
      $scope.popupFecha.opened = true;
    };

    $scope.format = ['dd-MMMM-yyyy'];

    $scope.popupFecha = {
      opened: false
    };

  });

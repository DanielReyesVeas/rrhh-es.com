'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:IngresoHorasExtraCtrl
 * @description
 * # IngresoHorasExtraCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('IngresoHorasExtraCtrl', function ($scope, horaExtra, $uibModal, $filter, $anchorScroll, trabajador, constantes, $rootScope, Notification) {
    $anchorScroll();

    $scope.datos = [];
    $scope.constantes = constantes;
    $scope.cargado = false;

    function cargarDatos(){
      $rootScope.cargando=true;
      var datos = trabajador.totalHorasExtra().get();
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
      var datos = horaExtra.datos().get({sid: 0});
      datos.$promise.then(function(response){        
        $rootScope.cargando=false;
        $scope.openHoraExtra(response);
      })
    }

    $scope.openHoraExtra = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-nueva-hora-extra.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormHorasExtraCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          },
          tramos: function () {
            return obj.tramos;          
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

    $scope.openDetalleHorasExtra = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-detalle-horas-extra.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormDetalleHorasExtraCtrl',
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
      var datos = trabajador.horasExtra().get({sid: sid});
      datos.$promise.then(function(response){
        $scope.openDetalleHorasExtra( response );
        $rootScope.cargando=false;
      });
    };

    $scope.toolTipDetalle = function( nombre ){
      return 'Gestionar horas extra del trabajador <b>' + nombre + '</b>';
    };

  })
  .controller('FormDetalleHorasExtraCtrl', function ($rootScope, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, horaExtra, trabajador) { 
    
    $scope.trabajador = angular.copy(objeto.datos);
    $scope.accesos = angular.copy(objeto.accesos);

    function cargarDatos(tra){
      $rootScope.cargando=true;
      var datos = trabajador.horasExtra().get({sid: tra});
      datos.$promise.then(function(response){
        $scope.trabajador = response.datos;
        $scope.accesos = response.accesos;
        $rootScope.cargando=false;
      });
    };

    $scope.editar = function(hora, tra){
      $rootScope.cargando=true;
      var datos = horaExtra.datos().get({sid: hora.sid});
      datos.$promise.then(function(response){
        $scope.openHoraExtra( response );
        $rootScope.cargando=false;
      });
    };

    $scope.eliminar = function(hora, tra){
      $rootScope.cargando=true;
      $scope.result = horaExtra.datos().delete({ sid: hora.sid });
      $scope.result.$promise.then( function(response){
        if(response.success){
          Notification.success({message: response.mensaje, title:'Notificación del Sistema'});
          cargarDatos(tra);
        }
      });
    }

    $scope.openHoraExtra = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-nueva-hora-extra.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormHorasExtraCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          },
          tramos: function () {
            return $scope.trabajador.tramos;          
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
  .controller('FormHorasExtraCtrl', function ($rootScope, $filter, trabajador, Notification, tramos, $scope, $uibModalInstance, objeto, horaExtra, fecha) {

    var mesActual = $rootScope.globals.currentUser.empresa.mesDeTrabajo;
    $scope.isTrabajador = false;

    if(objeto.datos){
      $scope.trabajador = angular.copy(objeto.datos.trabajador);
      $scope.horaExtra = angular.copy(objeto.datos);
      $scope.horaExtra.fecha = fecha.convertirFecha($scope.horaExtra.fecha);
      $scope.isEdit = true;
      $scope.tramos = angular.copy(objeto.datos.trabajador.tramos.tramos);
      $scope.titulo = 'Horas Extra';
      $scope.encabezado = 'Modificación Hora Extra';
      $scope.horaExtra.tramo = $filter('filter')( $scope.tramos, { tramo : $scope.horaExtra.tramo }, true )[0];
    }else{
      $scope.trabajador = angular.copy(objeto);    
      $scope.trabajadores = angular.copy(objeto.trabajadores);  
      $scope.isEdit = false;
      $scope.titulo = 'Horas Extra';
      $scope.encabezado = 'Nueva Hora Extra';
      $scope.horaExtra = { fecha : fecha.fechaActiva() };
    }    

    $scope.jornadas = [
                      { id : 1, nombre : '4 x 3' },
                      { id : 2, nombre : '4 x 4' },
                      { id : 3, nombre : '7 x 7' },
                      { id : 4, nombre : '8 x 6' },
                      { id : 5, nombre : '10 x 10' },
                      { id : 6, nombre : '14 x 7' },
                      { id : 7, nombre : '20 x 10' },
                      { id : 8, nombre : '50%' },
                      { id : 9, nombre : '100%' }
    ];

    $scope.selectTrabajador = function(){
      $rootScope.cargando=true;
      $scope.isTrabajador = false;
      var datos = trabajador.horasExtra().get({sid: $scope.horaExtra.trabajador.sid});
      datos.$promise.then(function(response){
        $scope.isTrabajador = true;
        $scope.trabajador = response.datos;
        $scope.tramos = angular.copy(response.datos.tramos.tramos);
        $rootScope.cargando=false;
      });
    }

    $scope.guardar = function(horas){
      $rootScope.cargando=true;
      var mes = $rootScope.globals.currentUser.empresa.mesDeTrabajo;
      var response;
      if(horas.fecha==fecha.fechaActiva()){
        horas.fecha = fecha.convertirFecha(fecha.convertirFechaFormato(horas.fecha));
      }

      var HorasExtra = { idTrabajador : $scope.trabajador.id, idMes : mes.id, factor : horas.tramo.factor, cantidad : horas.cantidad, jornada : horas.jornada, fecha : horas.fecha, observacion : horas.observacion };

      if( $scope.horaExtra.sid ){
        response = horaExtra.datos().update({sid:$scope.horaExtra.sid}, HorasExtra);
      }else{
        response = horaExtra.datos().create({}, HorasExtra);
      }
      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, sidTrabajador : $scope.trabajador.sid });
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
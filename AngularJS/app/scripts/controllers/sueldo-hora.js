'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:SueldoHoraCtrl
 * @description
 * # SueldoHoraCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('SueldoHoraCtrl', function ($scope, $uibModal, $filter, fecha, $anchorScroll, trabajador, constantes, $rootScope, Notification) {
    
    $anchorScroll();
    $scope.isSelect = false;
    $scope.cargado = false;

    function cargarDatos(){
      $rootScope.cargando = true;
      $scope.cargado = false;
      var datos = trabajador.trabajadoresSueldoHora().get();
      datos.$promise.then(function(response){
        $scope.datos = response.datos;
        $scope.accesos = response.accesos;        
        $rootScope.cargando = false;
        $scope.cargado = true;
      });
    }

    $scope.detalle = function(obj){
      $rootScope.cargando=true;
      var datos = trabajador.sueldoHora().get({sid: obj.sid});
      datos.$promise.then(function(response){
        openDetalleVacaciones( response );
        $rootScope.cargando=false;
      });
    }


    function openDetalleVacaciones(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-detalle-sueldo-hora.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormDetalleSueldoHoraCtrl',
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

    cargarDatos();

    $scope.toolTipDetalle = function( nombre ){
      return 'Gestionar horas semanales del trabajador <b>' + nombre + '</b>';
    };

  })
  .controller('FormDetalleSueldoHoraCtrl', function ($rootScope, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, trabajador, descuentoHora) { 

    $scope.accesos = angular.copy(objeto.accesos)
    $scope.trabajador = angular.copy(objeto.datos);
    $scope.detalle = angular.copy(objeto.datos.detalle);
    var mes = angular.copy(objeto.mes);

    function cargarDatos(sid){
      $rootScope.cargando=true;
      var datos = trabajador.sueldoHora().get({sid: sid});
      datos.$promise.then(function(response){
        $scope.accesos = angular.copy(response.accesos)
        $scope.trabajador = angular.copy(response.datos);
        $scope.detalle = angular.copy(response.datos.detalle);
        mes = angular.copy(response.mes);
        $rootScope.cargando=false;
      });
    }

    $scope.nuevoDescuento = function(){
      openDescuento(null);
    }

    function openDescuento(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-nuevo-descuento-hora.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormNuevoDescuentoHoraCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          },
          mes: function () {
            return mes;          
          },
          trab: function () {
            return $scope.trabajador;          
          }
        }
      });
     miModal.result.then(function (obj) {
        Notification.success({message: obj.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos(obj.sidTrabajador);         
      }, function () {
        cargarDatos($scope.trabajador.sid); 
      });
    };   

    $scope.editar = function(des){
      $rootScope.cargando=true;
      var datos = descuentoHora.datos().get({sid: des.sid});
      datos.$promise.then(function(response){
        openDescuento( response.datos );
        $rootScope.cargando=false;
      });
    };

    $scope.eliminar = function(des){
      $rootScope.cargando=true;
      $scope.result = descuentoHora.datos().delete({ sid: des.sid });
      $scope.result.$promise.then( function(response){
        if(response.success){
          Notification.success({message: response.mensaje, title:'Notificación del Sistema'});
          cargarDatos($scope.trabajador.sid);
        }
      });
    }
     
  })
  .controller('FormNuevoDescuentoHoraCtrl', function ($rootScope, descuentoHora, fecha, trab, $uibModal, mes, $filter, Notification, $scope, $uibModalInstance, objeto, trabajador) { 

    var mes = angular.copy(mes);    

    if(objeto){
      $scope.trabajador = angular.copy(objeto.trabajador);
      var hora = new Date(objeto.fecha);
      hora.setHours(objeto.horas, objeto.minutos)
      $scope.descuentoHora = { id : objeto.id, sid : objeto.sid, fecha : fecha.convertirFecha(objeto.fecha), hora : hora, observacion : objeto.observacion };
      $scope.isEdit = true;
      $scope.titulo = 'Descuentos Hora';
      $scope.encabezado = 'Modificación Descuento';
    }else{
      $scope.trabajador = angular.copy(trab);
      $scope.isEdit = false;
      $scope.titulo = 'Descuentos Hora';
      $scope.encabezado = 'Ingreso Descuento';
      $scope.descuentoHora = { fecha : fecha.fechaActiva(), hora : fecha.fechaActiva(), observacion : null };
    }

    $scope.guardar = function(){
      $rootScope.cargando=true;
      var response;
      var Des = { idTrabajador : $scope.trabajador.id, fecha : fecha.convertirFechaFormato($scope.descuentoHora.fecha), horas : $scope.descuentoHora.hora.getHours(), minutos : $scope.descuentoHora.hora.getMinutes(), observacion : $scope.descuentoHora.observacion };
      if( $scope.descuentoHora.sid ){
        response = descuentoHora.datos().update({sid:$scope.descuentoHora.sid}, Des);
      }else{
        response = descuentoHora.datos().create({}, Des);
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
      maxDate: fecha.convertirFecha(mes.fechaRemuneracion),
      minDate: fecha.convertirFecha(mes.mes),
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

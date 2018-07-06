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
        openDetalleVacaciones( response.datos );
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
  .controller('FormDetalleSueldoHoraCtrl', function ($rootScope, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, trabajador) { 

    console.log(objeto)

    if(objeto){
      $scope.atraso = angular.copy(objeto);
      $scope.titulo = 'Modificación Atraso';
      $scope.encabezado = $scope.atraso;
      $scope.isEdit = true;
    }else{
      $scope.atraso = {};
      $scope.titulo = 'Atrasos';
      $scope.encabezado = 'Nuevo Atraso';
      $scope.isEdit = false;
    }    

  });

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
      var datos = trabajador.vacaciones().get({sid: obj.sid});
      datos.$promise.then(function(response){
        var fechas = crearModels(response.datos);        
        $scope.fechas = { primerMes : response.primerMes, ultimoMes : response.ultimoMes };
        openDetalleVacaciones( response, fechas );
        $rootScope.cargando=false;
      });
    }


    function openDetalleVacaciones(obj, fechas){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-detalle-vacaciones.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormDetalleSueldoHoraCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          },
          tomadas: function () {
            return fechas.tomadas;          
          },
          feriados: function () {
            return fechas.feriados;          
          },
          fechas: function () {
            return $scope.fechas;          
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


  });

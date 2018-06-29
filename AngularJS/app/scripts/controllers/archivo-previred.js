'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:ArchivoPreviredCtrl
 * @description
 * # ArchivoPreviredCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('ArchivoPreviredCtrl', function ($scope, $uibModal, $filter, $anchorScroll, trabajador, constantes, $rootScope, Notification) {
    $anchorScroll();
    $scope.datos = [];
    $scope.constantes = constantes;
    $scope.cargado = false;

    function cargarDatos(){
      $rootScope.cargando = true;
      var datos = trabajador.previred().get();
      datos.$promise.then(function(response){
        $scope.datos = response.datos;
        $scope.accesos = response.accesos;
        $scope.isGenerar = response.isLiquidaciones;
        $scope.isIndicadores = response.isIndicadores;
        $rootScope.cargando = false;      
        $scope.cargado = true;  
      });
    };

    cargarDatos();

    $scope.generarArchivo = function(){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-generar-previred.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormPreviredCtrl',
        resolve: {
          objeto: function () {
            return $scope.datos;          
          }
        }
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
      }, function () {
        javascript:void(0)
      });
    }

    /*$scope.generarArchivo = function(){
      $rootScope.cargando=true;
      var url = $scope.constantes.URL + 'trabajadores/archivo-previred/descargar-excel';
      window.open(url, "_self");
      $rootScope.cargando=false;
    }*/

    $scope.toolTipInfo = function( nombre, liquidacion ){
      if(liquidacion){
        return 'Liquidación <b>generada</b> del trabajador <b>' + nombre + '</b>';
      }else{
        return 'Liquidación <b>pendiente</b> del trabajador <b>' + nombre + '</b>';        
      }
    };

  })
  .controller('FormPreviredCtrl', function ($rootScope, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, trabajador) {

    $scope.datos = angular.copy(objeto);
    $scope.objeto = { todos : true };

    function crearModels(){
      for(var i=0, len=$scope.datos.length; i<len; i++){
        $scope.datos[i].check = true;
      }                
    }    

    $scope.isSelected = function(){
      var bool = false;
      for(var i=0,len=$scope.datos.length; i<len; i++){
        if($scope.datos[i].check){
          bool = true;
          break;
        }
      }

      return bool;
    }

    crearModels();

    $scope.selectTrabajadores = function(check){     
      if(!check){
        if($scope.objeto.todos){
          $scope.objeto.todos = false; 
        }
      }else{
        if(isSelected($scope.datos)){
          $scope.objeto.todos = true;
        }
      }
    }

    function isSelected(datos){
      var bool = true;
      for(var i=0, len=datos.length; i<len; i++){
        if(!datos[i].check){
          bool = false;
          return bool;
        }
      }
      return bool;
    }

    $scope.selectAll = function(datos, all){      
      for(var i=0, len=datos.length; i<len; i++){
        datos[i].check = all;
      }
    }

    function crearObjeto(){
      var trabajadores = [];
      for(var i=0,len=$scope.datos.length; i<len; i++){
        if($scope.datos[i].check){
          trabajadores.push($scope.datos[i].sid);
        }
      }

      return trabajadores;
    }

    $scope.generarArchivo = function(){
      $rootScope.cargando=true;
      var trabajadores = crearObjeto();
      var datos = trabajador.generarPrevired().post({trabajadores : trabajadores});
      datos.$promise.then(function(response){
        open(response);
      });
    }

    function open(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-previred.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormArchivoPreviredCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          }
        }
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
      }, function () {
        javascript:void(0)
      });
    }

  })
  .controller('FormArchivoPreviredCtrl', function ($rootScope, $uibModal, $filter, constantes, Notification, $scope, $uibModalInstance, objeto) {

    $rootScope.cargando = false;
    $scope.constantes = constantes;
    $scope.datos = angular.copy(objeto.datos);
    $scope.afps = angular.copy(objeto.detalles.afps);
    $scope.caja = angular.copy(objeto.detalles.caja);
    $scope.mutual = angular.copy(objeto.detalles.mutual);
    $scope.isapres = angular.copy(objeto.detalles.isapres);
    $scope.ipsFonasa = angular.copy(objeto.detalles.ipsFonasa);
    $scope.detalles = angular.copy(objeto.detalles);
    $scope.empresa = $rootScope.globals.currentUser.empresa;

    $scope.detalleLarge = function(datos, detalle){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-detalle-large-previred.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormDetallePreviredCtrl',
        resolve: {
          objeto: function () {
            return datos;          
          },
          detalle: function () {
            return detalle;          
          }
        },
        size: 'lg'
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
      }, function () {
        javascript:void(0)
      });
    }

    $scope.detalle = function(datos){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-detalle-previred.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormDetallePreviredCtrl',
        resolve: {
          objeto: function () {
            return datos;          
          },
          detalle: function () {
            return null;          
          }
        }
      });
      miModal.result.then(function (object) {
        Notification.success({message: object.mensaje, title: 'Mensaje del Sistema'});
      }, function () {
        javascript:void(0)
      });
    }
    
    $scope.descargar = function(){
      var url = $scope.constantes.URL + 'trabajadores/archivo-previred/descargar';
      window.open(url, "_self");
    }

  })
  .controller('FormDetallePreviredCtrl', function ($rootScope, detalle, constantes, Notification, $scope, $uibModalInstance, objeto) {

    $scope.datos = angular.copy(objeto);
    $scope.detalle = angular.copy(detalle);
    console.log($scope.datos)

  });
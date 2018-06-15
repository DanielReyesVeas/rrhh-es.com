'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:ConfiguracionCtrl
 * @description
 * # ConfiguracionCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('ConfiguracionCtrl', function ($scope, $uibModal, $filter, $anchorScroll, constantes, $rootScope, Notification, empresa) {
    $anchorScroll();

    $scope.datos = [];
    $scope.cargado = false;

    function cargarDatos(){
      $scope.cargado = false;
      $rootScope.cargando = true;
      var datos = empresa.configuracion().get();
      datos.$promise.then(function(response){
        $scope.accesos = response.accesos;
        $scope.datos = response.datos;
        $scope.configuraciones = response.configuraciones;
        actualizarOptions();
        $rootScope.cargando = false;
        $scope.cargado = true;
      });
    };

    $scope.selectConfiguracion = function(){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-confirmacion.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormCambiarConfiguracionCtrl',
        resolve: {
          objeto: function () {
            return $scope.datos;          
          }
        }
      });
     miModal.result.then(function (object) {
        $rootScope.cargando=true;
        var datos = empresa.cambiarConfiguracion().post({}, { valor : object.configuracion.valor });
        datos.$promise.then(function(response){     
          Notification.success({message: response.mensaje, title: 'Mensaje del Sistema'});
          cargarDatos();
        });
      }, function (obj) {
        if(obj.configuracion.valor=='g'){
          $scope.datos.configuracion = $filter('filter')($scope.configuraciones, { valor: 'e'}, true )[0];
        }else{
          $scope.datos.configuracion = $filter('filter')($scope.configuraciones, { valor: 'g'}, true )[0];
        }
      }); 
    }

    $scope.cambiarValor = function(variable){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-confirmacion.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormCambiarVariableCtrl',
        resolve: {
          objeto: function () {
            return variable;          
          }
        },
        size: 'sm',
      });
     miModal.result.then(function (variable) {
        $rootScope.cargando=true;
        var datos = empresa.cambiarValor().post({}, { variable : variable, valor : $scope.datos[variable] });
        datos.$promise.then(function(response){     
          Notification.success({message: response.mensaje, title: 'Mensaje del Sistema'});
          cargarDatos();
        });
        console.log(variable)
      }, function (obj) {
        $scope.datos[obj] = !$scope.datos[obj];
      }); 
    }

    function actualizarOptions(){
      $scope.datos.configuracion = $filter('filter')($scope.configuraciones, { valor: $scope.datos.configuracion}, true )[0];
    }

    cargarDatos();

  })
  .controller('FormCambiarVariableCtrl', function ($scope, $rootScope, $uibModalInstance, objeto, empresa, constantes) {

    $scope.configuracion = angular.copy(objeto);

    $scope.mensaje = 'La Configuración del sistema cambiará.';
    $scope.mensaje2 = '¿Desea continuar?';      
    $scope.ok = 'Cambiar';
    $scope.titulo = 'Configuración del Sistema';
    $scope.cancel = 'Cancelar';
    $scope.isOK = true;
    $scope.isCerrar = true;
    $scope.isExclamation = true;

    $scope.aceptar = function(){
      $uibModalInstance.close($scope.configuracion);
    }

    $scope.cerrar = function(){
      $uibModalInstance.dismiss($scope.configuracion);
    }  
  
  })
  .controller('FormCambiarConfiguracionCtrl', function ($scope, $rootScope, $uibModalInstance, objeto, empresa, constantes) {

    $scope.configuracion = angular.copy(objeto);

    if($scope.configuracion.configuracion.valor=='g'){
      $scope.mensaje = 'La Configuración del sistema será establecida como <b>Global</b>.';
      $scope.mensaje2 = 'Todas las empresas tendrán la misma configuración que sea definida en esta sección. ¿Desea continuar?';      
    }else{
      $scope.mensaje = 'La Configuración del sistema será establecida <b>Por Empresa</b>.';
      $scope.mensaje2 = 'Cada empresa tendrá su propia configuración, la cual será definida en esta sección. ¿Desea continuar?';          
    }

    $scope.ok = 'Cambiar Configuración';
    $scope.titulo = 'Configuración del Sistema';
    $scope.cancel = 'Cancelar';
    $scope.isOK = true;
    $scope.isCerrar = true;
    $scope.isExclamation = true;

    $scope.aceptar = function(){
      $uibModalInstance.close($scope.configuracion);
    }

    $scope.cerrar = function(){
      $uibModalInstance.dismiss($scope.configuracion);
    }  

});

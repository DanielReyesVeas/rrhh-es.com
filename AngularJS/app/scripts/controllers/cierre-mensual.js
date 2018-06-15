'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:CierreMensualCtrl
 * @description
 * # CierreMensualCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('CierreMensualCtrl', function ($scope, $http, $uibModal, $filter, $anchorScroll, anio, constantes, $rootScope, Notification, mesDeTrabajo, $location) {
    
    $anchorScroll();
    $scope.isCME = $rootScope.globals.currentUser.empresa.isCME;
    $scope.cargado = false;

    if($rootScope.globals.currentUser.empresa){
      $scope.mesDeTrabajo = $rootScope.globals.currentUser.empresa.mesDeTrabajo;
      $scope.anio = $scope.mesDeTrabajo.anio;
    }
    console.log($scope.mesDeTrabajo)
    $scope.cierre = {};

    function actualizarOptions(){
        if( $scope.datos.nombre ){
            $scope.cierre.anio = $filter('filter')( $scope.anios, { nombre : $scope.datos.nombre } , true )[0];
        }else{
            $scope.cierre.anio = $filter('filter')( $scope.anios, $scope.cierre.anio, true )[0];
        }
    }

    $scope.get = function(mes){
      $rootScope.cargando = true;
      $http.get(constantes.URL + 'trabajadores/cuentas/obtener')
      .then(function(response){
        $rootScope.cargando = false;      
      });
    }        

    function cargarDatos(){
      $scope.cargado = false;
      $rootScope.cargando = true;
      var datos = anio.datosCierre().get();
      datos.$promise.then(function(response){
        $scope.anios = response.anios;
        $scope.isNuevoAnio = response.isNuevoAnio;
        $scope.datos = response.datos;
        $scope.accesos = response.accesos;
        $scope.isLiquidaciones = response.isLiquidaciones;
        $scope.isCuentas = response.isCuentas;
        $rootScope.cargando = false;      
        $scope.cargado = true;  
        actualizarOptions();
      });
    };

    $scope.isFirst = function(index){
      var bool = false;

      if(!$scope.datos.estadoMeses[index].iniciado){
        if($scope.datos.estadoMeses[index].disponible){        
          bool = true;

          for(var i=0, len=$scope.datos.estadoMeses.length; i<len; i++){
            if($scope.datos.estadoMeses[i].disponible && !$scope.datos.estadoMeses[i].iniciado){
              if(i===index){
                bool = true;
                break;
              }else{
                bool = false;
                break;
              }
            }
          }
        }
      }

      return bool;
    }

    $scope.isDisponibleSinIndicadores = function(index){
      var bool = false;

      if(!$scope.datos.estadoMeses[index].iniciado && $scope.datos.estadoMeses[index].disponibleSinIndicadores && !$scope.datos.estadoMeses[index].disponible && $scope.datos.estadoMeses[(index - 1)].indicadores){
        bool = true;
        console.log(index)
        for(var i=0, len=$scope.datos.estadoMeses.length; i<len; i++){
          if(!$scope.datos.estadoMeses[i].disponible && !$scope.datos.estadoMeses[i].iniciado){
            if(i===index){
              bool = true;
              break;
            }else{
              bool = false;
              break;
            }
          }
        }
      }

      return bool;
    }

    $scope.cargarIndicadores = function(mes){
      $rootScope.cargando = true;
      var response = mesDeTrabajo.cargarIndicadores().post({}, mes);
      response.$promise.then(
        function(response){
          $rootScope.cargando = false;    
          if(response.success){
            console.log(response)
            if($rootScope.globals.currentUser.empresa.mesDeTrabajo.mes==response.mes.mes){
              $rootScope.globals.currentUser.empresa.mesDeTrabajo = response.mes;
              $rootScope.globals.indicadores.uf = response.indicadores.uf;
              $rootScope.globals.indicadores.utm = response.indicadores.utm;
              $rootScope.globals.indicadores.uta = response.indicadores.uta;                        
              $rootScope.globals.isIndicadores = response.mes.indicadores;
              console.log($rootScope.globals)
            }
            Notification.success({message: response.mensaje, title: 'Mensaje del Sistema'});
            cargarDatos();  
          }else{
            $scope.erroresDatos = response.errores;
            Notification.error({message : response.mensaje, title : 'Mensaje del Sistema'});
          }
        }
      );  
    }

    cargarDatos();

    $scope.guardar = function(){
      $rootScope.cargando=true;
      var meses = $scope.datos.estadoMeses;
      var response;
      response = anio.cerrarMeses().post({}, meses);

      response.$promise.then(
        function(response){
          if(response.success){
            Notification.success({message: response.mensaje, title: 'Mensaje del Sistema'});
            cargarDatos()
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }          
        }
      );
    } 

    $scope.centralizar = function(mes){
      $rootScope.cargando = true;
      var datos = mesDeTrabajo.centralizar().post({}, mes);
      datos.$promise.then(function(response){
        $rootScope.cargando = false;      
      });
    }

    function openCentralizar(obj){
      var miModal = $uibModal.open({
        animation: true,
        backdrop: false,
        templateUrl: 'views/forms/form-centralizar.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormCentralizar',
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
        javascript:void(0)
      });
    }

    $scope.confirmar = function(obj, anio, isIndicadores){
      if(isIndicadores){
        openTerminos(obj);
      }else{
        openConfirmar(obj, anio);
      }
    }

    function openTerminos(obj){
      var miModal = $uibModal.open({
        animation: true,
        backdrop: false,
        templateUrl: 'views/forms/form-terminos.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormTerminos',
        resolve: {
          objeto: function () {
            return obj;
          }
        }
      });
      miModal.result.then(function (mes) {
        iniciar(mes, true);
      }, function () {
        javascript:void(0)
      });
    }

    function openConfirmar(obj, anio){
      var miModal = $uibModal.open({
        animation: true,
        backdrop: false,
        templateUrl: 'views/forms/form-confirmacion.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormConfirmarApertura',
        resolve: {
          objeto: function () {
            return obj;
          }, 
          anio: function () {
            return anio;
          }
        }
      });
      miModal.result.then(function (mes) {
        iniciar(mes, false);
      }, function () {
        javascript:void(0)
      });
    }

    function iniciar(mes, indicadores){
      $rootScope.cargando=true;
      if(mes.nombre==='NuevoAnio'){
        var mes = mes;
        var remuneracion = mes;
        var nuevoMes = { mes : mes, remuneracion : remuneracion };
        var datosMes = { mes : nuevoMes.mes, nombre : 'NuevoAnio', fechaRemuneracion : nuevoMes.remuneracion, idAnio : $scope.datos.id };
      }else{
        var datosMes = { mes : mes.mes, nombre : mes.nombre, fechaRemuneracion : mes.fechaRemuneracion, idAnio : $scope.datos.id };
      }
      datosMes.indicadores = indicadores;
      var response;
      response = mesDeTrabajo.datos().create({}, datosMes);

      response.$promise.then(
        function(response){
          if(response.success){
            $scope.cambiarMesDeTrabajo(response.mes);
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }          
        }
      );
    }


    $scope.selectAnio = function(){
        $scope.cargado = false;
        $rootScope.cargando = true;
        var datos = anio.datosCierre().get({anio:$scope.cierre.anio.nombre});
        datos.$promise.then(function(response){
          $scope.anios = response.anios;
          $scope.isNuevoAnio = response.isNuevoAnio;
          $scope.datos = response.datos;
          $scope.accesos = response.accesos;
          $scope.isLiquidaciones = response.isLiquidaciones;
          $scope.isCuentas = response.isCuentas;
          $rootScope.cargando = false;      
          $scope.cargado = true;  
          actualizarOptions();
        });
    };
  })

  .controller('FormConfirmarApertura', function ($scope, $rootScope, $uibModalInstance, objeto, anio) {

    $scope.mes = angular.copy(objeto);

    $scope.mensaje = '<b> El mes de ' + $scope.mes.nombre + ' ' + anio + ' será iniciado sin los Indicadores Previsionales</b>.<br /> La información generada por el sistema será meramente referencial, ya que se estarán utilizando los valores de los indicadores del mes anterior hasta que se carguen los indicadores correspondientes.';
    $scope.mensaje2 = '¿Desea continuar?';      
    $scope.ok = 'Iniciar';
    $scope.titulo = 'Apertura de Mes sin Indicadores';
    $scope.cancel = 'Cancelar';
    $scope.isOK = true;
    $scope.isCerrar = true;
    $scope.isExclamation = true;

    $scope.aceptar = function(){
      $uibModalInstance.close($scope.mes);
    }

    $scope.cerrar = function(){
      $uibModalInstance.dismiss();
    }  
  
  })  
  .controller('FormTerminos', function ($scope, $uibModalInstance, objeto, $rootScope) {

    $scope.aceptar = function(){
      $uibModalInstance.close(objeto);
    }

  });

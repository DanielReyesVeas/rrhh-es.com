'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:SemanaCorridaCtrl
 * @description
 * # SemanaCorridaCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('SemanaCorridaCtrl', function ($scope, anio, $uibModal, $filter, $anchorScroll, trabajador, constantes, $rootScope, Notification) {
    
    $anchorScroll();
    $scope.objeto = [];
    $scope.isSelect = false;
    $scope.cargado = false;
    $scope.empresa = $rootScope.globals.currentUser.empresa;

    $scope.tabSemanal = true;
    $scope.tabMensual = false;

    $scope.openTab = function(tab){
      switch (tab) {
        case 'semanal':
          $scope.tabSemanal = true;
          $scope.tabMensual = false;
          break;
        case 'mensual':
          $scope.tabSemanal = false;
          $scope.tabMensual = true;
          break;
      }
    }

    function cargarDatos(){
      $rootScope.cargando = true;
      $scope.cargado = false;
      var datos = trabajador.trabajadoresSemanaCorrida().get();
      datos.$promise.then(function(response){
        $scope.trabajadoresSemanal = response.trabajadoresSemanal;
        $scope.trabajadoresMensual = response.trabajadoresMensual;
        $scope.accesos = response.accesos;
        $scope.semanas = response.semanas;
        $rootScope.cargando = false;
        $scope.cargado = true;
      });
    }

    $scope.detalle = function(tra, tipo){
      if(tipo=='s'){
        openDetalleSemanal(tra);
      }else{
        openDetalleMensual(tra);        
      }
    }

    function openDetalleSemanal(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-semana-corrida-semanal.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormSemanaCorridaCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          },
          accesos: function () {
            return $scope.accesos;          
          },
          tipo: function(){
            return 'semanal';
          }
        },
        size: 'lg'
      });
      miModal.result.then(function (mensaje) {
        Notification.success({message: mensaje, title: 'Mensaje del Sistema'});
        cargarDatos();         
        }, function () {
      });
    };

    function openDetalleMensual(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-semana-corrida-mensual.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormSemanaCorridaCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          },
          accesos: function () {
            return $scope.accesos;          
          },
          tipo: function(){
            return 'mensual';
          }
        }
      });
      miModal.result.then(function (mensaje) {
        Notification.success({message: mensaje, title: 'Mensaje del Sistema'});
        cargarDatos();         
        }, function () {
      });
    };

    $scope.calendario = function(){
      $rootScope.cargando = true;
      var datos = anio.calendario().get();
      datos.$promise.then(function(response){
        $scope.anios = response.anios;
        $scope.accesos = response.accesos;
        $rootScope.cargando = false;
        open($scope.anios, $scope.accesos, response.festivos)
      });
    }

    function open(anios, accesos, festivos){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-calendario-semana-corrida.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormCalendarioSemanaCorridaCtrl',
        resolve: {
          accesos: function () {
            return accesos;          
          },
          anios: function () {
            return anios;          
          },
          festivos: function () {
            return festivos;          
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
      return 'Gestionar semana corrida del trabajador <b>' + nombre + '</b>';
    };

  })
  .controller('FormCalendarioSemanaCorridaCtrl', function ($rootScope, anio, fecha, anios, festivos, accesos, $uibModal, $filter, Notification, $scope, $uibModalInstance) { 
    
    $scope.semanaCorrida = true;
    var anioActual = $rootScope.globals.currentUser.empresa.mesDeTrabajo.idAnio;
    $scope.anios = angular.copy(anios);
    $scope.festivos = angular.copy(festivos);
    $scope.calendario = { anio : $filter('filter')( $scope.anios, {id : anioActual }, true )[0] };
    $scope.accesos = angular.copy(accesos);
    $scope.nombre = 'Semana Corrida';

    function cargarDatos(anioActual){
      $rootScope.cargando = true;
      var datos = anio.calendario().get();
      datos.$promise.then(function(response){
        $scope.anios = response.anios;
        $scope.festivos = response.festivos;
        $scope.accesos = response.accesos;
        $scope.calendario = { anio : $filter('filter')( $scope.anios, {id : anioActual.id }, true )[0] };
        anioActual = anioActual;
        $scope.selectAnio();
        $rootScope.cargando = false;
      });      
    }

    $scope.selectAnio = function(){
      $scope.meses = $scope.calendario.anio.meses;
    }

    $scope.openFestivos = function(){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-festivos.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormFestivosCtrl',
        resolve: {
          objeto: function () {
            return $scope.festivos;          
          },
          anioActual: function () {
            return $scope.calendario.anio;          
          }
        }
      });
      miModal.result.then(function (obj) {
        Notification.success({message: obj.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos(obj);
      }, function () {
      });
    }

    $scope.detalle = function(obj){
      if(obj.feriados.length>0){
        for(var i=0,len=obj.feriados.length; i<len; i++){
          obj.feriados[i] = fecha.convertirFecha(obj.feriados[i]).setHours(0, 0, 0, 0);         
        }
      }
      openMes(obj);
    }

    function openMes(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-mes-festivos.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormMesFestivosCtrl',
        resolve: {
          objeto: function () {
            return obj;          
          },
          anioActual: function () {
            return $scope.calendario.anio;          
          }
        }
      });
      miModal.result.then(function (obj) {
        Notification.success({message: obj.mensaje, title: 'Mensaje del Sistema'});
        cargarDatos(obj.anio);
      }, function () {
      });
    };

    $scope.selectAnio();
  
  })
  .controller('FormFestivosCtrl', function ($rootScope, fecha, $uibModal, anioActual, anio, $filter, Notification, $scope, $uibModalInstance, objeto) { 

    $scope.festivos = angular.copy(objeto);
    $scope.anio = angular.copy(anioActual);

    $scope.guardar = function(){
      $rootScope.cargando=true;
      var response;
      response = anio.modificarFestivos().post({}, $scope.festivos);
      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, anio : $scope.anio});
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;       
        }
      );
    }
  
  })
  .controller('FormMesFestivosCtrl', function ($rootScope, anio, anioActual, fecha, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, trabajador) { 

    $scope.titulo = "Semana Corrida";
    $scope.mes = angular.copy(objeto);
    $scope.anioActual = angular.copy(anioActual);
    $scope.activeDate = fecha.convertirFecha($scope.mes.mes).setHours(0, 0, 0, 0);
    $scope.selectedDates = crearModels();

    function crearModels(){
      var feriados = [];
      for(var i=0,len=$scope.mes.feriadosSemanaCorrida.length; i<len; i++){
        var feriado = fecha.convertirFecha($scope.mes.feriadosSemanaCorrida[i]);
        feriados.push(feriado.setHours(0, 0, 0, 0));
      }
      console.log(feriados)
      return feriados;
    }

    $scope.options = {
      startingDay:1,
      minDate: fecha.convertirFecha($scope.mes.mes),
      maxDate: fecha.convertirFecha($scope.mes.fechaRemuneracion),
      customClass: function(data) {
        if($scope.selectedDates.indexOf(data.date.setHours(0, 0, 0, 0)) > -1) {
          console.log(data)
          return 'selected';
        }
        return '';
      }
    }
        
    $scope.removeFromSelected = function(dt) {
      $scope.selectedDates.splice($scope.selectedDates.indexOf(dt), 1);
      $scope.activeDate = dt;
    }    

    $scope.guardar = function(){
      $rootScope.cargando=true;
      var response;
      var feriados = [];
      for(var i=0,len=$scope.selectedDates.length; i<len; i++){
        feriados.push(fecha.convertirFechaFormato($scope.selectedDates[i]));
      }
      var obj = { feriados : feriados, anio : $scope.anioActual, mes : $scope.mes };
      response = anio.feriados().post({}, obj);
      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, anio : response.anio});
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;       
        }
      );
    }

  })
  .controller('FormSemanaCorridaCtrl', function ($rootScope, tipo, accesos, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, licencia, trabajador) { 

    $scope.trabajador = angular.copy(objeto);
    $scope.accesos = angular.copy(accesos);
    $scope.edit = false;

    if(tipo=='semanal'){
      crearModels();
    }else{
      $scope.input = { comision : $scope.trabajador.semanaCorrida.comision };
    }


    function crearModels(){
      $scope.input = [];
      for(var i=0, len=$scope.trabajador.semanaCorrida.semanas.length; i<len; i++){
        $scope.input.push($scope.trabajador.semanaCorrida.semanas[i].comision);            
      }
    }

    $scope.editar = function(){
      if(tipo=='semanal'){
        for(var i=0, len=$scope.trabajador.semanaCorrida.semanas.length; i<len; i++){
          $scope.trabajador.semanaCorrida.semanas[i].comision = angular.copy($scope.input[i]);            
        }
      }else{
        console.log($scope.input.comision)
        $scope.trabajador.semanaCorrida.comision = $scope.input.comision; 
        console.log($scope.trabajador.semanaCorrida)
      }
      $scope.edit = !$scope.edit;
    }

    $scope.guardar = function(){
      $rootScope.cargando=true;
      var response;
      $scope.trabajador.semanaCorrida.tipo = tipo;

      response = trabajador.semanaCorrida().post({}, $scope.trabajador.semanaCorrida);

      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close(response.mensaje);
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    }

  });

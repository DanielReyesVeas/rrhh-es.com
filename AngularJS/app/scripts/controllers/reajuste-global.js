'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:ReajusteGlobalCtrl
 * @description
 * # ReajusteGlobalCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('ReajusteGlobalCtrl', function ($scope, $uibModal, filterFilter, $timeout, $filter, $anchorScroll, trabajador, constantes, $rootScope, Notification) {
    $anchorScroll();
    $scope.objeto = [];
    $scope.cargado = false;
    $scope.empresa = $rootScope.globals.currentUser.empresa;
    $scope.filtro = {};
    
    function cargarDatos(){
      $rootScope.cargando = true;
      var datos = trabajador.reajuste().get();
      datos.$promise.then(function(response){
        $scope.accesos = response.accesos;
        $scope.datos = response.datos;
        $scope.rmi = response.rmi;
        $rootScope.cargando = false;
        $scope.isSelect = false;
        crearModels();
        $scope.filtrar();                
        $timeout(function() {
          aumentarLimite();
        }, 250);
        $scope.cargado = true;
      });
    };

    cargarDatos();

    $scope.filtrar = function(){
      $scope.filtro.itemsFiltrados=[];
      var listaTemp = filterFilter($scope.datos, $scope.filtro.nombre);
      if(listaTemp.length){
        for(var ind in listaTemp){
          $scope.filtro.itemsFiltrados.push( listaTemp[ind] );
        }
      }
      countSelected();
    };

    $scope.clearText = function(){
      $scope.filtro.nombre = "";
      $scope.filtrar();
    }

    $scope.cargaElementos=0;

    function aumentarLimite(){
      if( $scope.limiteDinamico < $scope.datos.length ){
        $scope.cargaElementos = Math.round(($scope.limiteDinamico/$scope.datos.length) * 100);
        $scope.limiteDinamico+=5;
        $timeout( function(){
          aumentarLimite();
        }, 250);
      }else{
        $rootScope.cargando=false;
        $scope.cargaElementos=100;
      }
    };

    function crearModels(){
      for(var i=0, len=$scope.datos.length; i<len; i++){
        $scope.datos[i].check = false;
      }         
    }
    
    $scope.select = function(index){
      if(!$scope.filtro.itemsFiltrados[index].check){
        if($scope.objeto.todos){
          $scope.objeto.todos = false; 
        }
        countSelected();
        $scope.isSelect = isThereSelected(); 
      }else{
        $scope.isSelect = true;
        countSelected();
      }
    }

    function isThereSelected(){
      var bool = false;
      for(var i=0, len=$scope.filtro.itemsFiltrados.length; i<len; i++){
        if($scope.filtro.itemsFiltrados[i].check){
          bool = true;
          return bool;
        }
      }
      return bool;
    }

    function countSelected(){
      var count = 0;
      for(var i=0, len=$scope.filtro.itemsFiltrados.length; i<len; i++){
        if($scope.filtro.itemsFiltrados[i].check){
        console.log($scope.filtro.itemsFiltrados[i])
          count++;
          $scope.mensaje = 'Se reajustarán los Sueldos de los ' + count + ' trabajadores seleccionados a ';
        }
      }
      if(count===1){
        count = $scope.filtro.itemsFiltrados[0].nombreCompleto;
        $scope.mensaje = 'Se reajustará el Sueldo de ' + count + ' a ';
      }
      console.log(count)
      return count;
    }

    $scope.selectAll = function(){
      if($scope.objeto.todos){
        var total = 0;
        for(var i=0, len=$scope.filtro.itemsFiltrados.length; i<len; i++){
          $scope.filtro.itemsFiltrados[i].check = true
          $scope.isSelect = true;
          total++;  
        }
        countSelected();
      }else{
        for(var i=0, len=$scope.filtro.itemsFiltrados.length; i<len; i++){
          $scope.filtro.itemsFiltrados[i].check = false
          $scope.isSelect = false;
        }
      }
    }    

    $scope.reajustar = function(trab){
      $rootScope.cargando=true;
      if(!trab){
        var trabajadoresReajuste = { trabajadores : [] };
        console.log($scope.filtro.itemsFiltrados);
        for(var i=0,len=$scope.filtro.itemsFiltrados.length; i<len; i++){
          if($scope.filtro.itemsFiltrados[i].check){
            trabajadoresReajuste.trabajadores.push({ id : $scope.filtro.itemsFiltrados[i].id });        
          }
        }
        console.log(trabajadoresReajuste)
      }else{
        console.log(trab)
        var trabajadoresReajuste = { trabajadores : [{ id : trab }] };
      }
      var datos = trabajador.reajustar().post({}, trabajadoresReajuste);
      datos.$promise.then(function(response){
        if(response.success){
          cargarDatos();
          Notification.success({message: response.mensaje, title: 'Mensaje del Sistema'});
          cargarDatos()
        }else{
          // error
          $scope.erroresDatos = response.errores;
          Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
        }
        $rootScope.cargando = false;
      });
    }

    $scope.toolTipReajustar = function( nombre ){
      return 'Reajustar al trabajador <b>' + nombre + '</b>';
    };

  });

'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:GenerarReportesCtrl
 * @description
 * # GenerarReportesCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('GenerarReportesCtrl', function ($scope, constantes, generarReporte, $filter, filterFilter, $timeout, $rootScope, $uibModal, Notification, $anchorScroll) {
  
    $anchorScroll();
    $scope.constantes = constantes;
    $scope.cargado = false;
    $scope.objeto = {};
    $scope.filtro = {};
    $scope.empresa = $rootScope.globals.currentUser.empresa;
    $scope.objeto = { todosTrabajadores : true, todosConceptos : true, isSelectedConceptos : true,  isSelectedTrabajadores : true };

    function cargarDatos(){
      $rootScope.cargando = true;
      $scope.cargado = false;
      var datos = generarReporte.datos().get();
      datos.$promise.then(function(response){
        $rootScope.cargando = false;
        $scope.conceptos = response.conceptos;
        $scope.haberes = response.haberes;
        $scope.aportes = response.aportes;
        $scope.trabajadores = response.trabajadores;
        $scope.descuentos = response.descuentos;
        $scope.objeto.concepto = $scope.conceptos[0];      
        $scope.datos = $scope[$scope.objeto.concepto.concepto];
        crearModels();
        $scope.filtrar(true);                
        $timeout(function() {
          aumentarLimite();
        }, 250);
        $scope.cargado = true;
      });
    }

    function isThereAllSelected(datos){
      var one = false;
      var all = true;
      if(datos.length > 0){
        for(var i=0, len=datos.length; i<len; i++){
          if(datos[i].check){
            if(!one){
              one = true;
            }
          }else{
            if(all){
              all = false;
            }
          }
        }
      }else{
        all = false;
      }
      var bool = { one : one, all : all };

      return bool;
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

    $scope.selectTrabajadores = function(check){     
      if(!check){
        $scope.objeto.isSelectedTrabajadores = isSelected($scope.filtro.itemsFiltrados);
        if($scope.objeto.todosTrabajadores){
          $scope.objeto.todosTrabajadores = false; 
        }
      }else{
        $scope.objeto.isSelectedTrabajadores = true;
        if(isSelected($scope.filtro.itemsFiltrados)){
          $scope.objeto.todosTrabajadores = true;
        }
      }
    }

    $scope.filtrar = function(crearModels){
      $scope.filtro.itemsFiltrados=[];
      var listaTemp = filterFilter($scope.trabajadores, $scope.filtro.nombre);
      if(listaTemp.length){
        for(var ind in listaTemp){
          if(crearModels){
            listaTemp[ind].check = true;
          }
          $scope.filtro.itemsFiltrados.push( listaTemp[ind] );
        }
      }
      var thereSelected = isThereAllSelected($scope.filtro.itemsFiltrados);
      if(!thereSelected.one){
        $scope.objeto.todosTrabajadores = false;
        $scope.objeto.isSelectedTrabajadores = false;
      };
      if(thereSelected.all){
        $scope.objeto.todosTrabajadores = true;
        $scope.objeto.isSelectedTrabajadores = true;
      };
    };

    $scope.clearText = function(){
      $scope.filtro.nombre = "";
      $scope.filtrar();
    }

    $scope.cargaElementos=0;

    function aumentarLimite(){
      if( $scope.limiteDinamico < $scope.trabajadores.length ){
        $scope.cargaElementos = Math.round(($scope.limiteDinamico/$scope.trabajadores.length) * 100);
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
      for(var i=0,len=$scope.datos.length; i<len; i++){
        $scope.datos[i].check = true;
      }
    }

    $scope.selectAll = function(datos, all){      
      for(var i=0, len=datos.length; i<len; i++){
        datos[i].check = all;
      }
      $scope.objeto.isSelectedTrabajadores = all;
    }

    $scope.selectDatos = function(check){
      var thereSelected = isThereAllSelected($scope.datos);
      if(!check){
        if($scope.objeto.todosConceptos){
          $scope.objeto.todosConceptos = false; 
        }
        $scope.objeto.isSelectedConceptos = thereSelected.one; 
      }else{
        $scope.isSelect = true;
        $scope.objeto.todosConceptos = thereSelected.all;
        $scope.objeto.isSelectedConceptos = true;
      }
    }

    $scope.selectAllConceptos = function(){
      if($scope.objeto.todosConceptos){
        var total = 0;
        $scope.objeto.isSelectedConceptos = true;
        for(var i=0, len=$scope.datos.length; i<len; i++){
          $scope.datos[i].check = true
          total++;  
        }

      }else{
        for(var i=0, len=$scope.datos.length; i<len; i++){
          $scope.datos[i].check = false
        }
        $scope.objeto.isSelectedConceptos = false;
      }
    } 

    cargarDatos();

    $scope.selectConcepto = function(){
      $scope.datos = $scope[$scope.objeto.concepto.concepto];     
      crearModels();
    }

    $scope.generar = function(){
      var obj = { conceptos :  [], trabajadores : [], tipo : $scope.objeto.concepto.concepto };

      for(var i=0,len=$scope.datos.length; i<len; i++){
        if($scope.datos[i].check){
          obj.conceptos.push($scope.datos[i].sid);
        }
      }
      for(var i=0,len=$scope.filtro.itemsFiltrados.length; i<len; i++){
        if($scope.filtro.itemsFiltrados[i].check){
          obj.trabajadores.push($scope.filtro.itemsFiltrados[i].sid);
        }
      }

      var datos = generarReporte.generar().post({}, obj);
      datos.$promise.then(function(response){
        if(response.success){
          Notification.success({message: response.mensaje, title: 'Mensaje del Sistema'});
          descargar();
          $rootScope.cargando = false;
        }else{
          Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          $rootScope.cargando = false;
        }        
      });
    }

    function descargar(){
      var url = $scope.constantes.URL + 'trabajadores/reporte/descargar';
      window.open(url, "_self");
    }

  });

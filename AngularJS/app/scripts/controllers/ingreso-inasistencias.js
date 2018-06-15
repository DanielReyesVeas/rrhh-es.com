'use strict';

/**
 * @ngdoc function
 * @name angularjsApp.controller:IngresoInasistenciasCtrl
 * @description
 * # IngresoInasistenciasCtrl
 * Controller of the angularjsApp
 */
angular.module('angularjsApp')
  .controller('IngresoInasistenciasCtrl', function ($scope, $uibModal, inasistencia, $filter, $anchorScroll, trabajador, constantes, $rootScope, Notification) {
    $anchorScroll();
    
    $scope.datos = [];
    $scope.constantes = constantes;
    $scope.cargado = false;

    function cargarDatos(){
      $rootScope.cargando=true;
      var datos = trabajador.totalInasistencias().get();
      datos.$promise.then(function(response){
        $scope.datos = response.datos;
        $scope.accesos = response.accesos;
        $rootScope.cargando=false;
        $scope.cargado = true;
      });
    };

    $scope.open = function(){
      $rootScope.cargando=true;
      var datos = inasistencia.datos().get({sid: 0});
      datos.$promise.then(function(response){        
        $rootScope.cargando=false;
        $scope.openInasistencia(response);
      })
    }

    cargarDatos();

    $scope.openInasistencia = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-nueva-inasistencia.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormInasistenciasCtrl',
        resolve: {
          objeto: function () {
            return obj;          
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

    $scope.openDetalleInasistencias = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-detalle-inasistencias.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormDetalleInasistenciasCtrl',
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
      var datos = trabajador.inasistencias().get({sid: sid});
      datos.$promise.then(function(response){
        $scope.openDetalleInasistencias( response );
        $rootScope.cargando=false;
      });
    };

    $scope.toolTipDetalle = function( nombre ){
      return 'Gestionar inasistencias del trabajador <b>' + nombre + '</b>';
    };

  })
  .controller('FormDetalleInasistenciasCtrl', function ($rootScope, $uibModal, $filter, Notification, $scope, $uibModalInstance, objeto, inasistencia, trabajador) { 
    
    $scope.trabajador = angular.copy(objeto.datos);
    $scope.accesos = angular.copy(objeto.accesos);

    function cargarDatos(tra){
      $rootScope.cargando=true;
      var datos = trabajador.inasistencias().get({sid: tra});
      datos.$promise.then(function(response){
        $scope.trabajador = response.datos;
        $scope.accesos = response.accesos;
        $rootScope.cargando=false;
      });
    };

    $scope.editar = function(ina, tra){
      $rootScope.cargando=true;
      var datos = inasistencia.datos().get({sid: ina.sid});
      datos.$promise.then(function(response){
        $scope.openInasistencia( response );
        $rootScope.cargando=false;
      });
    };

    $scope.eliminar = function(ina, tra){
      $rootScope.cargando=true;
      $scope.result = inasistencia.datos().delete({ sid: ina.sid });
      $scope.result.$promise.then( function(response){
        if(response.success){
          Notification.success({message: response.mensaje, title:'Notificación del Sistema'});
          cargarDatos(tra);
        }
      });
    }

    $scope.openInasistencia = function(obj){
      var miModal = $uibModal.open({
        animation: true,
        templateUrl: 'views/forms/form-nueva-inasistencia.html?v=' + $filter('date')(new Date(), 'ddMMyyyyHHmmss'),
        controller: 'FormInasistenciasCtrl',
        resolve: {
          objeto: function () {
            return obj;          
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
  .controller('FormInasistenciasCtrl', function ($rootScope, Notification, trabajador, $scope, $uibModalInstance, objeto, inasistencia, fecha) {

    var mesActual = $rootScope.globals.currentUser.empresa.mesDeTrabajo;

    $scope.selectedDates = [];
    var disabledDates = [];
    $scope.totalDias = 0;
    $scope.activeDate = fecha.fechaActiva();
    var ultimoMes = $rootScope.globals.currentUser.empresa.ultimoMes.fechaRemuneracion;
    var primerMes = $rootScope.globals.currentUser.empresa.primerMes.mes;
    $scope.isTrabajador = false;

    if(objeto.datos){
      $scope.titulo = 'Inasistencias';
      $scope.encabezado = 'Modificación Inasistencia';
      $scope.inasistencia = angular.copy(objeto.datos)
      $scope.isEdit = true;
    }else{
      $scope.isEdit = false;
      $scope.inasistencia = { observacion : null };
      $scope.trabajadores = angular.copy(objeto.trabajadores);
      $scope.titulo = 'Inasistencias';
      $scope.encabezado = 'Nueva Inasistencia';
    }

    $scope.motivos = [
                      { id : 1, nombre : 'Falta sin aviso' },
                      { id : 2, nombre : 'Permiso sin goce de sueldo' }
    ];

    $scope.options = {
      startingDay:1,      
      dateDisabled: disabled,
      maxDate: fecha.convertirFecha(ultimoMes),
      minDate: fecha.convertirFecha(primerMes),
      customClass: function(data) {
        if($scope.selectedDates.indexOf(data.date.setHours(0, 0, 0, 0)) > -1) {
          return 'selected';
        }
        return '';
      }
    }    

    function disabled(data) {
      var date = data.date,
        mode = data.mode;
      return disabledDates.indexOf(data.date.setHours(0, 0, 0, 0)) > -1;
    }

    $scope.removeFromSelected = function(dt) {
      var otherDate;
      if($scope.selectedDates.length>1){
        if($scope.selectedDates.indexOf(dt)==1){
          otherDate = $scope.selectedDates[0];
        }else{
          otherDate = $scope.selectedDates[1];
        }
        $scope.selectedDates = [otherDate];
        $scope.activeDate = otherDate;
      }else{
        $scope.selectedDates.splice($scope.selectedDates.indexOf(dt), 1);
        $scope.activeDate = null;
      }
    }

    $scope.select = function(){
      $scope.totalDias = contarDias();
      $scope.isSelect = ($scope.selectedDates.length > 0);
    }

    function contarDias(){
      var cont = 0;
      for(var i=0,len=$scope.selectedDates.length; i<len; i++){
        cont++;
      }
      return cont;
    }

    function contarDiasMes(desde, hasta){
      desde = fecha.convertirFecha(desde);
      hasta = fecha.convertirFecha(hasta);
      var nuevaFecha, tiempo;
      var i=0, cont=0;
      
      do{     
        nuevaFecha = angular.copy(desde);
        tiempo = (i * 86400);
        nuevaFecha.setSeconds(tiempo);
        cont++;
        i++;
      }while(nuevaFecha.setHours(0, 0, 0, 0)!=hasta.setHours(0, 0, 0, 0))

      return cont;
    }

    function crearModels(datos){
      var inasistencias = [];
      for(var i=0,len=datos.inasistencias.length; i<len; i++){
        var desde = fecha.convertirFecha(datos.inasistencias[i].desde);
        var hasta = fecha.convertirFecha(datos.inasistencias[i].hasta);
        var nuevaFecha, tiempo;
        var k=0;
        do{     
          nuevaFecha = angular.copy(desde);
          tiempo = (k * 86400);
          nuevaFecha.setSeconds(tiempo);
          inasistencias.push(nuevaFecha.setHours(0, 0, 0, 0));
          k++;
        }while(nuevaFecha.setHours(0, 0, 0, 0)!=hasta.setHours(0, 0, 0, 0))
      }
      console.log(inasistencias)
      return inasistencias;
    }

    $scope.selectTrabajador = function(){
      $scope.isTrabajador = false;
      $rootScope.cargando=true;
      var datos = trabajador.inasistencias().get({sid: $scope.inasistencia.trabajador.sid});
      datos.$promise.then(function(response){
        disabledDates = crearModels(response.datos);
        console.log(disabledDates)
        $scope.isTrabajador = true;
        $scope.trabajador = response.datos;
        $rootScope.cargando=false;
      });
    }

    function crearObjeto(){   
      var mes, desde, hasta;
      var arr = []; 

      if($scope.selectedDates.length==1){
        var desde = fecha.convertirFechaFormato($scope.selectedDates[0]);
        var obj = { idTrabajador : $scope.trabajador.id, motivo : $scope.inasistencia.motivo, observacion : $scope.inasistencia.observacion, mes : fecha.convertirFechaFormato((fecha.obtenerMes(desde))), desde : desde, hasta : desde, dias : 1 };
        arr.push(obj);
      }else{        
        var mesGuardado;
        var mesAnterior = null;
        $scope.selectedDates.sort();
        for(var i=0,len=$scope.selectedDates.length; i<len; i++){
          mes = new Date($scope.selectedDates[i]).getMonth();

          if(i==0){
            desde = fecha.convertirFechaFormato($scope.selectedDates[0]);
          }else{
            if(mes==mesAnterior){       
              if((i + 1)==len){
                if(mesGuardado!=mes){   
                  var obj = {};
                  obj.mes = fecha.convertirFechaFormato((fecha.obtenerMes(desde)));
                  obj.desde = desde;
                  obj.idTrabajador = $scope.trabajador.id;
                  obj.observacion = $scope.inasistencia.observacion;
                  obj.motivo = $scope.inasistencia.motivo;
                  obj.hasta = fecha.convertirFechaFormato($scope.selectedDates[i]);
                  obj.dias = contarDiasMes(obj.desde, obj.hasta);
                  desde = fecha.convertirFechaFormato($scope.selectedDates[i]);
                  mesGuardado = obj.mes;
                  arr.push(obj);
                }
              }
            }else{
              if(mes!=mesGuardado){
                var obj = {};
                obj.mes = fecha.convertirFechaFormato((fecha.obtenerMes(desde)));
                obj.desde = desde;
                obj.idTrabajador = $scope.trabajador.id;
                obj.observacion = $scope.inasistencia.observacion;
                obj.motivo = $scope.inasistencia.motivo;
                obj.hasta = fecha.convertirFechaFormato($scope.selectedDates[i-1]);
                obj.dias = contarDiasMes(obj.desde, obj.hasta);
                mesGuardado = obj.mes;
                arr.push(obj);
                desde = fecha.convertirFechaFormato($scope.selectedDates[i]);
              }
            }
          }
          mesAnterior = mes;
        }
      }
      return arr;
    }

    $scope.guardar = function(){
      $rootScope.cargando=true;
      var inas = crearObjeto();    
      var response;

      if( $scope.inasistencia.sid ){
        response = inasistencia.datos().update({sid:$scope.inasistencia.sid}, inas);
      }else{
        response = inasistencia.datos().create({}, inas);
      }
      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, sidTrabajador : trabajador.sid });
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    }

    $scope.calcularDias = function(){
      if($scope.inasistencia.desde && $scope.inasistencia.hasta){
        if($scope.inasistencia.desde == $scope.inasistencia.hasta){
          $scope.inasistencia.dias = 1;
        }else{
          $scope.inasistencia.dias = (($scope.inasistencia.hasta - $scope.inasistencia.desde) / 86400000 + 1);
        }
      }
    }

    // Fecha
    $scope.dateOptions = {
      formatYear: 'yy',
      maxDate: fecha.convertirFecha(mesActual.fechaRemuneracion),
      minDate: fecha.convertirFecha(mesActual.mes),
      startingDay: 1
    };  

    $scope.openFechaHasta = function() {
      $scope.popupFechaHasta.opened = true;
    };

    $scope.openFechaDesde = function() {
      $scope.popupFechaDesde.opened = true;
    };

    $scope.format = ['dd-MMMM-yyyy'];

    $scope.popupFechaHasta = {
      opened: false
    };
    $scope.popupFechaDesde = {
      opened: false
    };

    /*var mesActual = $rootScope.globals.currentUser.empresa.mesDeTrabajo;
    
    if(objeto.trabajador){
      $scope.trabajador = angular.copy(objeto.trabajador);
      $scope.inasistencia = angular.copy(objeto);
      $scope.inasistencia.desde = fecha.convertirFecha($scope.inasistencia.desde);
      $scope.inasistencia.hasta = fecha.convertirFecha($scope.inasistencia.hasta);
      $scope.isEdit = true;
      $scope.titulo = 'Modificación Inasistencia';
    }else{
      $scope.trabajador = angular.copy(objeto);
      $scope.isEdit = false;
      $scope.titulo = 'Ingreso Inasistencia';
      $scope.inasistencia = { desde : fecha.fechaActiva(), hasta : fecha.fechaActiva() };
    }

    $scope.motivos = [
                      { id : 1, nombre : 'Falta sin aviso' },
                      { id : 2, nombre : 'Permiso sin goce de sueldo' }
    ];

    $scope.guardar = function(inasist, trabajador){
      console.log(inasist)
      $rootScope.cargando=true;
      var mes = $rootScope.globals.currentUser.empresa.mesDeTrabajo;
      var response;
      if(inasist.desde==fecha.fechaActiva()){
        inasist.desde = fecha.convertirFecha(fecha.convertirFechaFormato(inasist.desde));
      }
      if(inasist.hasta==fecha.fechaActiva()){
        inasist.hasta = fecha.convertirFecha(fecha.convertirFechaFormato(inasist.hasta));
      }
      var Inasistencia = { idTrabajador : trabajador.id, idMes : mes.id, desde : inasist.desde, hasta : inasist.hasta, dias : inasist.dias, motivo : inasist.motivo, observacion : inasist.observacion };

      if( $scope.inasistencia.sid ){
        response = inasistencia.datos().update({sid:$scope.inasistencia.sid}, Inasistencia);
      }else{
        response = inasistencia.datos().create({}, Inasistencia);
      }
      response.$promise.then(
        function(response){
          if(response.success){
            $uibModalInstance.close({ mensaje : response.mensaje, sidTrabajador : trabajador.sid });
          }else{
            // error
            $scope.erroresDatos = response.errores;
            Notification.error({message: response.mensaje, title: 'Mensaje del Sistema'});
          }
          $rootScope.cargando=false;
        }
      );
    }

    $scope.calcularDias = function(){
      if($scope.inasistencia.desde && $scope.inasistencia.hasta){
        $scope.inasistencia.dias = (($scope.inasistencia.hasta - $scope.inasistencia.desde) / 86400000 + 1);
      }
    }
    
    // Fecha

    $scope.dateOptions = {
      formatYear: 'yy',
      maxDate: fecha.convertirFecha(mesActual.fechaRemuneracion),
      minDate: fecha.convertirFecha(mesActual.mes),
      startingDay: 1
    };  

    $scope.openFechaHasta = function() {
      $scope.popupFechaHasta.opened = true;
    };

    $scope.openFechaDesde = function() {
      $scope.popupFechaDesde.opened = true;
    };

    $scope.format = ['dd-MMMM-yyyy'];

    $scope.popupFechaHasta = {
      opened: false
    };

    $scope.popupFechaDesde = {
      opened: false
    };*/

  });

'use strict';

/**
 * @ngdoc service
 * @name angularjsApp.empresa
 * @description
 * # empresa
 * Factory in the angularjsApp.
 */
angular.module('angularjsApp')
  .factory('empresa', function ($resource, constantes) {
    return {
      datos: function () {
        return $resource(constantes.URL + 'empresas/:id',
          {id : '@id'},
          {
            update : { 'method': 'PUT' },
            delete : { 'method': 'DELETE' },
            create : { 'method': 'POST' }
          }
        );
      },
      listaSelect : function(){
        return $resource(constantes.URL + 'empresas/lista-empresas/json');
      },
      empresasSistema : function(){
        return $resource(constantes.URL + 'empresas/sistemas/obtener');
      },
      datosEmpresaPortalTrabajador : function(sid){
        return $resource(constantes.URL + 'empresas/datos-empresa/portal-trabajador/' + sid);
      },
      configuracion : function(){
        return $resource(constantes.URL + 'empresas/configuracion/obtener');
      },
      habilitar: function(){
        return $resource(constantes.URL + 'empresas/habilitar/cambiar', {},{
          post : { method : 'POST'}
        });
      },
      cambiarConfiguracion: function(){
        return $resource(constantes.URL + 'empresas/configuracion/cambiar', {},{
          post : { method : 'POST'}
        });
      },
      cambiarValor: function(){
        return $resource(constantes.URL + 'empresas/valor-configuracion/cambiar', {},{
          post : { method : 'POST'}
        });
      }
    };
  });

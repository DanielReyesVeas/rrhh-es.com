'use strict';

/**
 * @ngdoc service
 * @name angularjsApp.descuentoHora
 * @description
 * # descuentoHora
 * Factory in the angularjsApp.
 */
angular.module('angularjsApp')
  .factory('descuentoHora', function (constantes, $resource) {
        return {
            datos: function () {
                return $resource(constantes.URL + 'descuentos-horas/:sid',
                    {sid : '@sid'},
                    {   
                        update : { 'method': 'PUT' },
                        delete : { 'method': 'DELETE' },
                        create : { 'method': 'POST' }
                    }
                );
            }
        };
  });
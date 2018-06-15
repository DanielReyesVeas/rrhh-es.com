'use strict';

/**
 * @ngdoc service
 * @name angularjsApp.atraso
 * @description
 * # atraso
 * Factory in the angularjsApp.
 */
angular.module('angularjsApp')
  .factory('atraso', function (constantes, $resource) {
        return {
            datos: function () {
                return $resource(constantes.URL + 'atrasos/:sid',
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
angular.module("logueo").controller('controlLogueo',controlLogueo);

controlLogueo.$inject = ['$scope','$rootScope','$cookieStore','$timeout' ,'factoria'];

function controlLogueo($scope, $rootScope, $cookieStore, $timeout, factoria){

	$scope.message ='Hola Soy JUAN';
	alert(factoria.mensaje('JUAN CAMEJO'));
	$scope.setCookie = function(){
		$cookieStore.put('user', {name: 'juan', tipe: 'admin'});
	}
	$scope.llamaCookie = function(){

		alert($cookieStore.get('user').name)
	}

	$scope.eliminarCookie = function(){

		try{
					$cookieStore.remove('user')
		}
		catch(err){
			alert('Error eliminado');
		}
	}
}





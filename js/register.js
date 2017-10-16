
'use strict'

angular.module('services')
.controller('registro',registro)
.controller('login', login)
.controller('moviesRegister',moviesRegister)
.controller('seriesRegister', seriesRegister)
.controller('dashboard', dashboard)



registro.$inject = ['$scope','$http', '$rootScope', '$window', 'AuthenticationService'];
function registro($scope, $http,$rootScope, $window, AuthenticationService){



$scope.data = {}

$scope.registrox = function(){
	$rootScope.load = true;

	AuthenticationService.registro($scope.data.username, $scope.data.password, $scope.data.email)
	.then(function(response){
		var respuesta = JSON.parse(response);

		if(respuesta.data.message == 'User registered'){
			alert('succesfully registered.');
			$window.location.reload();
		}
	})
	.catch(function(err){
		console.log(err);
	})


}
}


login.$inject = ['$rootScope','$scope','$http','AuthenticationService',  '$window'];
function login($rootScope,$scope,$http,AuthenticationService,  $window){

$scope.currentStatus = $rootScope.globals.status;
$scope.data = {}
$scope.login = function(){

	AuthenticationService.login($scope.data.username,$scope.data.password)
	.then(function(success){
			var respuesta = JSON.parse(success);
			if(respuesta.data.error == 0){
				AuthenticationService.setCookie(respuesta.data.username, respuesta.data.user_type, respuesta.data.id)
				alert("succesfully logged in.");
				$window.location.reload();

			}else{
				alert('Username or password wrong.');
			}
	})
	.catch(function(err){
		console.log(err)
	})
}


$scope.getOut = function(){
	AuthenticationService.deleteCookies();
} 
}



dashboard.$inject = ['$rootScope','$scope'];
function dashboard($rootScope, $scope){
	console.log("testing");

$rootScope.load = true;
$scope.currentUser = $rootScope.globals.currentUser.level;

}

/*REVISAR SERIES */
seriesRegister.$inject = ['$rootScope','$scope','$http','$q', 'AuthenticationService','$cookieStore', '$window']

function seriesRegister($rootScope, $scope, $http, $q, AuthenticationService, $cookieStore,$window){
	console.log("testing");
			
			var id = $cookieStore.get('globals');

	if(!id || id.currentUser.level == '1' || id.currentUser.level == '2'){

		$window.location = '';
	}
	$rootScope.load = true;
	$scope.x = id.currentUser.level;
	$scope.dataComplete = {
		genres: [],
		poster_path: '' ,
		original_name: '',
		overview: '',
		id: '',
		creation: '',
		'meta_description': '',
		'meta_title': '',
		'meta_tags': ''
	};

	$scope.addImage = function(){

		var url = prompt("Ingrese URL de la imagen");

		$scope.dataComplete['poster_path'] = url;
	}



	$scope.checkSerie = function(){

		AuthenticationService.serieCheck($scope.data.id)
		.then(function(response){
			var fecha = response['first_air_date'];
			var posicion = fecha.indexOf('-');
			var creacion = fecha.slice(0,posicion);
			$scope.dataComplete['poster_path'] = response['poster_path'];
			$scope.dataComplete['original_name'] = response['original_name'];
			$scope.dataComplete['meta_title'] = response['original_name'];
			$scope.dataComplete['meta_description'] = response['overview'];
			$scope.dataComplete['overview'] = response['overview'];
			$scope.dataComplete['id'] = response['id'];
			$scope.dataComplete['rating'] = response['vote_average'];
			$scope.dataComplete['creation'] = creacion;

			for(var x = 0; x<response.genres.length; x++)
			{
					$scope.dataComplete.genres.push(response.genres[x].name)
			}

		})
		.catch(function(err){
			console.log(err);
		})
	}

		$scope.saveSerie = function(){
		AuthenticationService.saveSerie($scope.dataComplete)
		.then(function(response){
			var respuesta = JSON.stringify(response)
			var response = JSON.parse(respuesta);
			if(response.message == 'The movie is already registered'){
				alert("This serie is already registered in the database.")
			}

			if(response.message == 'The serie was succesfully registered'){
				alert("Serie succesfully register!.")
				$window.location.reload();
			}
		})
		.catch(function(err){
			console.log(err);
		})
	}



}

/*REVISAR PELICULAS*/

moviesRegister.$inject = ['$rootScope','$scope','$http','$q', 'AuthenticationService', '$cookieStore', '$window']

function moviesRegister($rootScope, $scope, $http, $q, AuthenticationService,$cookieStore,$window){
	console.log("testing");
			
			var id = $cookieStore.get('globals');

	if(!id || id.currentUser.level == '1' || id.currentUser.level == '2'){

		$window.location = '';
	}
	$rootScope.load = true;
	$scope.x = id.currentUser.level;

	$scope.dataComplete = {
		genres : [],
		poster_path: '' ,
		original_title: '',
		overview: '',
		id: '',
		quality: '',
		creation: 0,
		'meta_description': '',
		'meta_title': '',
		'meta_tags': ''
	};

	$scope.addImage = function(){

		var url = prompt("Ingrese URL de la imagen");

		$scope.dataComplete['poster_path'] = url;
	}


	$scope.addGenre = function(){

		$scope.dataComplete['genres'].push($scope.data.genre);

	}

	$scope.checkMovie = function(){
		AuthenticationService.movieCheck($scope.data.id)
		.then(function(response){
			var respuesta = JSON.stringify(response);
			var respuestaParseada = JSON.parse(respuesta);
			var fecha = respuestaParseada['release_date'];
			var posicion = fecha.indexOf('-');
			var creacion = fecha.slice(0,posicion);
			$scope.dataComplete['poster_path'] = respuestaParseada['poster_path'];
			$scope.dataComplete['original_title'] = respuestaParseada['original_title'];
			$scope.dataComplete['overview'] = respuestaParseada['overview'];
			$scope.dataComplete['meta_description'] = respuestaParseada['overview'];
			$scope.dataComplete['meta_title'] = respuestaParseada['original_title'];
			$scope.dataComplete['id'] = respuestaParseada['id'];
			$scope.dataComplete['rating'] = respuestaParseada['vote_average'];
			$scope.dataComplete['creation'] = creacion;

			for(var x = 0; x<respuestaParseada.genres.length; x++)
			{
					$scope.dataComplete.genres.push(respuestaParseada.genres[x].name)
			}

			console.log('Esta es la respueta '+ $scope.dataComplete);
			console.log('Data Completa' + $scope.dataComplete['original_title']);

		})
		.catch(function(err){
			console.log(err);
		})
	}


	$scope.saveMovie = function(){
		AuthenticationService.saveMovie($scope.dataComplete)
		.then(function(response){
			var respuesta = JSON.stringify(response);
			var response = JSON.parse(respuesta);
			if(response.message == 'The movie is already registered'){
				alert("This movie is already registered in the database.")
			}

			if(response.message == 'The movie was succesfully registered'){
				alert("Movie succesfully register!.")
				$window.location.reload();
			}
		})
		.catch(function(err){
			console.log(err);
		})
	}

}





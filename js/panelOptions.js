(function(){
	'use strict'

angular.module('panelOptions')
.controller('membersPanel',membersPanel)
.controller('profilePanel',profilePanel)
.controller('seasonPanel',seasonPanel)
.controller('panelPlaylist',panelPlaylist)
.controller('panelWatched',panelWatched)
.controller('panelFavorites',panelFavorites)
.controller('resumeLinks',resumeLinks)
.controller('myLinksModerate',myLinksModerate)
.controller('moderateLinks',moderateLinks)
.controller('reportedLinks',reportedLinks)




membersPanel.$inject = ['$rootScope','$http', '$scope', '$q', '$cookieStore', '$window'];
function membersPanel($rootScope,$http,$scope,$q,$cookieStore, $window){

		var id = $cookieStore.get('globals');

	if(!id || id.currentUser.level == '1' || id.currentUser.level == '2'){

		$window.location = '';
	}

	$scope.x = id.currentUser.level;


	$scope.members = [];

	$http.get('http://45.55.62.152/New/proccess/classes/users.class.php?getusers=1')
	.success(function(response){

		$scope.members = response;
		console.log(response);
		$rootScope.load = true;
	})
	.error(function(err){
		console.log(err);
	})


	$scope.userTypeComplete = function(userType){

		switch(userType){

			case 1:

				return "Member"
				break;

			case 2:

				return "Uploader"
				break;

			case 3: 

				return "Moderator"
				break;

			case 4:

				return "Admin"
				break;

		}

	}

	$scope.changeUserLevel = function(arreglo){

	$http({
		method : "post",
		url: "http://45.55.62.152/New/proccess/classes/users.class.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({update:'1', id:arreglo.id, type:arreglo.type})
	})
	.then(function(data){
		console.log(data);
		if(data.data.error == false){
			alert("User Level Updated!")
		}
		else
		{
			alert("Error Updating User")
		}
	})
	.catch(function(err){

		console.log(err)
	})



	}

}

profilePanel.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window'];

function profilePanel($rootScope,$http,$scope,$q, $cookieStore, AuthenticationService,$window){
	var id = $cookieStore.get('globals');

	if(!id){

		$window.location = '';
	}

	$scope.x = id.currentUser.level;


	$scope.info = 	{username: id.currentUser.username,
					id:  id.currentUser.id,
					type: id.currentUser.level};

	AuthenticationService.getAllInfoUser(id.currentUser.id)
	.then(function(response){

			var respuesta = JSON.stringify(response);
			var respuestaParseada = JSON.parse(respuesta);
			console.log(respuestaParseada);
			$scope.info.nickname = respuestaParseada[0].nickname;
			$scope.info.fname = respuestaParseada[0]['first_name'];
			$scope.info.lname = respuestaParseada[0]['last_name'];
			$scope.info.bio = respuestaParseada[0]['biography'];
			$scope.info.email = respuestaParseada[0]['email'];
			$scope.info.website = respuestaParseada[0]['webpage'];
			$scope.info.facebook = respuestaParseada[0]['facebook'];
			$scope.info.twitter = respuestaParseada[0]['twitter'];
			$scope.info.gp = respuestaParseada[0]['gplus'];

			$rootScope.load = true;
	})
	.catch(function(err){

		alert('respuesta2' + err);
	})



	$scope.actualizarInfo = function(){


		AuthenticationService.saveAllInfoUser($scope.info)
		.then(function(response){
			var respuesta = JSON.stringify(response);
			var respuestaParseada = JSON.parse(respuesta);

			if(respuestaParseada.error == false){
				alert('User successfully Updated!')
			}else{
				alert('Error updating user, please contact with a admin.')
			}

		})
		.catch(function(err){

			console.log("error" + err)
		})


	}
}



seasonPanel.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window'];

function seasonPanel($rootScope,$http,$scope,$q, $cookieStore, AuthenticationService, $window){
	$rootScope.load = true;

				var id = $cookieStore.get('globals');

	if(!id || id.currentUser.level == '1' || id.currentUser.level == '2'){

		$window.location = '';
	}


	AuthenticationService.listSerie()
	.then(function(resp){
		var respuestaParseada = JSON.parse(resp);
		$scope.listSerie = respuestaParseada.data;
	})
	.catch(function(err){
		console.log(err)
	})


$scope.change = function(){

	var ide = $scope.listOfSeries.id;
	$scope.seasonTotal = [];
			$scope.showList = false;
	AuthenticationService.listSeason(ide)
	.then(function(resp){
		var respuestaParseada = JSON.parse(resp);
		if(respuestaParseada.data.length == 0){
			$scope.mensaje = "No Seasons Registered";
			$scope.button = true;
		}
		else{
			$scope.mensaje = "";
		$scope.seasonTotal = respuestaParseada.data;
		$scope.button = true;

	}
	})
	.catch(function(err){
		console.log(err)
	})
}

$scope.saveDatabase = function(){
	var season = $scope.listOfSeason.id;
	var episodes = $scope.listOfEpisodes;
	AuthenticationService.addEpisodes(season, episodes)
	.then(function(resp){
		var response = JSON.parse(resp);
		console.log(response.data);
		alert(response.data.message);
	})
	.catch(function(err){
		console.log(err);
	})
}

$scope.seasonChange = function(){

	var ide = $scope.listOfSeason.id;
	AuthenticationService.checkSeasonEpisodes(ide)
	.then(function(resp){

		var respuestaParseada = JSON.parse(resp);
		console.log(respuestaParseada);
		$scope.listOfEpisodes = [];
		if(respuestaParseada.data.length == 0){
			$scope.msjTwo = "No episodes registered.";
			$scope.showButton = true;
			$scope.listOfEpisodes = [];

		}
		else{
			
			$scope.msjTwo = "";
			$scope.showButton = true;
			$scope.showList = true;
			for(var x = 0; x<respuestaParseada.data.length; x++){
				var episodes = {};
				episodes.id  = respuestaParseada.data[x].id;
				episodes.title = respuestaParseada.data[x].title;
				episodes.overview = respuestaParseada.data[x].overview;
				episodes.episode = respuestaParseada.data[x]['episode'];
				episodes.release = new Date(respuestaParseada.data[x]['release_date']);
				episodes['meta_description'] = respuestaParseada.data[x]['meta_description'];
				episodes['meta_title'] = respuestaParseada.data[x]['meta_title'];
				episodes['meta_tags'] = '';
				var date = new Date(respuestaParseada.data[x]['register_date']);
				episodes.dbdate  = date;
				$scope.listOfEpisodes.push(episodes);

			}
	}
	})
	.catch(function(err){
		console.log(err)
	})
}

$scope.addNewEpisode = function(){

	var number = $scope.newEpisode;
	var block = false;
	for( var x=0; x<$scope.listOfEpisodes.length; x++){

		var y = $scope.listOfEpisodes[x].episode;
		if(number == y){
			alert("This chapter number is already register")
			block = true;
		}

	}

	if(block == false){
	var datex = new Date();
	var newEpisodio = {
		episode: number,
		dbdate: datex,
		title: '',
		overview: '',
		release: '',
		meta_description: '',
		meta_title: '',
		meta_tags: '',
		id: 'NE'
	}
	$scope.listOfEpisodes.push(newEpisodio);
	$scope.showList = true;
	}else{
		alert("This episode can´t be added. Already registered")
	}
}

$scope.delete = function(x){

		AuthenticationService.deleteSeason(x)
	.then(function(resp){
		var respuestaParseada = JSON.parse(resp);

		alert(respuestaParseada.data.message);
	})
	.catch(function(err){
		console.log(err)
	})
}


$scope.addNewSeason = function(){

	var NewSeason = $scope.seasonNumber; 
	var idSerie = $scope.listOfSeries.id;


		AuthenticationService.addSeasonNew(idSerie,NewSeason)
	.then(function(resp){
		var respuestaParseada = JSON.parse(resp);

		alert(respuestaParseada.data.message);
	})
	.catch(function(err){
		console.log(err)
	})

}
	$scope.x = id.currentUser.level;
	

			$scope.data = {
				episodes: []
			};


$scope.checkSeason = function(){

	AuthenticationService.seasonCheck($scope.data)
	.then(function(response){
			$scope.data.episodes = [];
			var respuesta = JSON.stringify(response);
			var respuestaParseada = JSON.parse(respuesta);
			console.log(respuestaParseada);
			for(var x = 0; x<respuestaParseada.episodes.length; x++){
				var episodes = {};
				episodes.title = respuestaParseada.episodes[x].name;
				episodes.overview = respuestaParseada.episodes[x].overview;
				episodes.episode = respuestaParseada.episodes[x]['episode_number'];
				episodes.release = respuestaParseada.episodes[x]['air_date'];
				episodes['meta_description'] = respuestaParseada.episodes[x].overview;
				episodes['meta_title'] = respuestaParseada.episodes[x].name;
				episodes['meta_tags'] = '';
				episodes.dbdate  = new Date();
				$scope.data.episodes.push(episodes);
				$rootScope.load = true;
			}
	})
	.catch(function(err){

		alert('respuesta2' + err);
	})
}



$scope.addSeasonAndEpisode = function(){

	AuthenticationService.addSeasonAndEpisode($scope.data)
	.then(function(response){
		var respuesta = JSON.stringify(response);
		var respuestaCompleta = JSON.parse(respuesta);
		console.log(respuestaCompleta)
	})
	.catch(function(err){
		console.log(err);
	})


}



}

panelPlaylist.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window'];
function panelPlaylist($rootScope,$http,$scope,$q,$cookieStore,AuthenticationService,$window){

	var user = $cookieStore.get('globals');
	console.log('userx',user)

	var id = user.currentUser.id;

	if(!id){

		$window.location = '';
	}

	

	AuthenticationService.resumePlaylist(id)
	.then(function(response){
		var respuesta = JSON.parse(response);
		console.log(respuesta);
		$scope.movies = respuesta.data.movies;
		$scope.episodes = respuesta.data.episodes;
		$scope.series = respuesta.data.series;
	
		$rootScope.load = true;

	})
	.catch(function(err){

		console.log(err);
	})


}


panelWatched.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window','$sce'];
function panelWatched($rootScope,$http,$scope,$q,$cookieStore,AuthenticationService,$window,$sce){

	var user = $cookieStore.get('globals');
	console.log('userx',user)

	var id = user.currentUser.id;

	if(!id){

		$window.location = '';
	}

	

	AuthenticationService.resumeWatched(id)
	.then(function(response){
		var respuesta = JSON.parse(response);
		console.log(respuesta);
		$scope.movies = respuesta.data.movies;
		$scope.episodes = respuesta.data.episodes;
		$scope.series = respuesta.data.series;
		$rootScope.load = true;


	})
	.catch(function(err){

		console.log(err);
	})



}



panelFavorites.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window','$sce'];
function panelFavorites($rootScope,$http,$scope,$q,$cookieStore,AuthenticationService,$window,$sce){

	var user = $cookieStore.get('globals');
	console.log('userx',user)

	var id = user.currentUser.id;

	if(!id){

		$window.location = '';
	}

	

	AuthenticationService.resumeFavorite(id)
	.then(function(response){
		var respuesta = JSON.parse(response);
		console.log(respuesta);
		$scope.movies = respuesta.data.movies;
		$scope.episodes = respuesta.data.episodes;
		$scope.series = respuesta.data.series;
		$rootScope.load = true;


	})
	.catch(function(err){

		console.log(err);
	})



}


resumeLinks.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window','$sce'];
function resumeLinks($rootScope,$http,$scope,$q,$cookieStore,AuthenticationService,$window,$sce){

	var user = $cookieStore.get('globals');
	console.log('userx',user)

	var id = user.currentUser.id;

	if(!id){

		$window.location = '';
	}

	

	AuthenticationService.linksResume(id)
	.then(function(response){
		var respuesta = JSON.parse(response);
		console.log(respuesta);
		$scope.movies = respuesta.data.movies;
		$scope.episodes = respuesta.data.episodes;
		$rootScope.load = true;
	


	})
	.catch(function(err){

		console.log(err);
	})



}

myLinksModerate.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window','$sce'];
function myLinksModerate($rootScope,$http,$scope,$q,$cookieStore,AuthenticationService,$window,$sce){

	var user = $cookieStore.get('globals');
	console.log('userx',user)

	var id = user.currentUser.id;

	if(!id){

		$window.location = '';
	}

	$scope.idArray = [];
	$scope.idArrayM = [];

	AuthenticationService.linksResume(id)
	.then(function(response){
		var respuesta = JSON.parse(response);
		console.log(respuesta);
		$scope.movies = respuesta.data.movies;
		$scope.episodes = respuesta.data.episodes;
		$rootScope.load = true;
	})
	.catch(function(err){

		console.log(err);
	})


$scope.delete = function(){

		AuthenticationService.deleteLinks($scope.idArray)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links deleted successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}

$scope.deleteM = function(){

		AuthenticationService.deleteLinksM($scope.idArrayM)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links deleted successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}

}

moderateLinks.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window','$sce'];
function moderateLinks($rootScope,$http,$scope,$q,$cookieStore,AuthenticationService,$window,$sce){

	var user = $cookieStore.get('globals');


	var id = user.currentUser.id;

	if(!id){

		$window.location = '';
	}

	$scope.idArray = [];
	$scope.idArrayM = [];

	AuthenticationService.linksForModerate()
	.then(function(response){
		var respuesta = JSON.parse(response);
		console.log(respuesta);
		$scope.movies = respuesta.data.movies;
		$scope.episodes = respuesta.data.episodes;
		$rootScope.load = true;
	})
	.catch(function(err){

		console.log(err);
	})


$scope.delete = function(){

		AuthenticationService.deleteLinks($scope.idArray)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links deleted successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}

$scope.deleteM = function(){

		AuthenticationService.deleteLinksM($scope.idArrayM)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links deleted successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}




$scope.approve = function(){

		AuthenticationService.activate($scope.idArray)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links activated successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}

$scope.approveM = function(){

		AuthenticationService.activateM($scope.idArrayM)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links activated successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}


}




reportedLinks.$inject = ['$rootScope','$http','$scope', '$q', '$cookieStore', 'AuthenticationService', '$window','$sce'];
function reportedLinks($rootScope,$http,$scope,$q,$cookieStore,AuthenticationService,$window,$sce){

	var user = $cookieStore.get('globals');
	var id = user.currentUser.id;

	if(!id){

		$window.location = '';
	}


		if(id.level == 1 || id.level == 2){

		$window.location = '';
	}

	$scope.idArray = [];
	$scope.idArrayM = [];

	AuthenticationService.reported(id)
	.then(function(response){
		var respuesta = JSON.parse(response);
		console.log(respuesta);
		$scope.movies = respuesta.data.movies;
		$scope.episodes = respuesta.data.episodes;
		$rootScope.load = true;
	})
	.catch(function(err){

		console.log(err);
	})


$scope.delete = function(){

		AuthenticationService.deleteLinks($scope.idArray)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links deleted successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}

$scope.deleteM = function(){

		AuthenticationService.deleteLinksM($scope.idArrayM)
	.then(function(response){
		var respuesta = JSON.parse(response);
		var respuestaCompleta = respuesta.data.error;
		console.log(respuesta);
		if(respuestaCompleta == false){

				alert('Links deleted successfully.')

		}else{
			alert('Problem deleting links.')
		}
		

	})
	.catch(function(err){

		console.log(err);
	})

}

}





})()
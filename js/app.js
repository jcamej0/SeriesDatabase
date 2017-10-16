
'use strict'


angular.module('services',[]);
angular.module('infoSerie',[]);
angular.module('panelOptions',[]);
angular.module('testApp',['ngCookies','services','infoSerie','ngSanitize','panelOptions','angularUtils.directives.dirPagination'])
	.directive('toggle', function(){
  return {
    restrict: 'A',
    link: function(scope, element, attrs){
      if (attrs.toggle=="tooltip"){
        $(element).tooltip();
      }
      if (attrs.toggle=="popover"){
        $(element).popover();
      }
    }
  };
})
		.controller('index',index)
		.controller('newMovies',newMovies)
		.controller('newSeries',newSeries)
		.controller('serieData',serieData)
		.controller('movieData',movieData)
		.controller('tv',tv)
		.controller('serieBase',serieBase)
		.controller('page',page)

		.run(run)


run.$inject = ['$rootScope','$location','$cookieStore'];
function run($rootScope,$location,$cookieStore){

	$rootScope.globals = $cookieStore.get('globals') || {status: 'off'}; 

		 /*$rootScope.$on('$locationChangeStart', function (event, next, current) {
            // redirect to login page if not logged in and trying to access a restricted page
            var restrictedPage = $.inArray($location.path(), ['/login', '/inicio']) === -1;
            var loggedIn = $rootScope.globals.currentUser;
            if (restrictedPage && !loggedIn) {
                $location.path('/login');
            }
        })*/

}




newSeries.$inject  = [ '$rootScope', '$scope', 'AuthenticationService'];


function newSeries($rootScope, $scope, AuthenticationService){

$scope.series;

AuthenticationService.getAllSeries()
.then(function(response){
	$scope.series = response;
	$rootScope.allLoaded = true;
})
.catch(function(err){

	console.log(err)

})

}







index.$inject  = [ '$rootScope', '$scope' , 'AuthenticationService', '$cookieStore'];
function index($rootScope, $scope, AuthenticationService, $cookieStore){

	if($rootScope.globals.status == 'off'){

		$scope.login = false;
	}else{

		$scope.login = true;
		 $scope.level = $cookieStore.get('globals').currentUser.level;
	}



		AuthenticationService.allPages()
.then(function(response){
	var respuestaCompleta = JSON.parse(response);
	console.log(respuestaCompleta);
	$scope.pages = respuestaCompleta.data;
})
.catch(function(err){

	console.log(err)

})


		AuthenticationService.number()
.then(function(response){
	var respuestaCompleta = JSON.parse(response);
	console.log(respuestaCompleta);
	$scope.reported = respuestaCompleta.data.reported_links;
	$scope.moderate = respuestaCompleta.data.waiting_links;
})
.catch(function(err){

	console.log(err)

})


}




newMovies.$inject  = [ '$rootScope', '$scope', 'AuthenticationService'];
function newMovies($rootScope, $scope, AuthenticationService){


$scope.movies;

AuthenticationService.getAllMovies()
.then(function(response){
	console.log(response);
	$scope.movies = response;
	$rootScope.load = true;
})
.catch(function(err){

	console.log(err)

})
}




movieData.$inject = ['$rootScope','$http','$location',  '$scope', 'AuthenticationService', '$cookieStore','$sce', '$window'];
function movieData($rootScope, $http,$location,$scope,AuthenticationService, $cookieStore,$sce, $window){
	var url = $location.$$absUrl;
	var distancia = url.length;
	var posicionMovie = url.indexOf('movie/');
	var urlCortada = url.slice(posicionMovie+6,distancia)
	$scope.slug = urlCortada;
	$scope.dfb = {}
	$scope.iframe = {};
	$scope.showInput = false;
	$scope.box = [];

	if($cookieStore.get('globals') != null){
				$scope.idArray = [];
				$scope.idArrayM = [];

				$scope.level = $cookieStore.get('globals').currentUser.level;
		
				$scope.online = {
				user: $cookieStore.get('globals').currentUser.id,
				};

	$scope.download = {
		user: $cookieStore.get('globals').currentUser.id,
	};

		$scope.dfb.user = $cookieStore.get('globals').currentUser.id;



			AuthenticationService.movieCheckX($scope.slug)
			.then(function(data){
			$scope.data = data[0];
			console.log(data);
			$scope.dfb.movie = $scope.data.id;
			$scope.online.movie = $scope.data.id;
			$scope.download.movie = $scope.data.id;
			$scope.myLink =  $sce.trustAsResourceUrl($scope.data.iframe)
			$scope.iframe.movie = $scope.data.id;
			

		AuthenticationService.checkWatchedMovie($scope.dfb)
	.then(function(data){

	$scope.checkStatus = data;
	console.log("stats",$scope.checkStatus)

		
	})
	.catch(function(err){
		console.log(err)
	})


AuthenticationService.checkFavoriteMovie($scope.dfb)
	.then(function(data){

		$scope.favoriteStatus = data;
		console.log("favoriteStatus",$scope.favoriteStatus)

		
	})
	.catch(function(err){
		console.log(err)
	})



	AuthenticationService.checkPlaylistMovie($scope.dfb)
	.then(function(data){

		$scope.playlistStatus = data;
		console.log("playlistStatus",$scope.playlistStatus)
		$rootScope.load = true;
		
	})
	.catch(function(err){
		console.log(err)
	})




		
	})
	.catch(function(err){
		console.log(err)
	})



$scope.addWatched = function(){

var data = $scope.dfb;
	AuthenticationService.addWatchedMovie(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Marked as watched!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.noWatch = function(){

var data = $scope.checkStatus;
	AuthenticationService.noWatchMovie(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no watch!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}

$scope.addPlaylistMovie = function(){

var data = $scope.dfb;
	AuthenticationService.addPlaylistMovie(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Added to playlist!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.noPlaylistMovie = function(){

var data = $scope.playlistStatus;
	AuthenticationService.noPlaylistMovie(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no playlist!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.addFavorite = function(){

var data = $scope.dfb;
	AuthenticationService.addFavoriteMovie(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Added to favorite!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}



$scope.noFavorite = function(){

var data = $scope.favoriteStatus;
	AuthenticationService.noFavorite(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no favorite!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}

$scope.brokenLink = function(link){

	var id = $scope.online.user;
	AuthenticationService.reportMovie(link,id)
	.then(function(resp){
		var respuestaCompleta = JSON.parse(resp)
		alert(respuestaCompleta.data.message);

	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.addLink = function(){

	var dataLink = $scope.online;
	AuthenticationService.addLinksMovie(dataLink)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
		if(respuestaCompleta.error == false){
			alert("Link added successfully.")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}

$scope.addLinkDownload = function(){

	var dataLinkDownload = $scope.download;
	AuthenticationService.addLinksDownloadMovie(dataLinkDownload)
	.then(function(resp){
				var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Download Link added successfully.")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})
}


$scope.addIframe = function(){

var dataLinkDownload = $scope.iframe;
	AuthenticationService.addIframeMovie(dataLinkDownload)
	.then(function(resp){
				var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("iFrame added successfully.")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.delete = function(data){

		var data = data;
	AuthenticationService.deleteMovie(data)
	.then(function(resp){
			
			var respuestaCompleta = JSON.parse(resp);
			console.log(respuestaCompleta)
			if(respuestaCompleta.data.error == false){
			alert("Movie deleted successfully.")
			$window.location = 'http://45.55.62.152/new-movies';
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}

$scope.changeView = function(){

	$scope.showInput = !$scope.showInput;

}

$scope.changeDownload= function(){

	$scope.showInputD = !$scope.showInputD;

}

$scope.update = function(id,num,url,server,lang,quality){

var data = {
	id: id,
	url: url,
	server: server,
	quality: quality,
	lang: lang,
}





AuthenticationService.updateStreamMovie(data)
.then(function(resp){
	var respuestaCompleta = JSON.parse(resp);
	if(respuestaCompleta.data.error == false){
		alert("Link Updated successfully");
	}
	console.log(respuestaCompleta);
})
.catch(function(err){
	console.log(err);
})

}

$scope.deleteLink = function(){

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

$scope.updateDownload = function(id,num,url,server,lang,quality){

var data = {
	id: id,
	url: url,
	server: server,
	quality: quality,
	lang: lang,
}





AuthenticationService.updateDownloadMovie(data)
.then(function(resp){
	var respuestaCompleta = JSON.parse(resp);
	if(respuestaCompleta.data.error == false){
		alert("Link Updated successfully");
	}
	console.log(respuestaCompleta);
})
.catch(function(err){
	console.log(err);
})
}


}else{
			AuthenticationService.movieCheckX($scope.slug)
			.then(function(data){
			$scope.data = data[0];
			console.log(data);
			$scope.myLink =  $sce.trustAsResourceUrl($scope.data.iframe)
			$scope.checkStatus = 0;
			$scope.favoriteStatus = 0;
			$scope.playlistStatus = 0;
		$rootScope.load = true;
		}).catch(function(err){
				console.log(err)
			})
}
}





serieData.$inject = ['$rootScope','$http','$location', '$scope', 'AuthenticationService', '$cookieStore','$sce', '$window'];
function serieData($rootScope,$http,$location,$scope, AuthenticationService, $cookieStore,$sce,$window){

    var url = $location.$$absUrl;
	var distancia = url.length;
	var posicionSerie = url.indexOf('serie');
	var urlCortada = url.slice(posicionSerie+5,distancia)
	var arrayElements = urlCortada.split('/')
	var slug = arrayElements[1];
	var seasonTemporal = arrayElements[2];
	var capTemporal = arrayElements[3];
	var posicionSeasonTermina =  seasonTemporal.length;
	var posicionSeasonEmpieza  = seasonTemporal.indexOf('-');
	var posicionCapTermina =  capTemporal.length;
	var posicionCapEmpieza  = capTemporal.indexOf('-');
	var season = seasonTemporal.slice(posicionSeasonEmpieza+1,posicionSeasonTermina);
	$scope.seasonx = season;
	var cap = capTemporal.slice(posicionCapEmpieza+1,posicionCapTermina);
	var data = {
		slug: slug,
		season: season,
		episode: cap
	}
	$scope.slug = data.slug;

			$scope.dfb = {}
			$scope.iframe = {}
	var episodioId;

if($cookieStore.get('globals') != null){



		$scope.level = $cookieStore.get('globals').currentUser.level;
		
	$scope.online = {
		user: $cookieStore.get('globals').currentUser.id,
	};

	$scope.download = {
		user: $cookieStore.get('globals').currentUser.id,
	};

		$scope.idArray = [];
				$scope.idArrayM = [];

		$scope.dfb.user = $cookieStore.get('globals').currentUser.id;


			AuthenticationService.episodeCheck(data)
	.then(function(data){

		$scope.data = data;
		 $scope.myLink =  $sce.trustAsResourceUrl(data.iframe)
		console.log(data);
		$scope.dfb.episode = data.id;
		$scope.online.episode = data.id
		$scope.download.episode = data.id
		$scope.iframe.episode = data.id;
	
		console.log($rootScope.allLoaded)

AuthenticationService.checkWatched($scope.dfb)
	.then(function(data){

		$scope.checkStatus = data;
		console.log("stats",$scope.checkStatus)

		
	})
	.catch(function(err){
		console.log(err)
	})


AuthenticationService.checkFavorite($scope.dfb)
	.then(function(data){

		$scope.favoriteStatus = data;
		console.log("favoriteStatus",$scope.favoriteStatus)

		
	})
	.catch(function(err){
		console.log(err)
	})



	AuthenticationService.checkPlaylist($scope.dfb)
	.then(function(data){

		$scope.playlistStatus = data;
		console.log("playlistStatus",$scope.playlistStatus)
		$rootScope.load = true;
		
	})
	.catch(function(err){
		console.log(err)
	})




		
	})
	.catch(function(err){
		console.log(err)
	})




$scope.addIf = function(){
console.log('iframe');
var dataLinkDownload = $scope.iframe;
	AuthenticationService.addIframe(dataLinkDownload)
	.then(function(resp){
				var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
			if(respuestaCompleta.error == false){
			alert("iFrame added successfully.")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}

	$scope.addLink = function(){

	var dataLink = $scope.online;
	AuthenticationService.addLinks(dataLink)
	.then(function(resp){
				var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
		if(respuestaCompleta.error == false){
			alert("Link added successfully.")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}

$scope.addLinkDownload = function(){

	var dataLinkDownload = $scope.download;
	AuthenticationService.addLinksDownload(dataLinkDownload)
	.then(function(resp){
			var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
			if(respuestaCompleta.error == false){
			alert("Download Link added successfully.")
		}
		console.log(respuestaCompleta);
	})
	.catch(function(err){
		console.log("err"+err);
	})
}



$scope.deleteLink = function(){

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

$scope.addFavorite = function(){

var data = $scope.dfb;
	AuthenticationService.addFavorite(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Added to favorite!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}

$scope.brokenLink = function(link){

	var id = $scope.online.user;
	AuthenticationService.reportEpisode(link,id)
	.then(function(resp){
		var respuestaCompleta = JSON.parse(resp)
		alert(respuestaCompleta.data.message);

	})
	.catch(function(err){
		console.log("err"+err);
	})

}

$scope.noFavorite = function(){

var data = $scope.favoriteStatus;
	AuthenticationService.noFavorite(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no favorite!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.addPlaylist = function(){

var data = $scope.dfb;
	AuthenticationService.addPlaylist(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Added to playlist!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.noPlaylist = function(){

var data = $scope.playlistStatus;
	AuthenticationService.noPlaylist(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no playlist!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}





$scope.addWatched = function(){

var data = $scope.dfb;
	AuthenticationService.addWatched(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Marked as watched!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.noWatch = function(){

var data = $scope.checkStatus;
	AuthenticationService.noWatch(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no watch!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.delete = function(data){

		var data = data;
	AuthenticationService.deleteEpisode(data)
	.then(function(resp){
			
			var respuestaCompleta = JSON.parse(resp);
			console.log(respuestaCompleta)
			if(respuestaCompleta.data.error == false){
			alert("Episode  successfully deleted.")
			$window.location = 'http://45.55.62.152/new-movies';
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.changeView = function(){

	$scope.showInput = !$scope.showInput;

}

$scope.changeDownload= function(){

	$scope.showInputD = !$scope.showInputD;

}

$scope.update = function(id,num,url,server,lang,quality){

var data = {
	id: id,
	url: url,
	server: server,
	quality: quality,
	lang: lang,
}





AuthenticationService.updateStreamCap(data)
.then(function(resp){
	var respuestaCompleta = JSON.parse(resp);
	if(respuestaCompleta.data.error == false){
		alert("Link Updated successfully");
	}
	console.log(respuestaCompleta);
})
.catch(function(err){
	console.log(err);
})
}



$scope.updateDownload = function(id,num,url,server,lang,quality){

var data = {
	id: id,
	url: url,
	server: server,
	quality: quality,
	lang: lang,
}





AuthenticationService.updateDownloadCap(data)
.then(function(resp){
	var respuestaCompleta = JSON.parse(resp);
	if(respuestaCompleta.data.error == false){
		alert("Link Updated successfully");
	}
	console.log(respuestaCompleta);
})
.catch(function(err){
	console.log(err);
})
}



}else{


	AuthenticationService.episodeCheck(data)
	.then(function(data){

		$scope.data = data;
			$scope.myLink =  $sce.trustAsResourceUrl($scope.data.iframe)
			$scope.checkStatus = 0;
			$scope.favoriteStatus = 0;
			$scope.playlistStatus = 0;
		$rootScope.load = true;
})
	.catch(function(err){
		console.log(err)
	})


}







}






tv.$inject = ['$rootScope','$http','$location','$scope', 'AuthenticationService', '$cookieStore','$sce'];
function tv($rootScope, $http,$location,$scope,AuthenticationService, $cookieStore,$sce){
$scope.tv;


  $scope.users = [];
    $scope.totalUsers = 0;
    $scope.usersPerPage = 37; // this should match however many results your API puts on one page
    $scope.letter;
    getResultsPage(1);

    $scope.pagination = {
        current: 1
    };

    $scope.pageChanged = function(newPage) {
        getResultsPage(newPage);
    };

    $scope.changeLetter = function(letter) {
        $scope.letter = letter;
        console.log($scope.letter);
        getResultsPage(1);
    };

      $scope.all = function() {
        $scope.letter = null;
        getResultsPage(1);
    };

function getResultsPage(pageNumber) {
	var page = pageNumber;
	console.log("la pagina"+page)
	if($scope.letter == null){


	
AuthenticationService.tv(page)
.then(function(resp){

	var respuestaCompleta = JSON.parse(resp);
	$scope.tv = respuestaCompleta.data.series;
	console.log($scope.tv)
	$scope.totalUsers = respuestaCompleta.data.elements;
	$rootScope.load = true;
})
.catch(function(err){
	console.log('err'+err)
})
}else{

	AuthenticationService.tvLetter(page,$scope.letter)
.then(function(resp){

	var respuestaCompleta = JSON.parse(resp);
	$scope.tv = respuestaCompleta.data.series;
	console.log($scope.tv)
	$scope.totalUsers = respuestaCompleta.data.elements;
$rootScope.load = true;
})
.catch(function(err){
	console.log('err'+err)
})

}

 }






}


serieBase.$inject = ['$rootScope','$http','$location', '$scope', 'AuthenticationService', '$cookieStore','$sce', '$window'];
function serieBase($rootScope,$http,$location,$scope, AuthenticationService, $cookieStore,$sce,$window){

 	var url = $location.$$absUrl;
	var distancia = url.length;
	var posicionSerie = url.indexOf('serie');
	var posicionOnline = url.indexOf('online');
	var urlCortada = url.slice(posicionSerie+6,posicionOnline-1)
	$scope.slug = urlCortada;
	$scope.dfb = {};

	if($cookieStore.get('globals') != null){

	$scope.level = $cookieStore.get('globals').currentUser.level;
	AuthenticationService.serieDefinitiva(urlCortada)
	.then(function(response){
		var respuestaDefinitiva = JSON.parse(response);
		$scope.info = respuestaDefinitiva.data[0];
		var currentUser = $cookieStore.get('globals');
		$scope.dfb.user = currentUser.currentUser.id;
		$scope.dfb.id = $scope.info.id;



		AuthenticationService.checkWatchedShow($scope.dfb)
	.then(function(data){

		$scope.checkStatus = data;
		console.log("stats",$scope.checkStatus)

		
	})
	.catch(function(err){
		console.log(err)
	})


AuthenticationService.checkFavoriteShow($scope.dfb)
	.then(function(data){

		$scope.favoriteStatus = data;
		console.log("favoriteStatus",$scope.favoriteStatus)

		
	})
	.catch(function(err){
		console.log(err)
	})



	AuthenticationService.checkPlaylistShow($scope.dfb)
	.then(function(data){

		$scope.playlistStatus = data;
		console.log("playlistStatus",$scope.playlistStatus)
		$rootScope.load = true;
		
	})
	.catch(function(err){
		console.log(err)
	})





	})
	.catch(function(err){
		console.log(err);
	})


$scope.addFavorite = function(){

var data = $scope.dfb;
	AuthenticationService.addFavoriteShow(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Added to favorite!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.noFavorite = function(){

var data = $scope.favoriteStatus;
	AuthenticationService.noFavoriteShow(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no favorite!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.addPlaylist = function(){

var data = $scope.dfb;
	AuthenticationService.addPlaylistShow(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Added to playlist!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.noPlaylist = function(){

var data = $scope.playlistStatus;
	AuthenticationService.noPlaylistShow(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no playlist!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}





$scope.addWatched = function(){

var data = $scope.dfb;
	AuthenticationService.addWatchedShow(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("Marked as watched!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.noWatch = function(){

var data = $scope.checkStatus;
	AuthenticationService.noWatchShow(data)
	.then(function(resp){
		var response = JSON.stringify(resp);
		var respuestaCompleta = JSON.parse(response)
		console.log(respuestaCompleta);
			if(respuestaCompleta.error == false){
			alert("no watch!")
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


$scope.delete = function(data){

		var data = data;
	AuthenticationService.deleteSeason(data)
	.then(function(resp){
			
			var respuestaCompleta = JSON.parse(resp);
			console.log(respuestaCompleta)
			if(respuestaCompleta.data.error == false){
			alert("Season  successfully deleted.")
			//$window.location = 'http://45.55.62.152/new-movies';
		}
	})
	.catch(function(err){
		console.log("err"+err);
	})

}


	}else{

			AuthenticationService.serieDefinitiva(urlCortada)
	.then(function(response){
		var respuestaDefinitiva = JSON.parse(response);
		$scope.info = respuestaDefinitiva.data[0];
			$scope.checkStatus = true;
			$scope.favoriteStatus = true;
			$scope.playlistStatus = true;
			$rootScope.load = true;


	})
	.catch(function(err){
		console.log(err)
	})
	

}
}


page.$inject = ['$rootScope','$http','$location','$scope', 'AuthenticationService', '$cookieStore','$sce'];
function page($rootScope, $http,$location,$scope,AuthenticationService, $cookieStore,$sce){

 var url = $location.$$absUrl;
	var distancia = url.length;
	var posicionSerie = url.indexOf('page');
	var urlCortada = url.slice(posicionSerie+5,distancia)
	console.log(urlCortada);
	AuthenticationService.page(urlCortada)
	.then(function(resp){
		var respuestaCompleta = JSON.parse(resp);
		$scope.content = $sce.trustAsHtml(respuestaCompleta.data.content);
		$rootScope.load = true;
		console.log($scope.content)
	})
	.catch(function(err){
		console.log(err);
	})
 }
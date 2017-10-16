(function(){

angular.module('services')
.factory('AuthenticationService',AuthenticationService);

AuthenticationService.$inject  = ['$http','$cookieStore','$rootScope', '$location', '$q', '$window']

function AuthenticationService($http, $cookieStore, $rootScope, $location, $q, $window){

	var servicio = {};



	servicio.setCookie = function(username, level, aid){

		$rootScope.globals = {
			currentUser : {
				username: username,
				level: level,
				id: aid
			}
		}

		$cookieStore.put('globals', $rootScope.globals)
	}



	servicio.deleteCookies = function(){
		$rootScope.globals = {};
		$cookieStore.remove('globals');
		$rootScope.globals.status = 'off';
		$window.location.reload();
		

	}


	servicio.registro = function(username, password, email){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/users.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({register:'1',username:username, password: password, type:'1', email:email})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


		servicio.page = function(slug){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/pages.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({pages:'1',slug:slug})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}

		servicio.addEpisodes = function(season,episodes){

						for(var x = 0; x<episodes.length; x++){
			var curdate = new Date(episodes[x].dbdate);
			var year =  curdate.getFullYear();
			var month =  curdate.getMonth() + 1;
			var day =    curdate.getDate();
			var fullDate = year + '-' + month + '-' + day;
			delete episodes[x].dbdate;
			episodes[x].dbdate = fullDate;

					var curdater = new Date(episodes[x].release);
			var yearr =  curdater.getFullYear();
			var monthr =  curdater.getMonth() + 1;
			var dayr =    curdater.getDate();
			var fullDater = yearr + '-' + monthr + '-' + dayr;
			delete episodes[x].release;
			episodes[x].release = fullDater;
		}


		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({update:'1',season:season, episodes: episodes})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


		servicio.reportEpisode = function(link,id){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({report:'1',link:link, type: 'episode', user:id})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}

			servicio.reportMovie = function(link,id){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({report:'1',link:link, type: 'movie', user:id})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}

				servicio.reported = function(link,id){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({report:'1'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


	servicio.listSerie = function(link,id){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1',series:'1',list:'1'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}



	servicio.listSeason = function(id){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1',series:'1',list:'1',id_serie: id})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}
http://45.55.62.152/New/proccess/classes/tv.class.php?show=1&series=1&list=1&id_season=58

		servicio.addSeasonNew = function(id,season){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({register:'4',id_serie:id ,season: season})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


			servicio.checkSeasonEpisodes = function(ide){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1',series:'1' ,list:'1', id_season: ide})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


				servicio.number = function(){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({counts:'1'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}



		servicio.allPages = function(username, password, email){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/pages.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({pages:'1',getall:'1'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


			servicio.deleteMovie = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({delete:'2',id:data, type: 'movie', action: 'delete'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


				servicio.deleteEpisode = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({delete:'2',id:data, type: 'episode', action: 'delete'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


					servicio.deleteSeason = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({delete:'2',id:data, type: 'season', action: 'delete'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


						servicio.updateStreamMovie = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({change:'1',id:data.id, url: data.url, server: data.server, quality:data.quality, type:'link', lang: data.lang})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


							servicio.updateDownloadMovie = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({change:'1',id:data.id, url: data.url, server: data.server, quality:data.quality, type:'download', lang: data.lang})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


						servicio.updateStreamCap = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({change:'1',id:data.id, url: data.url, server: data.server, quality:data.quality, type:'link', lang: data.lang})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


							servicio.updateDownloadCap = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({change:'1',id:data.id, url: data.url, server: data.server, quality:data.quality, type:'download', lang: data.lang})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


		servicio.linksResume = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1',links:'1', user:data})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}




		servicio.resumeWatched = function(user){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({watched:'3',user: user})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}



			servicio.resumePlaylist = function(user){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({playlist:'1', getall:'1', user: user})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


				servicio.resumeFavorite = function(user){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({favorite:'1', getall:'1', user: user})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}





		servicio.tv = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1', tvlisting:'1', page: data})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}

			servicio.tvLetter = function(page,letter){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1', tvlisting:'1', page: page, index: letter})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


	servicio.serieDefinitiva = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1',slug:data})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


	servicio.login = function(username,password){
		return $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/users.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({login:'1',username:username, password: password})

	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err))
	})
	}



		servicio.saveMovie = function(data){

		var defered = $q.defer();
		var promise = defered.promise;
		$http({
			method: 'post',
			url:'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({register: '1', poster: data['poster_path'], title: data['original_title'], overview: data.overview,
							id: data.id, quality: data.quality, genres: data.genres, rating: data.rating, creation: data.creation, meta_title: data['meta_title'], meta_description: data['meta_description'],meta_tags: data['meta_tags'] })

		})
		.success(function(response){
			defered.resolve(response)
		})
		.error(function(err){
			defered.reject(err)
		})

		return promise;

	}





			servicio.saveSerie = function(data){

		var defered = $q.defer();
		var promise = defered.promise;
		$http({
			method: 'post',
			url:'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({register: '1', poster: data['poster_path'], title: data['original_name'], overview: data.overview,
							id: data.id, genres: data.genres, creation: data.creation, rating: data.rating, meta_title: data['meta_title'], meta_description: data['meta_description'],meta_tags: data['meta_tags']})

		})
		.success(function(response){
			defered.resolve(response)
		})
		.error(function(err){
			defered.reject(err)
		})

		return promise;

	}


	servicio.serieCheck = function(code){


		var defered = $q.defer();
		var promise = defered.promise;

		$http({
			method: 'post',
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({search: '1', code: code})
		})
		.success(function(response){
			defered.resolve(response)
		})
		.error(function(err){
			defered.reject(err)
		})

		return promise;
	}



	servicio.movieCheck = function(code){


		var defered = $q.defer();
		var promise = defered.promise;

		$http({
			method: 'post',
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({search: '1', code: code})
		})
		.success(function(response){
			defered.resolve(response)
		})
		.error(function(err){
			defered.reject(err)
		})

		return promise;
	}




	servicio.getAllMovies = function(){
		var defered = $q.defer();
		var promise = defered.promise;

		$http.get('http://45.55.62.152/New/proccess/classes/movies.class.php?show=1')
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




	servicio.getAllSeries = function(){
		var defered = $q.defer();
		var promise = defered.promise;

		$http.get('http://45.55.62.152/New/proccess/classes/tv.class.php?show=1')
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


	servicio.getAllInfoUser = function(ide){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/users.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({getuser: '1', id: ide})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}



		servicio.saveAllInfoUser = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/users.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({update: '2', id : data.id,  
										type:  data.type,
										password: data.pass,
										username: data.username,
										email: data.email,
										nickname: data.nickname,
										first_name: data.fname,
										last_name:  data.lname,
										biography: 	data.bio,
										webpage:    data.website,
										facebook:   data.facebook,
										twitter: 	data.twitter,
										gplus: 		data.gp})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


			servicio.movieCheckX = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({show: '1', slug:data})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


			servicio.seasonCheck = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({search: '1', serie:data.id, season:data.season})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}

				servicio.addSeasonAndEpisode = function(data){


			for(var x = 0; x<data.episodes.length; x++){
			var curdate = new Date(data.episodes[x].dbdate);
			var year =  curdate.getFullYear();
			var month =  curdate.getMonth() + 1;
			var day =    curdate.getDate();
			var fullDate = year + '-' + month + '-' + day;
			delete data.episodes[x].dbdate;
			data.episodes[x].registeredat = fullDate;
		}


		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({register: '2', serie:data.id, season:data.season,episodes: data.episodes})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}





			servicio.episodeCheck = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({show: '1', slug:data.slug, season:data.season, episode: data.episode})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


				servicio.addLinks = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({register: '3', type: 'link', episode:data.episode, user:data.user, url: data.link, lang: data.language, server: data.server, quality: data.quality})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


		servicio.addLinksMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({register: '2', type: 'link', movie:data.movie, user:data.user, url: data.link, lang: data.language, server: data.server, quality: data.quality})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}



				servicio.addLinksDownload = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({register: '3', type: 'download', episode:data.episode, user:data.user, url: data.link, lang: data.language, server: data.server, quality: data.quality})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


					servicio.addLinksDownloadMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({register: '2', type: 'download', movie:data.movie, user:data.user, url: data.link, lang: data.language, server: data.server, quality: data.quality})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




				servicio.addIframe = function(data){

		console.log(data);
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({iframe: '1', url:data.link, episode:data.episode})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}





				servicio.addIframeMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({iframe: '1', url:data.link, movie:data.movie})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}






				servicio.addFavorite = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', mark: '1', user:data.user, multimedia:data.episode, type:'episode'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


					servicio.addFavoriteMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', mark: '1', user:data.user, multimedia:data.movie, type:'movie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


					servicio.checkFavorite = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', check: '1', user:data.user, multimedia:data.episode, type:'episode'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


						servicio.checkFavoriteMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', check: '1', user:data.user, multimedia:data.movie, type:'movie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




					servicio.noFavorite = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


						servicio.noFavoriteMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}



		servicio.addPlaylist = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', mark: '1', user:data.user, multimedia:data.episode, type:'episode'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




		servicio.addPlaylistSeriex = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', mark: '1', user:data.user, multimedia:data.episode, type:'episode'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}



				servicio.addPlaylistMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', mark: '1', user:data.user, multimedia:data.movie, type:'movie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




					servicio.checkPlaylist = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', check: '1', user:data.user, multimedia:data.episode, type:'episode'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


						servicio.checkPlaylistMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', check: '1', user:data.user, multimedia:data.movie, type:'movie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}



					servicio.noPlaylist = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}

						servicio.noPlaylistMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}








		servicio.checkWatched = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '1', check: '1', user:data.user, episode:data.episode})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




			servicio.checkWatchedMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '1', check: '1', user:data.user, movie:data.movie})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}



servicio.addWatchedMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '1', mark: '1', user:data.user, movie:data.movie})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}









				servicio.addWatched = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '1', mark: '1', user:data.user, episode:data.episode})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




				servicio.noWatchMovie = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/movies.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


				servicio.noWatch = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}




				servicio.playlistPanel = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', getall: '1', user:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}



				servicio.watchedPanel = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '1', getall: '1', user:data})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


/*Aqui son las de series normales*/



				servicio.addFavoriteShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', mark: '1', user:data.user, multimedia:data.id, type:'serie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}


					servicio.checkFavoriteShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', check: '1', user:data.user, multimedia:data.id, type:'serie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}
		servicio.noFavoriteShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({favorite: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}
		servicio.addPlaylistShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', mark: '1', user:data.user, multimedia:data.id, type:'serie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}

		servicio.checkPlaylistShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', check: '1', user:data.user, multimedia:data.id, type:'serie'})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}
					servicio.noPlaylistShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({playlist: '1', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}
		servicio.checkWatchedShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '2', check: '1', user:data.user, serie:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}
				servicio.addWatchedShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '2', mark: '1', user:data.user, serie:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}

				servicio.noWatchShow = function(data){
		var defered = $q.defer();
		var promise  =  defered.promise;
		$http({
			method: "post",
			url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({watched: '2', unmark: '1', id:data.id})
		})
		.success(function(response){

			defered.resolve(response);
		})
		.error(function(err){

			defered.reject(err);

		})
		return promise;
	}










		servicio.deleteLinks = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({delete:'1',id:data, type:'episode',action:'delete'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}




				servicio.activate = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({delete:'1',id:data, type:'episode',action:'activate'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}

			servicio.activateM = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({delete:'1',id:data, type:'movie',action:'activate'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


		servicio.deleteLinksM = function(data){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({delete:'1',id:data, type:'movie',action:'delete'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}


			servicio.linksForModerate = function(){
		return  $http({
		method: 'POST',
		url: 'http://45.55.62.152/New/proccess/classes/tv.class.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    	data: $.param({show:'1',links:'1', waiting:'1'})
	})
	.then(function(data){
		return (JSON.stringify(data));
	})
	.catch(function(err){
		return (JSON.stringify(err));
	})
	}





return servicio;
}


})();
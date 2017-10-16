
angular.module('AddPage',['ngCookies','textAngular'])
		.controller('index',index)
		.controller('addPage',addPage)
		.run(run)


run.$inject = ['$rootScope','$location','$cookieStore'];
function run($rootScope,$location,$cookieStore){

	$rootScope.globals = $cookieStore.get('globals') || {status: 'off'}; 


}


index.$inject  = [ '$rootScope', '$scope' , '$cookieStore'];
function index($rootScope, $scope,  $cookieStore){

	$scope.watching = $rootScope;
	console.log("Entre")
	console.log($rootScope.globals)
	if($rootScope.globals.status == 'off'){

		$scope.login = false;
	}else{

		$scope.login = true;
	}

}

addPage.$inject  = [ '$rootScope', '$scope' , '$cookieStore','$http'];
function addPage($rootScope, $scope,  $cookieStore,$http){


		var id = $cookieStore.get('globals');
		$scope.x = $cookieStore.get('globals').currentUser.level;

		if(!id || id.currentUser.level == '1' || id.currentUser.level == '2' || id.currentUser.level == '3'){

		$window.location = 'http://localhost/movies';
	}

	$scope.data = {};

	$scope.addPage = function(){

		if($scope.data.title == null || $scope.data.content == null || $scope.data.metad == null || $scope.data.metat == null){
			alert("Error, you have to fill all the input.")
		}


		var slug = $scope.data.slug.replace(/\s+/g, '-').toLowerCase();
		console.log(slug)

		$http({
			method: 'post',
			url: 'http://45.55.62.152/New/proccess/classes/pages.class.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: $.param({create: '1', meta_title: $scope.data.metat, meta_description: $scope.data.metad, content: $scope.data.content, title: $scope.data.title,slug:slug})
		})
		.success(function(response){
		alert(response.message)
		})
		.error(function(err){
			console.log(err);
		})




	}

}



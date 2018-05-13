<?php
$usesDatatables = 1; 
include('header.php'); 
?>

<div ng-cloak ng-app='jazzbooks' ng-controller='booklist'>

<div class='alert alert-danger' ng-show='errorMsg'>{{errorMsg}}</div>

<p><input type='checkbox' ng-model='showCovers' ng-change='showCoversChanged()'>Show Covers</p>

<table id='table' class='table table-hover' datatable='ng' dt-options='dtOptions' dt-column-defs='dtColumnDefs'>
	<thead>
	<tr>
		<th>Cover</th>
		<th>Title</th>
		<th>Author</th>
		<th>ISBN</th>
		<th>Published</th>
		<th>Interested</th>
	</tr>
	</thead>
	
	<tbody>
	<tr ng-repeat='b in books'>
		<td>
			<img src='{{b.cover}}' height='100'/>
		</td>
		<td><span ng-bind-html="b.title|trusted"/></td>
		<td>{{b.author_fl}}</td>
		<td>{{b.ISBN}}</td>
		<td>{{b.publicationdate}}</td>
		<td><input type='checkbox' ng-model='b.interested'></td>
	</tr>
	</tbody>
	
</table>

<!-- <pre ng-show='response'>{{response|json}}</pre> -->

</div>

<script>
var app = angular.module('jazzbooks', ['datatables']);

app.filter('trusted', ['$sce', function($sce) {
	var div = document.createElement('div');
	return function(text) {
		div.innerHTML = text;
		return $sce.trustAsHtml(div.textContent);
	};
}]);

app.controller('booklist', function($scope, $http) {
		
	$scope.dtOptions = {
		'language': {
			'emptyTable': 'Retrieving book information...'
		},
		'order': [[ 1, 'asc' ]] 
	};
        
	$scope.dtColumnDefs = [
		{ targets: [0], visible: false, orderable: false },
		{ targets: [5], orderable: false }
	];
	
	//http://www.librarything.com/api_getdata.php?userid=jazzleeds&key=2837672999&booksort=title&max=350&showTags=1&responseType=json
	$http.get('https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20json%20where%20url%20%3D%20%22http%3A%2F%2Fwww.librarything.com%2Fapi_getdata.php%3Fuserid%3Djazzleeds%26key%3D2837672999%26booksort%3Dtitle%26max%3D350%26showTags%3D1%26responseType%3Djson%22&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys')
		.then(function(response) {
			$scope.errorMsg = null;
			$scope.response = null;
			$scope.books = response.data.query.results.json.books;
		}, function(response) {
			$scope.errorMsg = response;
		});
		
	$scope.showCoversChanged = function() {
		$scope.dtColumnDefs[0].visible = $scope.showCovers;
	};
	
});

</script>

<?php include('footer.php'); ?>
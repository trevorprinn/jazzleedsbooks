<?php
$usesDatatables = 1; 
include('header.php'); 
?>

<div ng-cloak ng-app='jazzbooks' ng-controller='interested'>

	<div class='alert alert-danger' ng-show='errorMsg'>{{errorMsg}}</div>
	
	<a href="booklist.php" class="btn btn-default">Return to Book List</a>

	<table id='table' class='table table-hover' datatable='ng' dt-options='dtOptions' dt-column-defs='dtColumnDefs'>
		<thead>
		<tr>
			<th>Cover</th>
			<th>Title</th>
			<th>Author</th>
			<th>ISBN</th>
			<th>Published</th>
			<th></th>
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
			<td><button class="btn btn-default" ng-click="removeInterest($index)">Remove</button></td>
		</tr>
		</tbody>
		
	</table>

	<a href="booklist.php" class="btn btn-default">Return to Book List</a>

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

app.controller('interested', function($scope, $http) {

	$scope.dtOptions = {
		'language': {
			'emptyTable': 'No books marked for interest...'
		},
		'order': [[ 1, 'asc' ]] 
	};
        
	$scope.dtColumnDefs = [
		{ targets: [0], orderable: false },
		{ targets: [5], orderable: false }
	];

	
	$http.post('getInterested.php')
		.then(function(response) {
			$scope.books = response.data;
		});
		
	$scope.removeInterest = function(ix) {
		$scope.books.splice(ix, 1);
		$http.post('saveInterested.php', books);
	};
});

</script>

<?php include('footer.php'); ?>
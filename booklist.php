<?php
$usesDatatables = 1; 
include('header.php'); 
?>

<div ng-cloak ng-app='jazzbooks' ng-controller='booklist' class='body-content'>

<div class='alert alert-danger' ng-show='errorMsg'>{{errorMsg}}</div>

<form class="form-horizontal">

	<div class="form-group">
		<label class="col-md-2 control-label">Show Covers</label>
		<div class="col-md-10">
			<input type="checkbox" class="form-control checkbox-medium" ng-model="showCovers" ng-change='showCoversChanged()'/>
		</div>
	</div>
	
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
			<td>
				<span ng-show='b.ISBN_cleaned'><a href="http://www.librarything.com/isbn/{{b.ISBN_cleaned}}" target="_blank"><span ng-bind-html="b.title|trusted"/></a></span>
				<span ng-hide='b.ISBN_cleaned' ng-bind-html="b.title|trusted"/>
			</td>
			<td>{{b.author_fl}}</td>
			<td>{{b.ISBN}}</td>
			<td>{{b.publicationdate}}</td>
			<td><input type='checkbox' ng-model='b.interested' ng-change='interestedChanged()'></td>
		</tr>
		</tbody>
		
	</table>
	
	<div class="form-group">
		<div class="col-md-12">
			<a href="interested.php" class="btn btn-primary">Interested List</a>
		</div>
	</div>

</form>

<pre ng-show='response'>{{response|json}}</pre>

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
	
	// url for the book list is http://www.librarything.com/api_getdata.php?userid=jazzleeds&key=2837672999&booksort=title&max=350&showTags=1&responseType=json&showCollections=1 	
	$http.post('getbooks.php')
		.then(function(response) {
			$scope.errorMsg = null;
			$scope.response = null;
			var books = response.data.books;
			$.each(books, function(bookKey, book) {
				$.each(book.collections, function(collKey, coll) {
					var del = false;
					if (coll == 'Sold') {
						del = true;
					}
					if (del) {
						delete books[bookKey];
					}
				});
			});
			$scope.books = books;
			applyInterested();
		}, function(response) {
			$scope.errorMsg = response;
		});
		
	$scope.showCoversChanged = function() {
		$scope.dtColumnDefs[0].visible = $scope.showCovers;
	};
	
	applyInterested = function() {
		$scope.errorMsg = null;
		$http.get('getInterested.php')
			.then(function(response) {
				var ints = response.data;
				for (var i = 0; i < ints.length; i++) {
					var book_id = ints[i].book_id;
					for (var key in $scope.books) {
						if ($scope.books.hasOwnProperty(key)) {
							var book = $scope.books[key];
							if (book.book_id == book_id) {
								book.interested = true;
								break;
							}
						}					
					}
				}
			},
			function(response) {
				$scope.errorMsg = response;
			});
	};
	
	$scope.interestedChanged = function() {
		var ints = [];
		for (var key in $scope.books) {
			if ($scope.books.hasOwnProperty(key)) {
				var book = $scope.books[key];
				if (book.interested) ints.push(book);			
			}
		}
		$scope.errorMsg = null;
		$http.post('saveInterested.php', ints)
			.then(function(response) {},
			function(response) {
				$scope.errorMsg = response;
			});
	};
	
});

</script>

<?php include('footer.php'); ?>
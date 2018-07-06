<?php
$usesDatatables = 1; 
include('header.php'); 
?>

<div ng-cloak ng-app='jazzbooks' ng-controller='interested' class='body-content'>

	<div class='alert alert-danger' ng-show='errorMsg'>{{errorMsg}}</div>
	
	<a href="booklist.php" class="btn btn-default">Return to Book List</a>
	<p>&nbsp;</p>
	
	<form ng-submit='sendEmail()' class="form-horizontal">

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title accordian-toggle" data-toggle="collapse" data-target="#books">Interesting Books</h3>
			</div>
			<div id="books" class="panel-collapse collapse in">
				<div class="panel-body">
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
				</div>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title accordian-toggle" data-toggle="collapse" data-target="#gigs">Which gigs should we bring the books along to?</h3>
			</div>		
			<div id="gigs" class="panel-collapse collapse in">
				<div class="panel-body">
					<table class='table' datatable='ng' dt-options='dtGigOptions' dt-column-defs='dtGigColumns'>
						<thead>
							<tr>
								<th></th>
								<th>Date</th>
								<th>>DateOrder</th>
								<th>Artists</th>
								<th>Venue</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-repeat='g in gigs'>
								<td><input type='checkbox' ng-model='g.Going'></td>
								<td>{{formatDate(g.Date, 'ddd DD/MM/YYYY')}}</td>
								<td>{{formatDate(g.Date, 'YYMMDDDD')}}</td>
								<td>{{g.Band}}</td>
								<td>{{g.Venue}}</td>
							</tr>
						</tbody>
					
					</table>
				</div>
			</div>
		</div>
		
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title accordian-toggle" data-toggle="collapse" data-target="#details">Your details</h3>
			</div>
			<div id="details" class="panel-collapse collapse in">			
				<div class="panel-body">	
					<div class="form-group">
						<label for="name" class="col-md-2 control-label">Your Name</label>
						<div class="col-md-10">
							<input ng-model="name" type="text" class="form-control"/>
						</div>
					</div>
					
					<div class="form-group">
						<label for="email" class="col-md-2 control-label">Your Email address (optional)</label>
						<div class="col-md-10">
							<input ng-model="email" type="email" class="form-control"/>
						</div>
					</div>
					
					<div class="form-group">	
						<label for="notes" class="col-md-2 control-label">Notes</label>
						<div class="col-md-10">
							<textarea ng-model='notes' rows="5" cols="20" class="form-control nomaxwidth"></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row body-content">
			<div class="form-group">
				<div class="col-md-12">	
					<button type="submit" class="btn btn-primary" ng-disabled="!checkInfo()">Send Email</button>
					<a href="booklist.php" class="btn btn-default">Return to Book List</a>
				</div>
			</div>
			<div class="alert alert-info" ng-hide="checkInfo()">
				You must select some books, at least one gig and enter your name to send a request email.
			</div>
		</div>
	</form>
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

app.controller('interested', function($scope, $http, $window) {

	$scope.dtOptions = {
		'language': {
			'emptyTable': 'You haven\'t marked any books as interesting...'
		},
		'order': [[ 1, 'asc' ]] 
	};
        
	$scope.dtColumnDefs = [
		{ targets: [0, 5], orderable: false }
	];

	$scope.dtGigOptions = {
		'language': {
			'emptyTable': 'No gigs???'
		},
		'order': [[2, 'asc']]
	};

	$scope.dtGigColumns = [
		{ targets: [0, 1, 2, 3, 4], orderable: false },
		{ targets: [2], visible: false }
	];
	
	$http.post('getInterested.php')
		.then(function(response) {
			$scope.books = response.data;
		});
	$http.post('getGigList.php')
		.then(function(response) {
			var gigs = response.data;
			$scope.gigs = [];
			var now = moment();
			for (var i = 0; i < gigs.length; i++) {
				if (moment(gigs[i].Date).isAfter(now, 'day'))
					$scope.gigs.push(gigs[i]);
			}
		});
		
	$scope.removeInterest = function(ix) {
		$scope.books.splice(ix, 1);
		$http.post('saveInterested.php', books);
	};
	
	$scope.formatDate = function(d, f) {
		return moment(d).format(f);
	};
	
	getGoingGigs = function() {
		var gigs = [];
		for (var i = 0; i < $scope.gigs.length; i++) {
			if ($scope.gigs[i].Going) gigs.push($scope.gigs[i]);
		}
		return gigs;
	};
	
	$scope.sendEmail = function() {
		var gigs = getGoingGigs();
		var data = {
			'books': $scope.books,
			'gigs': gigs,
			'notes': $scope.notes,
			'email': $scope.email,
			'name': $scope.name
		};
		$http.post('SendEmail.php', data)
			.then(function(response) {
				$scope.response = response;
				$scope.error = null;
				if (response.data.success) {
					alert('Thank you. Your email has been sent.');
					$window.location.href = 'index.php';
				} else {
					$scope.errorMsg = response.data.error;
					alert(response.data.error);
				}
			});
	};	
	
	$scope.checkInfo = function() {
		return $scope.books.length > 0 && getGoingGigs().length > 0 && $scope.name;
	};
	
});

</script>

<?php include('footer.php'); ?>
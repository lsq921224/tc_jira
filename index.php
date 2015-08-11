<?php
	$username = 'jira_api';
	$password = '7c8bc1b4a0';
	$URL = 'http://jira.touchcommerce.com/rest/api/2/search';
	$URL_AUTO = 'http://jira.touchcommerce.com/rest/api/2/jql/autocompletedata/';
	//$password = 'T3sting123';
	//$URL = 'http://agvinfosec01.touchcommerce.com:8080/rest/api/2/search';
	$jql = !is_null($_GET["jql"]) && $_GET["jql"] != ''  ? $_GET["jql"] : 'assignee=mmcclarin';
	?>
	<script>
		console.log("<?php echo $jql;?>"); 
	</script>
	<?php
	$data = 'jql='.urlencode($jql).'&fields=created,resolutiondate,reporter,assignee,project,issuetype,status,resolution,timespent&maxResults=10000';
	//$data = 'jql=assignee=mmcclarin&fields=id&maxResults=10000';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_URL, $URL.'?'.$data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 500);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$results = curl_exec($ch);
	if($results === false)
	{
		echo 'Curl error: ' . curl_error($ch);
	}
	else
	{
		//echo urldecode($URL.'?'.$data);
	}
	curl_setopt($ch, CURLOPT_URL, $URL_AUTO);
	$auto = curl_exec($ch);
	curl_close($ch);
?>
<html>
<meta charset="utf-8">
<head>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="stylesheet" href="css/dc.css"/>
  <link rel="stylesheet" href="css/bootstrap.css"/>
  <link rel="stylesheet" href="css/daterangepicker.css"/>
  <script src="js/jquery-2.1.4.min.js"></script>
  <script src="js/jquery.ba-bbq.js"></script>
  <script src="js/typeahead.bundle.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/d3.v3.js"></script>
  <script src="js/crossfilter.js"></script>
  <script src="js/dc.js"></script>
  <script src="js/jquery.csv.js"></script>
  <script src="js/FileSaver.min.js"></script>
  <script src="js/json2csv.js"></script>

</head>

<body>
	<div id = "selections">
	<form id = "callapi" class="form-horizontal" method="GET" action="">
	<input type='text' class='ajax-typeahead form-control' id= "jql_query" style="width:400px;"/>
	<button type="submit" class="btn btn-default">Submit</button>
	</form>
	
<!--	
	<div id="export_drop" class="btn-default"><button type="button" class="btn dropdown-toggle" data-toggle="dropdown">Export</button><ul class="dropdown-menu">
	<li><a id = "s_json">JSON</a></li>
	<li><a id = "s_csv">CSV</a></li>
                  </ul>
				  <span class ="alert alert-info" id='message' style ="margin-left:4px;" hidden></span>
	</div>
-->
	<script>
	var fieldsNameJSON = <?php echo $auto; ?>;
	
	var fieldsNames = fieldsNameJSON['visibleFieldNames'];
	var name_operator = new Object();
	var name_value = new Object();
	var fieldsNamesDisplay = [];
	for (var i = 0; i < fieldsNames.length; i++)
	{
		name_operator[fieldsNames[i].displayName] = fieldsNames[i].operators;
		fieldsNamesDisplay.push(fieldsNames[i].displayName);
	}
	var functionNames = fieldsNameJSON['visibleFunctionNames'];
	var myVal = $('#jql_query').val();
	var operators = ['=','!=','~','<=','>=','>','<','!~','in','is','is not','not in','was','was not','was in','was not in','changed'];
	var operator = "";

	
	$('.ajax-typeahead').bind('typeahead:autocomplete', function(ev, suggestion) {
		 myVal = $('#jql_query').val();
	});
	
	$('.ajax-typeahead').bind('typeahead:change', function(ev) {
		for (var i = 0; i < operators.length; i ++)
		{
			operator = $('#jql_query').val().trim().endsWith(operators[i]) ? operators[i] : operator;
		}
		 myVal = $('#jql_query').val();
	});
	
	$('.ajax-typeahead').bind('typeahead:idle', function(ev) {
		 myVal = $('#jql_query').val();
	});
	
	$('.ajax-typeahead').bind('typeahead:active', function(ev) {
		 myVal = $('#jql_query').val();
	});
	
	$('.ajax-typeahead').bind('typeahead:render', function(ev, suggestions, flag, name) {
		for (var i = 0; i < operators.length; i ++)
		{
			operator = $('#jql_query').val().trim().endsWith(operators[i]) ? operators[i] : operator;
		}
		 myVal = $('#jql_query').val();
	});
	
	$('.ajax-typeahead').bind('typeahead:asyncrequest', function(ev, query, name) {
		 myVal = $('#jql_query').val();
	});
	
	
	$('.ajax-typeahead').bind('typeahead:select', function(ev, suggestion) {
		//myVal = $('#jql_query').val();
		ev.preventDefault();
		var value = name_value[suggestion];
		if (value == null)
			value = suggestion;
		console.log('myval: ' + myVal);
		var lastIndex = myVal.lastIndexOf(" ");
		if (lastIndex === -1)
			myVal = "";
		else
			myVal = myVal.substring(0, lastIndex);
		if (myVal != "")
			$('#jql_query').typeahead('val', myVal + " " + value);
		else
			$('#jql_query').typeahead('val', value);

	});
	
	$('.ajax-typeahead').typeahead({
	  hint: true,
	  highlight: true,
	  minLength: 0
	},
	{
	  limit: 12,
	  async: true,
	  source: function (query, processSync, processAsync) {
		var filtered = [];
		var fieldName = "";
		//var operator = "";
		var fieldValue = "";
		var valid_fieldName = false;
		var operator_entered = false;
		
		
		if ($('#jql_query').val().length === 0 || $('#jql_query').val().trim().length === 0)
		{
			filtered = fieldsNamesDisplay;
		}
		else
		{
			for (var i = 0 ; i < fieldsNamesDisplay.length; i++)
			{
				if (fieldsNamesDisplay[i].startsWith($('#jql_query').val().trim()))
				{
					filtered.push(fieldsNamesDisplay[i]);
				}
			}
		}	
		
		fieldName = $('#jql_query').val();
		if (fieldName.endsWith(' ') && fieldName.trim().length > 0 && operator == "")
		{
			if (name_operator[fieldName.trim()])
			{
				filtered = name_operator[fieldName.trim()];
			}
			else
			{
				filtered = operators;
			}
		}
		
		processSync(filtered);

		for (var i = 0; i < operators.length; i ++)
		{
			operator = $('#jql_query').val().trim().endsWith(operators[i]) ? operators[i] : operator;
		}
		
			if ( operator)
			{
				//operator = operators[i];
				console.log("operator: " + operator);
				var query =  $('#jql_query').val().replace(/\s+/, "").split(operator);
				fieldName = query[0];
				fieldValue = query[1];
				console.log("query: " + query);
				
				return $.ajax({
				  
				  url: "http://jira.touchcommerce.com/rest/api/2/jql/autocompletedata/suggestions/", 
				  headers: {
						"Authorization": "Basic amlyYV9hcGk6N2M4YmMxYjRhMA=="
				  },
				  /*
				  url: "http://agvinfosec01.touchcommerce.com:8080/rest/api/2/jql/autocompletedata/suggestions/",
				  headers: {
						"Authorization": "Basic amlyYV9hcGk6VDNzdGluZzEyMw=="
				  },
				  */
				  type: 'GET',
				  data: {'fieldName': fieldName.trim(), 'fieldValue':fieldValue.trim()},
				  dataType: 'json',
				  success: function (json) {
					  
					 var results = json.results;
					 console.log(results);
					 var json_d = [];
					 var rex = /(<([^>]+)>)/ig;
					 for (var i = 0; i < results.length; i++)
					 {
						 var display = results[i].displayName.replace(rex,"")
						 json_d.push(display);
						 name_value[display] = results[i].value;
					 }
					return processAsync(json_d);
				}
				  
				});
				
			}
		
		
		
	
	  }
	
	});
	
	$( "#callapi" ).submit(function( event ) {
		var parameter = "/jira/?jql=";
		//console.log($('#jql_query').val());
		parameter += $('#jql_query').val();
		//console.log(parameter);
		window.location.href = encodeURI(parameter);
		event.preventDefault();
	});

	/*
	$("#export_drop")
	.hover(
	function(){
		$('#message').html("Export selected " + status_d.top(Infinity).length + " objects to file");
		$('#message').show();
	},
	function(){
		$('#message').hide();
	}
	);
	
	$("#s_json").click(function() {
		var json_string = JSON.stringify(status_d.top(Infinity));
		var blob = new Blob([json_string], {type: "application/json"});
		saveAs(blob, "jira_json_"  + ".json");

	});
	
	
	$("#s_csv").click(function() {
		var json_obj = status_d.top(Infinity);
		var input = json_obj;
		if (!input) {
			return;
		}
		var json = json_obj;
		var inArray = arrayFrom(json);
		var outArray = [];
		for (var row in inArray)
		  outArray[outArray.length] = parse_object(inArray[row]);
		
		var csv = $.csv.fromObjects(outArray);
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
		saveAs(blob, "jira_csv_"+ ".csv");
	});
	*/
</script>
<h3 id = "jql_text" style = "margin-bottom: 0px;"></h3>
<h3 id = "nresults" style = "margin-bottom: 0px;"></h3>
<h3 id = "sresults" style = "margin-top: 0px;"></h3>

<div id = "error" hidden><p>Error Getting data from Jira REST API.</p></div>
<div id = "norsults" hidden><p>No data to show!</p></div>

<div id = "charts">
<div id = "created"><p>Created - 
<a class="reset" href="javascript:created_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "resolutiondate"><p>Resolution Date - 
<a class="reset" href="javascript:resolutiondate_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "reporter"><p>Reporter - 
<a class="reset" href="javascript:reporter_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "assignee"><p>Assignee - 
<a class="reset" href="javascript:assignee_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "time"><p>Time - 
<a class="reset" href="javascript:time_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
<p id = "total_time">Total: Loading..</p> 
</div>

<div id = "project"><p>Project - 
<a class="reset" href="javascript:project_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "issuetype"><p>Issue Type - 
<a class="reset" href="javascript:issuetype_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "status"><p>Status - 
<a class="reset" href="javascript:status_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "resolution"><p>Resolution - 
<a class="reset" href="javascript:resolution_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>
</div>
<script>
	var json_obj = <?php 
		echo $results;
	?>;
	var issues = json_obj['issues'];
	var length = 0;
	if (issues)
		length = issues.length;
	
	var jql_t = <?php	
		echo json_encode($jql);
	?>;
	$('#jql_text').html("JQL: "+ jql_t);
	
	$('#nresults').html("Number of Results: "+ length);
	if (!json_obj || json_obj == "")
	{
		$('#charts').hide();
		$('#error').show();
	}
	else if (!issues || length == 0)
	{
		$('#charts').hide();
		$('#norsults').show();
	}
	var time_chart = dc.barChart('#time');
	var created_chart = dc.barChart("#created");
	var resolutiondate_chart = dc.barChart("#resolutiondate");
	var reporter_chart = dc.rowChart("#reporter");
	var assignee_chart = dc.rowChart("#assignee");
	var project_chart = dc.rowChart("#project");
	var issuetype_chart = dc.pieChart("#issuetype");
	var status_chart = dc.pieChart("#status");
	var resolution_chart = dc.pieChart("#resolution");
	
	var charts = [time_chart, created_chart, resolutiondate_chart, reporter_chart, assignee_chart, project_chart, issuetype_chart, status_chart, resolution_chart];

	var issues_cf = crossfilter(issues);
	var time_d = issues_cf.dimension(function(d){return d.fields.timespent == null? 0 : d.fields.timespent/3600;});
	var count_by_time = time_d.group();
	
	function getTime_e(issues){
		issues.sort(function(a,b){
			return b.fields.timespent - a.fields.timespent;});
		for (var i = 0; i < issues.length; i++)
		{
			var issue = issues[i];
			if (issue && issue.fields && issue.fields.resolutiondate)
			{
				//console.log(issue.fields.resolutiondate);
				return issue.fields.timespent/3600;
			}
		}
	}
	
	var time_e = getTime_e(issues);
	
	time_chart
		.width(500)
		.height(250)
		.dimension(time_d)
		.group(count_by_time)
		.x(d3.scale.linear().domain([0,time_e]))
		.xAxisLabel("Hrs");
		//.elasticX(true);
		
	var created_d = issues_cf.dimension(function (d) {
		return new Date(d.fields.created);
	});
	var count_by_created = created_d.group(function (d) {
		return d3.time.day(d);
	});

	
	var created_sdate = new Date(issues.sort(function(a,b){
			var c = new Date(a.fields.created).getTime();
			var d = new Date(b.fields.created).getTime();
			return c-d;})[0].fields.created);
	var created_edate = new Date(issues.sort(function(a,b){
			var c = new Date(a.fields.created).getTime();
			var d = new Date(b.fields.created).getTime();
			return c-d;})[length-1].fields.created);

	created_chart
		.width(600)
		.height(250)
		.dimension(created_d)
		.group(count_by_created)
		.x(d3.time.scale().domain([created_sdate, created_edate]).nice(d3.time.day))
		//.xAxis().tickFormat(function (x) {
		//	return x.getDate() + "/" + (x.getMonth());
		//});
		.xUnits(d3.time.days);
	
	var resolutiondate_d = issues_cf.dimension(function (d) {
		//if (d.fields.resolutiondate)
			return new Date(d.fields.resolutiondate);
		/*
		else
		{
			var x = 12; 
			var myresolutionDate = new Date(d.fields.created);
			myresolutionDate.setMonth(myresolutionDate.getMonth() + x);
			return myresolutionDate;
		}
		*/
	});
	var count_by_resolutiondate = resolutiondate_d.group(function (d) {
		return d3.time.day(d);
	});
	
	function getResolution_sdate(issues){
		issues.sort(function(a,b){
			var c = new Date(a.fields.resolutiondate).getTime();
			var d = new Date(b.fields.resolutiondate).getTime();
			return c-d;});
		for (var i = 0; i < issues.length; i++)
		{
			var issue = issues[i];
			if (issue && issue.fields && issue.fields.resolutiondate)
			{
				//console.log(issue.fields.resolutiondate);
				return new Date(issue.fields.resolutiondate);
			}
		}
	}
	
	var resolutiondate_sdate = getResolution_sdate(issues);
			
	var resolutiondate_edate = new Date(issues.sort(function(a,b){
			var c = new Date(a.fields.resolutiondate).getTime();
			var d = new Date(b.fields.resolutiondate).getTime();
			return c-d;})[length-1].fields.resolutiondate);

	resolutiondate_chart
		.width(600)
		.height(250)
		.dimension(resolutiondate_d)
		.group(count_by_resolutiondate)
		.x(d3.time.scale().domain([resolutiondate_sdate, resolutiondate_edate]).nice(d3.time.day))
		//.xAxis().tickFormat(function (x) {
		//	return x.getDate() + "/" + (x.getMonth());
		//});
		.xUnits(d3.time.days);
		
	var reporter_d = issues_cf.dimension(function (d) {
		if (d.fields.reporter)
			return d.fields.reporter.displayName;
		else
			return "N/A";
	});
	var count_by_reporter = reporter_d.group();
	var reporter_height = count_by_reporter.all().length * 20 + 200;
	
	reporter_chart
	.width(300)
	.height(reporter_height)
	.dimension(reporter_d)
	.group(count_by_reporter)
	.elasticX(true);
	
	var assignee_d = issues_cf.dimension(function (d) {
		if (d.fields.assignee)
			return d.fields.assignee.displayName;
		else
			return 'N/A';
	});
	var count_by_assignee = assignee_d.group();
	var assignee_height = count_by_assignee.all().length * 20 + 200;
	
	assignee_chart
	.width(300)
	.height(assignee_height)
	.dimension(assignee_d)
	.group(count_by_assignee)
	.elasticX(true);
	
	var project_d = issues_cf.dimension(function (d) {
		if (d.fields.project)
			return d.fields.project.name;
		else
			return 'N/A';
	});
	var count_by_project = project_d.group();
	var project_height = count_by_project.all().length * 20 + 200;
	
	project_chart
	.width(300)
	.height(project_height)
	.dimension(project_d)
	.group(count_by_project)
	.elasticX(true);
	
	var issuetype_d = issues_cf.dimension(function(d){return d.fields.issuetype.name;});
	var count_by_issuetype = issuetype_d.group();
	var issuetype_n = count_by_project.all().length;

	issuetype_chart
		.width(issuetype_n*10+200)
		.height(issuetype_n*10+200)
		.slicesCap(issuetype_n)
		.innerRadius(0)
		.dimension(issuetype_d)
		.group(count_by_issuetype)
		.legend(dc.legend());
	
	var status_d = issues_cf.dimension(function(d){return d.fields.status.name;});
	var count_by_status = status_d.group();
	var status_n = count_by_project.all().length;

	status_chart
		.width(status_n*10+200)
		.height(status_n*10+200)
		.slicesCap(status_n)
		.innerRadius(0)
		.dimension(status_d)
		.group(count_by_status)
		.legend(dc.legend());
		
	var resolution_d = issues_cf.dimension(function(d){
		if (d.fields.resolution)
			return d.fields.resolution.name;
		else
			return 'N/A';
	});
	var count_by_resolution = resolution_d.group();
	var resolution_n = count_by_project.all().length;

	resolution_chart
		.width(resolution_n*10+200)
		.height(resolution_n*10+200)
		.slicesCap(resolution_n)
		.innerRadius(0)
		.dimension(resolution_d)
		.group(count_by_resolution)
		.legend(dc.legend());

	var total_default = issues_cf.groupAll().reduceSum(function(d){return d.fields.timespent/3600;}).value();
	var num_default = issues_cf.groupAll().value();
	var avg_default = total_default / num_default;
	$("#total_time").html("Total: " + total_default.toFixed(2) + " hours&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Avg: " + avg_default.toFixed(2) + " hours");
	dc.renderAll();
	for (var i = 0; i < charts.length; i++)
{
		charts[i].on("filtered", function(chart, filter){
		var total = issues_cf.groupAll().reduceSum(function(d){return d.fields.timespent/3600;}).value();
		var num = issues_cf.groupAll().value();
		var avg = total / num;
		$("#total_time").html("Total: " + total.toFixed(2) + " hours&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Avg: " + avg.toFixed(2) + " hours");
		$("#sresults").html("Number of selected results: " + num);
		});
}
</script>
</body>
</html>
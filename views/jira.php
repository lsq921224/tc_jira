<?php
	$username = 'jira_api';
	$password = '7c8bc1b4a0';
	$URL = 'http://jira.touchcommerce.com/rest/api/2/search';
	$URL_AUTO = 'http://jira.touchcommerce.com/rest/api/2/jql/autocompletedata/';
	$URL_FIELDS = 'http://jira.touchcommerce.com/rest/api/2/field';
	//$password = 'T3sting123';
	//$URL = 'http://agvinfosec01.touchcommerce.com:8080/rest/api/2/search';
	$jql = !is_null($_GET["jql"]) && $_GET["jql"] != ''  ? $_GET["jql"] : ' ';
	?>
	<script>
		console.log(<?php echo json_encode($jql);?>); 
	</script>
	<?php
	$default_fields = "created,resolutiondate,reporter,assignee,project,issuetype,status,resolution,timespent";
	$other_fields = "customfield_12890,customfield_14397,customfield_14396,components,customfield_14391,customfield_14390,customfield_13107,customfield_11391,customfield_11590,timeestimate,customfield_13790,priority,customfield_11990,customfield_14215,fixVersions,customfield_14403,timeoriginalestimate,duedate,customfield_10891,customfield_10890,customfield_10791,customfield_14091,customfield_10143,customfield_11090,customfield_11092,customfield_11093,customfield_10150,versions";
	$data = 'jql='.urlencode($jql).'&fields='.$default_fields.','.$other_fields.'&maxResults=1000';
	//$data = 'jql=assignee=mmcclarin&maxResults=50';
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
	curl_setopt($ch, CURLOPT_URL, $URL_FIELDS);
	$fields = curl_exec($ch);
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
	<input type='text' class='ajax-typeahead form-control' id= "jql_query" style="width:550px;"/>
	<input type="submit" class="btn btn-default" style="position:relative; margin-left: 555px;"></input>
	</form>
	</div>
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
	var customFieldsJSON = <?php echo $fields; ?>;
	var fieldsNames = fieldsNameJSON['visibleFieldNames'];
	var displayName_value = new Object();
	var name_operator = new Object();
	var name_value = new Object();
	var id_name = new Object();
	var fieldsNamesDisplay = [];
	for (var i = 0; i < fieldsNames.length; i++)
	{
		name_operator[fieldsNames[i].displayName] = fieldsNames[i].operators;
		displayName_value[fieldsNames[i].displayName] = fieldsNames[i].value;
		fieldsNamesDisplay.push(fieldsNames[i].displayName);
	}
	for (var i = 0; i < customFieldsJSON.length; i++)
	{
		id_name[customFieldsJSON[i].id] = customFieldsJSON[i].name;
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
			operator = $('#jql_query').val().trim().endsWith(" " + operators[i]) ? operators[i] : operator;
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
			operator = $('#jql_query').val().trim().endsWith(" " + operators[i]) ? operators[i] : operator;
		}
		 myVal = $('#jql_query').val();
	});
	
	$('.ajax-typeahead').bind('typeahead:asyncrequest', function(ev, query, name) {
		 myVal = $('#jql_query').val();
	});
	
	
	$('.ajax-typeahead').bind('typeahead:select', function(ev, suggestion) {
		//myVal = $('#jql_query').val();
		ev.preventDefault();
		var value = name_value[suggestion] ? name_value[suggestion] : displayName_value[suggestion];
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
		
		
		if ($('#jql_query').val().length === 0 || $('#jql_query').val().trim().length === 0 || $('#jql_query').val().trim().toUpperCase().endsWith('AND') || $('#jql_query').val().trim().toUpperCase().endsWith('OR') )
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
		
		var startIndexAND = $('#jql_query').val().toUpperCase().lastIndexOf('AND ');
		var startIndexOR = $('#jql_query').val().toUpperCase().lastIndexOf('OR ');
		var start = 0;
		if (startIndexAND != -1)
		{
			start = startIndexAND + 3;
			fieldName = $('#jql_query').val().substring(start, $('#jql_query').val().length);
			//console.log('fieldName: ' + fieldName);
			filtered = [];
			for (var i = 0 ; i < fieldsNamesDisplay.length; i++)
			{
				if (fieldsNamesDisplay[i].startsWith(fieldName.trim()))
				{
					filtered.push(fieldsNamesDisplay[i]);
				}
			}
			
		}
		else if (startIndexOR != -1)
		{
			start = startIndexOR + 2;
			fieldName = $('#jql_query').val().substring(start, $('#jql_query').val().length);
			filtered = [];
			for (var i = 0 ; i < fieldsNamesDisplay.length; i++)
			{
				if (fieldsNamesDisplay[i].startsWith(fieldName.trim()))
				{
					filtered.push(fieldsNamesDisplay[i]);
				}
			}
		}
		else fieldName = $('#jql_query').val();
		
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
			operator = $('#jql_query').val().endsWith( " " + operators[i]) ? operators[i] : operator;
		}
		
		if ($('#jql_query').val().lastIndexOf(operator) <= start)
		{
			operator = "";
		}
			if ( operator)
			{
				//operator = operators[i];
				console.log("operator: " + operator);
				//var query =  $('#jql_query').val().replace(/\s+/, "").split(operator);
				//fieldName = query[query.length-2];
				var indexofOperator = $('#jql_query').val().lastIndexOf(operator) + operator.length;
				fieldName = $('#jql_query').val().substring( start, indexofOperator - operator.length).trim();
				fieldValue = $('#jql_query').val().substring(indexofOperator,  $('#jql_query').val().length).trim();
				console.log("FieldName: " + fieldName);
				console.log("fieldValue: " + fieldValue);
				
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

<div id = "error" hidden><p id = "err_message"></p></div>
<div id = "norsults" hidden><p>No data to show!</p></div>
<div id = "result">
<label class="radio-inline"><input id = "show_default" class = "rg" type="radio" name="optradio" checked="">Show default charts</label>
<label class="radio-inline"><input id = "show_all" class = "rg" type="radio" name="optradio">Show all charts</label>
<script>
	$(".rg").change(function () {

	if ($("#show_default").is(":checked")) {
			$('#other_charts').hide();
        }
        else {
			$('#other_charts').show();
        }
    });
</script>
<div id = "charts" style = "margin-top: 10px;">
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
<div id = "other_charts" hidden>
<div id = "customfield_12890"><p>Service Impacting Regression - 
<a class="reset" href="javascript:customfield_12890_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "customfield_14397"><p>Target Branch - 
<a class="reset" href="javascript:customfield_14397_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "components"><p>Components - 
<a class="reset" href="javascript:components_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "customfield_14396"><p>Business Targeted Date - 
<a class="reset" href="javascript:customfield_14396_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "customfield_14391"><p>Risk Mitigation Notes - 
<a class="reset" href="javascript:customfield_14391_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "customfield_14390"><p>Hotfixable - 
<a class="reset" href="javascript:customfield_14390_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "customfield_13107"><p>Tech Lead - 
<a class="reset" href="javascript:customfield_13107_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "customfield_11391"><p>Product Feature - 
<a class="reset" href="javascript:customfield_11391_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "customfield_11590"><p>Percent Done - 
<a class="reset" href="javascript:customfield_11590_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

<div id = "timeestimate"><p>Remaining Estimate - 
<a class="reset" href="javascript:timeestimate_chart.filterAll();dc.redrawAll();" style = "display: none;">reset</a> </p>
</div>

</div>
</div>
<script>
	var json_obj = <?php 
		echo $results;
	?>;
	var issues = json_obj['issues'];
	var errors = json_obj['errorMessages'];
	
	var length = 0;
	if (issues)
		length = issues.length;
	
	var jql_t = <?php	
		echo json_encode($jql);
	?>;
	$('#jql_text').html("JQL: "+ jql_t);
	
	$('#nresults').html("Number of Results: "+ length);
	if (!json_obj || json_obj == "" || errors)
	{
		$('#result').hide();
		$('#err_message').html("Error: " + errors[0]);
		$('#error').show();
	}
	else if (!issues || length == 0)
	{
		$('#result').hide();
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
	
	var customfield_12890_chart = dc.rowChart('#customfield_12890');
	var customfield_14397_chart = dc.rowChart('#customfield_14397');
	var components_chart = dc.rowChart('#components');
	var customfield_14396_chart = dc.barChart('#customfield_14396');
	var customfield_14391_chart = dc.rowChart('#customfield_14391');
	var customfield_14390_chart = dc.rowChart('#customfield_14390');
	var customfield_13107_chart = dc.rowChart('#customfield_13107');
	var customfield_11391_chart = dc.rowChart('#customfield_11391');
	var customfield_11590_chart = dc.barChart('#customfield_11590');
	var timeestimate_chart = dc.barChart('#timeestimate');
	
	var charts = [time_chart, created_chart, resolutiondate_chart, reporter_chart, assignee_chart, project_chart, issuetype_chart, status_chart, resolution_chart];
	var o_charts = [customfield_12890_chart, customfield_14397_chart, components_chart, customfield_14396_chart, customfield_14391_chart, customfield_14390_chart, customfield_13107_chart, customfield_11391_chart,customfield_11590_chart, timeestimate_chart];
	
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
		.xAxisLabel("Hrs")
		.elasticX(true);
		
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
	var issuetype_n = count_by_issuetype.all().length;

	issuetype_chart
		.width(issuetype_n*12+200)
		.height(issuetype_n*12+200)
		.slicesCap(issuetype_n)
		.innerRadius(0)
		.dimension(issuetype_d)
		.group(count_by_issuetype)
		.legend(dc.legend());
	
	var status_d = issues_cf.dimension(function(d){return d.fields.status.name;});
	var count_by_status = status_d.group();
	var status_n = count_by_status.all().length;

	status_chart
		.width(status_n*12+200)
		.height(status_n*12+200)
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
	var resolution_n = count_by_resolution.all().length;

	resolution_chart
		.width(resolution_n*12+200)
		.height(resolution_n*12+200)
		.slicesCap(resolution_n)
		.innerRadius(0)
		.dimension(resolution_d)
		.group(count_by_resolution)
		.legend(dc.legend());
		
	
	
	
	

	var total_default = issues_cf.groupAll().reduceSum(function(d){return d.fields.timespent/3600;}).value();
	var num_default = issues_cf.groupAll().value();
	var avg_default = total_default / num_default;
	$("#total_time").html("Total: " + total_default.toFixed(2) + " hours&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Avg: " + avg_default.toFixed(2) + " hours");
	for (var i = 0; i < charts.length; i++)
	{
		charts[i].on("filtered", function(chart, filter){
		var total = issues_cf.groupAll().reduceSum(function(d){return d.fields.timespent/3600;}).value();
		var num = issues_cf.groupAll().value();
		var avg = total / num;
		$("#total_time").html("Total: " + total.toFixed(2) + " hours&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Avg: " + avg.toFixed(2) + " hours");
		$("#sresults").html("Number of selected results: " + num);
		});
		charts[i].render();
	}
	
	/****            expanded fields                       ****/
	var customfield_12890_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.customfield_12890 && d.fields.customfield_12890.length > 0)
			return d.fields.customfield_12890[0].value;
		else
			return 'N/A';
	});
	var count_by_customfield_12890 = customfield_12890_d.group();
	var customfield_12890_height = count_by_customfield_12890.all().length * 20 + 200;
	
	customfield_12890_chart
	.width(300)
	.height(customfield_12890_height)
	.dimension(customfield_12890_d)
	.group(count_by_customfield_12890)
	.elasticX(true);
		
	var customfield_14397_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.customfield_14397)
			return d.fields.customfield_14397[0];
		else
			return 'None';
	});
	
	var count_by_customfield_14397 = customfield_14397_d.group();
	var customfield_14397_height = count_by_customfield_14397.all().length * 20 + 200;
	
	customfield_14397_chart
	.width(300)
	.height(customfield_14397_height)
	.dimension(customfield_14397_d)
	.group(count_by_customfield_14397)
	.elasticX(true);
	
	var components_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.components && d.fields.components.length > 0)
			return d.fields.components[0].name;
		else
			return 'N/A';
	});
	
	var count_by_components = components_d.group();
	var components_height = count_by_components.all().length * 20 + 200;
	
	components_chart
	.width(300)
	.height(components_height)
	.dimension(components_d)
	.group(count_by_components)
	.elasticX(true);
	
	var customfield_14396_d = issues_cf.dimension(function (d) {
			return new Date(d.fields.customfield_14396);
	});
	var count_by_customfield_14396 = customfield_14396_d.group(function (d) {
		return d3.time.day(d);
	});
	
	function getBT_sdate(issues){
		issues.sort(function(a,b){
			var c = new Date(a.fields.customfield_14396).getTime();
			var d = new Date(b.fields.customfield_14396).getTime();
			return c-d;});
		for (var i = 0; i < issues.length; i++)
		{
			var issue = issues[i];
			if (issue && issue.fields && issue.fields.customfield_14396)
			{
				return new Date(issue.fields.customfield_14396);
			}
		}
	}
	
	var customfield_14396_sdate = getBT_sdate(issues);
			
	var customfield_14396_edate = new Date(issues.sort(function(a,b){
			var c = new Date(a.fields.customfield_14396).getTime();
			var d = new Date(b.fields.customfield_14396).getTime();
			return c-d;})[length-1].fields.customfield_14396);

	customfield_14396_chart
		.width(600)
		.height(250)
		.dimension(customfield_14396_d)
		.group(count_by_customfield_14396)
		.x(d3.time.scale().domain([customfield_14396_sdate, customfield_14396_edate]).nice(d3.time.day))
		.xUnits(d3.time.days);
		
	var customfield_14391_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.customfield_14391)
			return d.fields.customfield_14391;
		else
			return 'None';
	});
	
	var count_by_customfield_14391 = customfield_14391_d.group();
	var customfield_14391_height = count_by_customfield_14391.all().length * 20 + 200;
	
	customfield_14391_chart
	.width(500)
	.height(customfield_14391_height)
	.dimension(customfield_14391_d)
	.group(count_by_customfield_14391)
	.elasticX(true);
	
	var customfield_14390_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.customfield_14390)
			return d.fields.customfield_14390.value;
		else
			return 'None';
	});
	var count_by_customfield_14390 = customfield_14390_d.group();
	var customfield_14390_height = count_by_customfield_14390.all().length * 20 + 200;
	
	customfield_14390_chart
	.width(300)
	.height(customfield_14390_height)
	.dimension(customfield_14390_d)
	.group(count_by_customfield_14390)
	.elasticX(true);
	
	var customfield_13107_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.customfield_13107)
			return d.fields.customfield_13107.name;
		else
			return 'N/A';
	});
	var count_by_customfield_13107 = customfield_13107_d.group();
	var customfield_13107_height = count_by_customfield_13107.all().length * 20 + 200;
	
	customfield_13107_chart
	.width(300)
	.height(customfield_13107_height)
	.dimension(customfield_13107_d)
	.group(count_by_customfield_13107)
	.elasticX(true);
	
	var customfield_11391_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.customfield_11391)
			return d.fields.customfield_11391[0];
		else
			return 'N/A';
	});
	var count_by_customfield_11391 = customfield_11391_d.group();
	var customfield_11391_height = count_by_customfield_11391.all().length * 20 + 200;
	
	customfield_11391_chart
	.width(300)
	.height(customfield_11391_height)
	.dimension(customfield_11391_d)
	.group(count_by_customfield_11391)
	.elasticX(true);
	
	var customfield_11590_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.customfield_11590)
			return d.fields.customfield_11590;
		else
			return 0;
	});
	var count_by_customfield_11590 = customfield_11590_d.group();
	var customfield_11590_height = count_by_customfield_11590.all().length * 20 + 200;
	
	customfield_11590_chart
	.width(300)
	.height(200)
	.dimension(customfield_11590_d)
	.group(count_by_customfield_11590)
	.margins({top:10,right:20,bottom:30,left:40})
	.x(d3.scale.linear().domain([0,1]));
	
	
	var timeestimate_d = issues_cf.dimension(function(d) 
	{
		if (d.fields.timeestimate)
			return d.fields.timeestimate;
		else
			return 0;
	});
	var count_by_timeestimate = timeestimate_d.group();
	//var timeestimate_height = count_by_timeestimate.all().length * 20 + 200;
	
	function get_eestimate(issues){
		issues.sort(function(a,b){
			var c = a.fields.timeestimate;
			var d = b.fields.timeestimate;
			return c-d;});
		return issues[issues.length-1].fields.timeestimate;
	}
	
	function get_sestimate(issues){
		issues.sort(function(a,b){
			var c = a.fields.timeestimate;
			var d = b.fields.timeestimate;
			return c-d;});
		return issues[0].fields.timeestimate;
	}
	
	var eestimate = get_eestimate(issues);
	var sestimate = get_sestimate(issues);
	if (!eestimate)
		eestimate  = 60;
	if (!sestimate)
		sestimate = 0;
	
	timeestimate_chart
	.width(300)
	.height(200)
	.dimension(timeestimate_d)
	.group(count_by_timeestimate)
	.margins({top:10,right:20,bottom:30,left:40})
	.x(d3.scale.linear().domain([sestimate/60,eestimate/60]))
	//.xUnits(d3.time.hours)
	//.elasticX(true)
	.xAxisLabel("Mins");

	for (var i = 0; i < o_charts.length; i++)
	{
		o_charts[i].render();
	}
</script>
</body>
</html>

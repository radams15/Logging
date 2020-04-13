<center>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

<?php

$f = file_get_contents("data");

$measurements = array_reverse(explode("\n", $f));

$table = "";

$table .= "<div><table class='table table-bordered table-hover'>";
$table .= "<thead class='thead-dark'><tr><th>Date</th><th>Temperature (&#8451)</th><th>Pressure(mBar)</th></tr></thead><tbody>";

$out = [];

foreach($measurements as $m){
	if ($m == ""){
		continue;
	}
	$s = explode(",", $m);
	$date = date("d/m/Y h:i:sa",$s[2]);
	$temp = number_format($s[0], 1);
	$pres = number_format($s[1], 0, ".", "");
	
	$out[] = [$s[2], floatval($s[0]), intval($s[1])];

	$table .= "<tr> <td>$date</td> <td>$temp</td> <td>$pres</td> </tr>";
}

$table .= "</tbody></table></div>";

$latest = $out[0];

if($latest[2] > 1020){
	$weather = "Very Dry";
}else if($latest[2] > 1005){
	$weather = "Fair";
}else if($latest[2] > 995){
	$weather = "Changeable";
}else if($latest[2] > 975){
	$weather = "Rain";
}else{
	$weather = "Stormy";
}

echo "<h2>Latest Measurement (" . date("h:i:sa",$latest[0]) . "):</h2><h3>Temperature: ". number_format($latest[1], 1) ."&degC<br>Pressure: $latest[2] mBar<br>Weather: $weather</h3>";

include "graph.js.php";

?>

<br><br><br><br>

<div class="graph">
		<canvas id="canvas"></canvas>
</div>

<style>
.graph{
	width: 100%;
}
</style>

<script type="text/javascript">
var ctx = "canvas";

var timeFormat = 'MM/DD/YYYY HH:mm';

function epoch(str){
	var date = new Date(0);
	date.setUTCSeconds(str);
	//return date;
	return moment.unix(parseInt(str));
}

var dates = [<?php
		foreach(array_slice($out, 0, -1) as $m){
			echo "epoch(\"$m[0]\"),";
		}
	?>];

var temperatures = [<?php
		foreach($out as $m){
			echo "$m[1],";
		}
	?>];

var pressures = [<?php
		foreach($out as $m){
			echo "$m[2],";
		}
	?>];

var presCol = "#2600ff";
var tempCol = "#ff0000";

dates.pop(); // remove last element which is null

var color = Chart.helpers.color;
var config = {
	type: 'line',
	data: {
		labels: dates,
		datasets: [{
			label: 'My First dataset',
			backgroundColor: color(tempCol).alpha(0.5).rgbString(),
			borderColor: tempCol,
			fill: false,
			label: 'Temperature',
			yAxisID: 'temperature',
			data: temperatures
		}, {
			label: 'My Second dataset',
			backgroundColor: color(presCol).alpha(0.5).rgbString(),
			borderColor: presCol,
			fill: false,
			label: 'Pressure',
			yAxisID: 'pressure',
			data: pressures,
		}]
	},
	options: {
		title: {
			text: 'Chart.js Time Scale'
		},
		elements: {
			line: {
				tension: 0.6
			}
		},
		scales: {
			xAxes: [{
				type: 'time',
				time: {
					parser: timeFormat,
					time: {},
				},
			}],		
			yAxes: [{
				id: 'temperature',
				type: 'linear',
				position: 'left',
				scaleLabel: {
					display: true,
					labelString: 'Temperature (\u00B0C)'
				}
			  }, {
				id: 'pressure',
				type: 'linear',
				position: 'right',
				scaleLabel: {
					display: true,
					labelString: 'Pressure (mBar)'
				}
			  }]
		},
	}
};

var myLineChart = new Chart(ctx, config);
</script>

<br><br><br><br>

<?php
echo $table;
?>
</center>

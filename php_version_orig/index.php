<center>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
<script type="text/javascript" src="chartjs-plugin-colorschemes.js"></script>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"><</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

<?php

$f = file_get_contents("data");

$measurements = array_reverse(explode("\n", $f));

//$table = "";

//$table .= "<div><table class='table table-bordered table-striped table-dark'>";
//$table .= "<thead class='thead-dark'><tr><th>Date</th><th>Temperature (&#8451)</th><th>Pressure(mBar)</th></tr></thead><tbody>";

$out = [];

$delay = (intval(explode(",", $measurements[1])[2]) - intval(explode(",", $measurements[2])[2]));

$before = intval(explode(",", $measurements[1])[2])-$delay;

foreach($measurements as $m){
	if ($m == ""){
		continue;
	}
	$s = explode(",", $m);
	$date = date("d/m/Y h:i:sa",$s[2]);
	$temp = number_format($s[0], 1);
	$pres = number_format($s[1], 2, ".", "");

	$out[] = [$s[2], floatval($s[0]), floatval($s[1])];

	//$table .= "<tr> <td>$date</td> <td>$temp</td> <td>$pres</td> </tr>";
}

//$table .= "</tbody></table></div>";

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

echo "<h2>Latest Measurement (" . date("h:i:sa",$latest[0]) . "):</h2><h3>Temperature: ". number_format($latest[1], 1) ."&degC<br>Pressure: ".number_format($latest[2], 2, ".", "")." mBar<br>Weather: $weather</h3>";

include "graph.js.php";

?>

<br><br><br><br>

<div class="graph">
		<canvas id="canvas"></canvas>
</div>

<style>
	body{
		background-color: #4E4E4E;
	}

	p, h1, h2, h3, tr, th{
		color: white;
	}

	.graph{
		width: 90%;
	}

	table{
		width: 90% !important;
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
dates.pop(); // remove last element which is null


Chart.defaults.global.defaultFontColor = "white";
var color = Chart.helpers.color;
var config = {
	type: 'line',
	data: {
		labels: dates,
		datasets: [{
			label: 'Temperature',
			fill: false,
			label: 'Temperature',
			yAxisID: 'temperature',
			data: temperatures
		}, {
			label: 'Pressure',
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
		plugins: {
			colorschemes: {
				scheme: 'brewer.DarkTwo7'
			}
		},
		elements: {
			line: {
				tension: 0.3
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
//echo $table;
?>
</center>

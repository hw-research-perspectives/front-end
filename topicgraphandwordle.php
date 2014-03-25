<?php
require_once("config.inc.php");

// override this here, yhyh about the config file, but it seems the wordle needs meng_project and this needs meng_rcuk?
$dbname = "meng_rcuk";
	
$f = fopen("spendpertopicperschool.csv", "wb");

// Connect to server and select databse.
$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
	
$queryToUse="SELECT TopicID, sum(LifeSciences), sum(EngineeringAndPhysical), sum(BuiltEnvironment), sum(ManagementAndLanguages), sum(Petroleum), sum(Macs), sum(TechRes), sum(Textiles), sum(Other) FROM vw_hw_totalspendbyschool where TopicID = :topicID;";
	
$query = $db->prepare($queryToUse);
$query->execute(array(":topicID" => $_GET['topicID']));
	
$result = $query->fetchAll();
	
if (count($result) == 1) {
	
	fwrite($f, '"TopicId","Sch of Life Sciences","Sch of Engineering and Physical Science","Sch of the Built Environment","Sch of Management and Languages","Institute Of Petroleum Engineering","S of Mathematical and Computer Sciences","Technology and Research Services","Sch of Textiles and Design","Other"');
	fwrite($f, "\n");
		
	$row = $result[0];
		
	$outputString = $row['TopicID'].",".$row['sum(LifeSciences)'].",".$row['sum(EngineeringAndPhysical)'].",".$row['sum(BuiltEnvironment)'].",".$row['sum(ManagementAndLanguages)'].",".$row['sum(Petroleum)'].",".$row['sum(Macs)'].",".$row['sum(TechRes)'].",".$row['sum(Textiles)'].",".$row['sum(Other)'];
		
	fwrite($f, $outputString);
}

fclose($f);

$query = $db->prepare("SELECT TopicID, TopicWord FROM topicwords_100 where topicID = :topicID;");
$query->execute(array(":topicID" => $_GET['topicID']));
	
$result = $query->fetchAll();
$topicWords = "";
	
if (count($result) > 0) {
	foreach ($result as $row) {
		$topicWords .= '"'.$row['TopicWord'].'",';
	}
	$topicWords = substr($topicWords, 0, strlen($topicWords)-1);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Topic Details</title>
</head>
<style>

body {
  font: 10px sans-serif;
}

.axis path,
.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.bar {
  fill: steelblue;
}

.x.axis path {
  display: none;
}

</style>
<body>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script type="text/javascript" src="D3/d3.layout.cloud.js"></script>
<script>
  var fill = d3.scale.category20();

  d3.layout.cloud().size([960, 350])
      .words([
        <?php echo $topicWords; ?>].map(function(d) {
       // return {text: d, size: 1 + Math.random() * 50};
     return {text: d, size: 40};
      }))
      .padding(2)
      .rotate(0)
      .font("Helvetica")
      .fontSize(function(d) { return d.size; })
      .on("end", draw)
      .start();

  function draw(words) {
    d3.select("body").append("svg")
        .attr("width", 960)
        .attr("height", 350)
      .append("g")
        .attr("transform", "translate(500,170)")
      .selectAll("text")
        .data(words)
      .enter().append("text")
        .style("font-size", function(d) { return d.size + "px"; })
        .style("font-family", "Helvetica")
        .style("fill", function(d, i) { return fill(i); })
        .attr("text-anchor", "middle")
        .attr("transform", function(d) {
          return "translate(" + [d.x,d.y] + ")";
        })
        .text(function(d) { return d.text; });
  }
</script>
<script>

var margin = {top: 40, right: 40, bottom: 100, left: 70},
    width = 960 - margin.left - margin.right,
    height = 350 - margin.top - margin.bottom;

var x0 = d3.scale.ordinal()
    .rangeRoundBands([0, width], .1);

var x1 = d3.scale.ordinal();

var y = d3.scale.linear()
    .range([height, 0]);

var color = d3.scale.ordinal()
    .range(["#efc050", "#d0417e", "#00947e", "#0c1e3c", "#766a62", "#dc241f", "#7fcdcd" , "#FF9900", "#99FF00", "#990033"]);

var xAxis = d3.svg.axis()
    .scale(x0)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left")
    .tickFormat(d3.format(".2s"));

var svg = d3.select("body").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

d3.csv("spendpertopicperschool.csv", function(error, data) {
  var ageNames = d3.keys(data[0]).filter(function(key) { return key !== "TopicId"; });

  data.forEach(function(d) {
    d.spend = ageNames.map(function(name) { return {name: name, value: +d[name]}; });
  });

  x0.domain(data.map(function(d) { return d.TopicId; }));
  x1.domain(ageNames).rangeRoundBands([0, x0.rangeBand()]);
  y.domain([0, d3.max(data, function(d) { return d3.max(d.spend, function(d) { return d.value; }); })]);

  svg.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

  svg.append("g")
      .attr("class", "y axis")
      .call(yAxis)
    .append("text")
      .attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", ".71em")
      .style("text-anchor", "end")
      .text("Total Spend (Pound)");

  var state = svg.selectAll(".state")
      .data(data)
    .enter().append("g")
      .attr("class", "g")
      .attr("transform", function(d) { return "translate(" + x0(d.TopicId) + ",0)"; });

  state.selectAll("rect")
      .data(function(d) { return d.spend; })
    .enter().append("rect")
      .attr("width", x1.rangeBand() - 20)
      .attr("x", function(d) { return x1(d.name); })
      .attr("y", function(d) { return y(d.value); })
      .attr("height", function(d) { return height - y(d.value); })
      .style("fill", function(d) { return color(d.name); });

  var legend = svg.selectAll(".legend")
      .data(ageNames.slice())
    .enter().append("g")
      .attr("class", "legend")
      .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });

  legend.append("rect")
      .attr("x", width - 18)
      .attr("width", 18)
      .attr("height", 18)
      .style("fill", color);

  legend.append("text")
      .attr("x", width - 24)
      .attr("y", 9)
      .attr("dy", ".35em")
      .style("text-anchor", "end")
      .text(function(d) { return d; });

});

</script>
<?php 
include("createsvg.php");
?>
<script>
(function () {
var margin = {top: 40, right: 40, bottom: 100, left: 70},
    width = 960 - margin.left - margin.right,
    height = 350 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y%m").parse;

var alternatingColorScale = function () {
var domain, range;

function scale(x) { return range[domain.indexOf(x)%10]; }
scale.domain = function(x) {
if(!arguments.length) return domain; domain = x; return scale; }
scale.range = function(x) {
if(!arguments.length) return range; range = x; return scale; }
return scale; }
var color = alternatingColorScale().range(["#efc050", "#d0417e", "#00947e", "#0c1e3c", "#766a62", "#dc241f", "#7fcdcd" , "#FF9900", "#99FF00", "#990033"]);

d3.tsv("monthlyfunding.tsv", function(error, data) {
var names = d3.keys(data[0]).filter(function(key) { return key !== "date"; });
color.domain(names);

data.forEach(function(d) {
d.date = parseDate(d.date); });

var x = d3.time.scale().range([0, width]);
var y = d3.scale.linear().range([height, 0]).domain([0, <?php echo $_SESSION['MonthlyFunding']; ?>]);

var xAxis = d3.svg.axis().scale(x).orient("bottom");
var yAxis = d3.svg.axis().scale(y).orient("left");

var area = d3.svg.area().x(function(d) { return x(d.date); })
.y0(function(d) { return y(d.y0); })
.y1(function(d) { return y(d.y0 + d.y); });

var stack = d3.layout.stack().values(function(d) { return d.values; });

var svg = d3.select("body").append("svg")
.attr("width", width + margin.left + margin.right)
.attr("height", height + margin.top + margin.bottom)
.append("g")
.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var browsers = stack(color.domain().map(function(name) {
return { name: name, values: data.map(function(d) {
return { date: d.date, y: d[name] / 1}; }) };
}));

x.domain(d3.extent(data, function(d) { return d.date; }));

var browser = svg.selectAll(".browser").data(browsers)
.enter().append("g").attr("class", "browser");

browser.append("path").attr("class", "area")
.attr("d", function(d) { return area(d.values); })
.style("fill", function(d) { return color(d.name); });

svg.append("g").attr("class", "x axis")
.attr("transform", "translate(0," + height + ")").call(xAxis);

svg.append("g").attr("class", "y axis").call(yAxis)
.append("text")
.attr("transform", "rotate(-90)")
.attr("y", 6)
.attr("dy", ".71em")
.style("text-anchor", "end")
.text("Monthly Funding (Pound)");
	  
  var legend = svg.selectAll(".legend")
      .data(names.slice())
    .enter().append("g")
      .attr("class", "legend")
      .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });

  legend.append("rect")
      .attr("x", width - 18)
      .attr("width", 18)
      .attr("height", 18)
      .style("fill", color);

  legend.append("text")
      .attr("x", width - 24)
      .attr("y", 9)
      .attr("dy", ".35em")
      .style("text-anchor", "end")
      .text(function(d) { return d; });
});
}());
</script>
</body>
</html>
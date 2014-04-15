<?php
$topicID = 0;
if (isset($_GET['topicID'])) {
	$topicID = $_GET['topicID'];
}

require_once('config.inc.php');
$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
$wordleQuery = $db->prepare('SELECT TopicLabel FROM topics_100 where topicID = :topicID;');
$wordleQuery->execute(array(':topicID' => $topicID));
$words = $wordleQuery->fetchColumn();
$wordleQuery->closeCursor();

?>
<script>
(function () {
  var fill = d3.scale.category20();

  d3.layout.cloud().size([960, 350])
      .words([<?php echo '"' . str_replace(' ', '","', trim($words)) . '"' ?>].map(function(d) {
        return {text: d, size: 34 + Math.random() * 20};
     //return {text: d, size: 40};
      }))
      .padding(2)
      .rotate(0)
      .font("Helvetica")
      .fontSize(function(d) { return d.size; })
      .on("end", draw)
      .start();

  function draw(words) {
    d3.select("#wordle_chart").append("svg")
        .attr("width", 960)
        .attr("height", 300)
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
}());

(function () {
var margin = {top: 40, right: 40, bottom: 100, left: 70},
    width = 960 - margin.left - margin.right,
    height = 350 - margin.top - margin.bottom;

var x0 = d3.scale.ordinal()
    .rangeRoundBands([0, width], .1);

var x1 = d3.scale.ordinal();

var y = d3.scale.linear()
    .range([height, 0]);

var color = d3.scale.ordinal()
    .range(["#efc050", "#f3759f", "#00947e", "#0c1e3c", "#766a62", "#dc241f", "#7fcdcd" , "#FF9900", "#99FF00", "#990033"]);

var xAxis = d3.svg.axis()
    .scale(x0)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left")
    .tickFormat(d3.format(".2s"));

var svg = d3.select("#total_spend_chart").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

d3.tsv("svgData.php?query=totalSpend&format=tsv&topicID=<?php echo $topicID;?>", function(error, data) {
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
}());

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
var color = alternatingColorScale().range(["#efc050", "#f3759f", "#00947e", "#0c1e3c", "#766a62", "#dc241f", "#7fcdcd" , "#FF9900", "#99FF00", "#990033"]);

d3.tsv("svgData.php?query=monthlySpend&format=tsv&topicID=<?php echo $topicID;?>", function(error, data) {
var names = d3.keys(data[0]).filter(function(key) { return key !== "date"; });
color.domain(names);

data.forEach(function(d) {
d.date = parseDate(d.date); });

var x = d3.time.scale().range([0, width]);
var y = d3.scale.linear().range([height, 0]);

var xAxis = d3.svg.axis().scale(x).orient("bottom");
var yAxis = d3.svg.axis().scale(y).orient("left");

var area = d3.svg.area().x(function(d) { return x(d.date); })
.y0(function(d) { return y(d.y0); })
.y1(function(d) { return y(d.y0 + d.y); });

var stack = d3.layout.stack().values(function(d) { return d.values; });

var svg = d3.select("#monthly_spend_chart").append("svg")
.attr("width", width + margin.left + margin.right)
.attr("height", height + margin.top + margin.bottom)
.append("g")
.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var browsers = stack(color.domain().map(function(name) {
return { name: name, values: data.map(function(d) {
return { date: d.date, y: d[name] / 1}; }) };
}));

x.domain(d3.extent(data, function(d) { return d.date; }));
y.domain([0, d3.max(browsers, function(d) { return d3.max(d.values, function(d) { return d.y0; }); })]);

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
.text("Monthly Spend (Pound)");
	  
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
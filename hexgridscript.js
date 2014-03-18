var data = [
{topicID: "1",  words: ["migrant","asian","identity","cultural","state"], url: "http://bl.ocks.org/mbostock/4063550"},
{topicID: "2",  words: ["state","asian","identity","cultural","migrant"], url: "http://www.jasondavies.com/factorisation-diagrams/"},
{topicID: "3",  words: ["migrant","asian","identity","cultural","state"], url: "http://www.jasondavies.com/tree-of-life/"},
{topicID: "4",  words: ["migrant","asian","identity","cultural","state"], url: "http://www.jasondavies.com/maps/clip/"},
{topicID: "5",  words: ["migrant","asian","identity","cultural","state"], url: "http://bost.ocks.org/mike/miserables/"},
{topicID: "6",  words: ["migrant","asian","identity","cultural","state"], url: "http://bl.ocks.org/mbostock/3014589"},
{topicID: "7",  words: ["migrant","asian","identity","cultural","state"], url: "http://bl.ocks.org/mbostock/4063582"},
{topicID: "8",  words: ["migrant","asian","identity","cultural","state"], url: "http://www.jasondavies.com/maps/transition/"},
{topicID: "9",  words: ["migrant","asian","identity","cultural","state"], url: "http://www.nytimes.com/interactive/2013/05/25/sunday-review/corporate-taxes.html"},
{topicID: "10", words: ["migrant","asian","identity","cultural","state"], url: "http://bl.ocks.org/mbostock/4636377"}
];

//svg sizes and margins
var margin = {
    top: 150,
    right: 100,
    bottom: 20,
    left: 120
};

//The next lines should be run, but this seems to go wrong on the first load in bl.ocks.org
var windowWidth = $(window).width() - margin.left - margin.right - 20;
var windowHeight = $(window).height() - margin.top - margin.bottom - 80;

//The number of columns and rows of the grid
var MapColumns = 8,
	MapRows = 7;
	
//The maximum radius the hexagons can have to still fit the screen
var hexRadius = 100; //d3.min([windowWidth/((MapColumns + 0.5) * Math.sqrt(3)), windowHeight/((MapRows + 1/3) * 1.5)]);

//Set the new height and width of the SVG based on the max possible
var width = MapColumns*hexRadius*Math.sqrt(3);
var height = MapRows*1.5*hexRadius+0.5*hexRadius;

// add co-ords to the objects
data.forEach(function(d, i) {
  d.i = i % 10;
  d.j = i / 10 | 0;
});

d3.shuffle(data);

// select the nodes we need
var svg = d3.select("body").append("svg")
    .attr("class", "chart")
    .attr("width", windowWidth + margin.left + margin.right)
    .attr("height", windowHeight + margin.top + margin.bottom);

//Set the hexagon radius
var hexbin = d3.hexbin()
				.size([windowWidth, windowHeight])
    	       .radius(hexRadius);

// make the hexgrid
svg.append("path")
    .attr("class", "mesh")
    .attr("d", hexbin.mesh);

var anchor = svg.append("g")
      .attr("class", "wordle")
    .selectAll("a");

centers = hexbin.size([width, height]).centers();

var counter;

centers.forEach(function(center, i) {
    counter = i;
	center.j = Math.round(center[1] / (hexRadius * 1.5));
	center.i = Math.round((center[0] - (center.j & 1) * hexRadius * Math.sin(Math.PI / 3)) / (hexRadius * 2 * Math.sin(Math.PI / 3)));
	// define the wordles
	  d3.layout.cloud().size([200, 200])
	  .words(
	    data[i].words.map(function(d) {
	   return {text: d, size: 10};
	  }))
	  .padding(2)
	  .rotate(0)
	  .font("Helvetica")
	  .fontSize(function(d) { return d.size; })
	  .on("end", drawWordle)
	  .start();
});

function drawWordle(words){
	var wordle = svg.selectAll(".wordle")
		wordle.append("g")
        .attr("transform", "translate(" + counter*hexRadius+",250)")
        .selectAll("text")
        .data(words)
			.enter().append("text")
        .style("font-size", function(d) { return d.size + "px"; })
        .style("font-family", "Helvetica")
        .attr("text-anchor", "middle")
        .attr("transform", function(d) {
          return "translate(" + [d.x,d.y] + ")";
        })
        .text(function(d) { return d.text; });
}
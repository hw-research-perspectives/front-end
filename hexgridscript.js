/* Design and Code Project 2014
   Authors: Lewis Deacon, Laura McCormack, Tom Townsend and Tsz Kit Law
   JavaScript file to generate Hexgrid with Topic wordles (orignal file - HexGrid.js)
   Revision History
   Initial Creation - Lewis
   Refactored to enable multiple wordles and moved to this file - Tom
   Fixed so wordles were placed correctly in the hexagons - Laura
   Removed data from file, will be loaded before use - Simon
   Add colours to wordles based on School - Laura
   The size of hex grid svg is now fixed, minor changes to its style, minor changes to the position of the wordle - Kit
   Fix sizing of wordles - Tom
   Splice array here so can have 5 words in word cloud and 10 words on hover - Laura
   minor changes to the visual of hex grid - Kit
*/

///////////////////////////////////////////////////////////////////////////
////////////// Initiate SVG and create hexagon centers ////////////////////
///////////////////////////////////////////////////////////////////////////
//svg sizes and margins
var margin = {
    top: 120,
    right: 10,
    bottom: 20,
    left: 80
};

//The next lines should be run, but this seems to go wrong on the first load in bl.ocks.org
//var width = $(window).width() - margin.left - margin.right;
//var height = $(window).height() - margin.top - margin.bottom - 80;
//Fixed values 
var width = 2260;
var height = 1120;

//The number of columns and rows of the grid
var MapColumns = 11,
    MapRows = 9;
    
//The maximum radius the hexagons can have to still fit the screen
var hexRadius = d3.min([width/((MapColumns + 1) * Math.sqrt(3)),
            height/((MapRows + 1/3) * 1.5)]);

//Set the new height and width of the SVG based on the max possible
width = MapColumns*hexRadius*Math.sqrt(3);
heigth = MapRows*1.5*hexRadius+0.5*hexRadius;

//Set the hexagon radius
var hexbin = d3.hexbin()
               .radius(hexRadius);

//Calculate the center positions of each hexagon    
var points = [];
for (var i = 0; i < MapRows; i++) {
    for (var j = 0; j < MapColumns; j++) {
        points.push([hexRadius * j * 1.75, hexRadius * i * 1.5]);
    }//for j
}//for i

//Create SVG element
var svg = d3.select("#chart").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

///////////////////////////////////////////////////////////////////////////
////////////////////// Draw hexagons and color them ///////////////////////
///////////////////////////////////////////////////////////////////////////

//Start drawing the hexagons
svg.append("g")
    .selectAll(".hexagon")
    .data(hexbin(points))
    .enter().append("path")
    .attr("class", "hexagon")
    .attr("d", function (d) {
        return "M" + d.x + "," + d.y + hexbin.hexagon();
    })
    .attr("stroke", function (d,i) {
        return "#000";
    })
    .attr("stroke-width", "1px")
    .style("fill", function (d,i) {
        return "#fff";
    })
    ;

var anchor = svg.append("g")
      .attr("class", "wordle")
    .selectAll("a");

var centers = hexbin.size([width,height]).centers();

var hexX;
var hexY;
var urlRef;
var topicWords;
var wordleColour;

data.forEach(function(d, i) {
  counter = i;
  urlRef = data[i].url;
  hexX = (data[i].hexX)*(hexRadius*1.72);
  hexY = (data[i].hexY)*(hexRadius*1.5);
  // if hexY is an odd number need to move across an extra hexRadius
  if (data[i].hexY%2 != 0){hexX = hexX + hexRadius}

  // update the value based on number in hexagon (i.e. 1st topic within Hex, 2nd Topic witin Hex, 3rd Topic within Hex)
  if (data[i].hexNumber == 1){
    hexY = hexY - 35;
  } else if (data[i].hexNumber == 2){
    hexX = hexX - 35;
    hexY = hexY + 30;
  } else if (data[i].hexNumber == 3){
    hexX = hexX + 42;
    hexY = hexY + 30;
  }

  topicWords = data[i].words;

  wordleColour = fill(data[i].school);

  // define the wordles
	d3.layout.cloud().size([100, 75])
    .words(
      data[i].words.slice(0,5).map(function(d) {
     return {text: d, size: 11.5 + Math.random() * 3};
    }))
    .padding(0.25)
    .rotate(0)
    .font("Helvetica")
    .fontSize(function(d) { return d.size; })
    .on("end", drawWordle)
    .start();
});

function drawWordle(words){
  var wordle = svg.selectAll(".wordle")
  wordle.append("a")
        .attr("class", "words")
        .attr("xlink:href", function(d) { return urlRef; })
        .attr("xlink:title", function(d) { return topicWords; })
        .attr("transform", "translate(" + hexX +","+hexY+")")
        .selectAll("text")
        .data(words)
      .enter().append("text")
        .style("font-size", function(d) { return d.size + "px"; })
        .style("font-family", "Helvetica")
	.attr("fill", wordleColour)
        .attr("text-anchor", "middle")
        .attr("transform", function(d) {
          return "translate(" + [d.x,d.y] + ")";
        })
       .text(function(d) { return d.text; });
}

function fill(d) {
  //"#efc050", "#f3759f", "#00947e", "#0c1e3c", "#766a62", "#dc241f", "#7fcdcd" , "#FF9900", "#99FF00", "#990033"
  if (d == "Sch of Life Sciences"){
	return "#efc050";
  } 
  if (d == "Sch of Engineering and Physical Science"){
	return "#A76BCE";
  } 
  if (d == "Sch of the Built Environment"){
	return "#00947e";
  } 
  if (d == "Sch of Management and Languages"){
	return "#0c1e3c";
  } 
  if (d == "Institute Of Petroleum Engineering"){
	return "#766a62";
  } 
  if (d == "S of Mathematical and Computer Sciences"){
	return "#dc241f";
  } 
  if (d == "Technology and Research Services"){
	return "#7fcdcd";
  } 
  if (d == "Sch of Textiles and Design"){
	return "#FF9900";
  } 
  return "#99FF00";
}

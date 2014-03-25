var data = [
{topicID: "1",  words: ["health","intervention","evidence","decision","behaviour"], url: "topicgraphandwordle.php?topicID=1", hexX: 0, hexY: 0, hexNumber: 1, school: ""},
{topicID: "2",  words: ["policy","political","public","law","government"], url: "topicgraphandwordle.php?topicID=2", hexX: 0, hexY: 0, hexNumber: 2, school: ""},
{topicID: "3",  words: ["species","population","habitat","fish","community"], url: "topicgraphandwordle.php?topicID=3", hexX: 0, hexY: 0, hexNumber: 3, school: ""},
{topicID: "4",  words: ["personal","detail","sensitive","submission","gtr"], url: "topicgraphandwordle.php?topicID=4", hexX: 1, hexY: 0, hexNumber: 1, school: ""},
{topicID: "5",  words: ["universe","dark","energy","matter","gravitational"], url: "topicgraphandwordle.php?topicID=5", hexX: 1, hexY: 0, hexNumber: 2, school: ""},
{topicID: "6",  words: ["cell","stem","tissue","bone","human"], url: "topicgraphandwordle.php?topicID=6", hexX: 0, hexY: 1, hexNumber: 1, school: ""},
{topicID: "7",  words: ["protein","structure","molecule","enzyme","biological"], url: "topicgraphandwordle.php?topicID=7", hexX: 0, hexY: 2, hexNumber: 1, school: ""},
{topicID: "8",  words: ["public","summary","await","request","receive"], url: "topicgraphandwordle.php?topicID=8", hexX: 0, hexY: 3, hexNumber: 1, school: ""},
{topicID: "9",  words: ["gene","genetic","genome","sequence","species"], url: "topicgraphandwordle.php?topicID=9", hexX: 0, hexY: 4, hexNumber: 1, school: ""},
{topicID: "10", words: ["muscle","oxygen","stress","mitochondrial","damage"], url: "topicgraphandwordle.php?topicID=10", hexX: 1, hexY: 1, hexNumber: 1, school: ""}
];
///////////////////////////////////////////////////////////////////////////
////////////// Initiate SVG and create hexagon centers ////////////////////
///////////////////////////////////////////////////////////////////////////
//svg sizes and margins
var margin = {
    top: 150,
    right: 0,
    bottom: 20,
    left: 80
};

//The next lines should be run, but this seems to go wrong on the first load in bl.ocks.org
var width = $(window).width() - margin.left - margin.right;
var height = $(window).height() - margin.top - margin.bottom - 80;
//Fixed values 
//var width = 850;
//var height = 350;

//The number of columns and rows of the grid
var MapColumns = 8,
    MapRows = 5;
    
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

data.forEach(function(d, i) {
  counter = i;
  urlRef = data[i].url;
  hexX = (data[i].hexX)*(hexRadius*1.75);
  hexY = (data[i].hexY)*(hexRadius*1.5);
  // if hexY is an odd number need to move across an extra hexRadius
  if (data[i].hexY%2 != 0){hexX = hexX + hexRadius}

  // update the value based on number in hexagon (i.e. 1st topic within Hex, 2nd Topic witin Hex, 3rd Topic within Hex)
  if (data[i].hexNumber == 1){
    hexY = hexY - 35;
  } else if (data[i].hexNumber == 2){
    hexX = hexX - 30;
    hexY = hexY + 30;
  } else if (data[i].hexNumber == 3){
    hexX = hexX + 30;
    hexY = hexY + 30;
  }

  topicWords = data[i].words;

  // define the wordles
    d3.layout.cloud().size([75, 75])
    .words(
      data[i].words.map(function(d) {
     return {text: d, size: 11};
    }))
    .padding(1)
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
        .attr("text-anchor", "middle")
        .attr("transform", function(d) {
          return "translate(" + [d.x,d.y] + ")";
        })
       .text(function(d) { return d.text; });
}
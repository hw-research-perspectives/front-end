<?php

error_reporting(0);

require_once("config.inc.php");

//Database Connection
$connection = mysql_connect($dbhost, $dbuser, $dbpass);
mysql_select_db($dbname, $connection);

//Simple Query
$query = 'SELECT information.GrantRefNumber, information.GrantTitle, DATE_FORMAT(information.StartDate, "%d/%m/%Y") AS StartDate, ' .
    'DATE_FORMAT(information.EndDate, "%d/%m/%Y") AS EndDate, StartDate as StartDateRaw, EndDate AS EndDateRaw, ' .
    'information.TotalGrantValue, information.HoldingDepartmentName, ' .
    'information.HoldingOrganisationName, summary.Summary FROM information ' .
    'INNER JOIN summary ON information.GrantRefNumber = summary.GrantRefNumber ' .
    'INNER JOIN analysis ON information.GrantRefNumber = analysis.GrantRefNumber ' .
    'LIMIT 5';

$grants = array();  // Hold grants information

// Get data from MYSQL
$result = mysql_query($query) or die(mysql_error());
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $pack = array();

    $pack['GrantRefNumber'] = $row['GrantRefNumber'];
    $pack['GrantTitle'] = $row['GrantTitle'];
    $pack['WordCloudFile'] = 'http://www.researchperspectives.org/gow.grants/grant_' . str_replace('/', '', $grantRefNumber) . '.png';

    $pack['StartDate'] = substr($row['StartDate'], strpos($row['StartDate'], "/") + 1);
    $pack['EndDate'] = substr($row['EndDate'], strpos($row['EndDate'], "/") + 1);
    $pack['TotalGrantValue'] = 'Â£' . number_format($row['TotalGrantValue']);
    $pack['Link'] = 'http://gow.epsrc.ac.uk/NGBOViewGrant.aspx?GrantRef=' . $row['GrantRefNumber'];
    $pack['HoldingDepartment'] = $row['HoldingDepartmentName'];
    $pack['HoldingOrganization'] = $row['HoldingOrganisationName'];

    $pack['Summary'] = $row['Summary'];
    $pack['StartDateRaw'] = date("c", strtotime($row['StartDateRaw']));
    $pack['EndDateRaw'] = date("c", strtotime($row['EndDateRaw']));

    array_push($grants, $pack); // Push array with all the grant information to grants array
}

MYSQL_CLOSE(); // Very Important as server can only open 20 concurrent MYSQL connections.


?><!doctype html>
<html>
<style>

.subunit.SCT { fill: #ddc; }
.subunit.WLS { fill: #cdd; }
.subunit.NIR { fill: #cdc; }
.subunit.ENG { fill: #dcd; }

.subunit.IRL,
.subunit-label.IRL {
  display: none;
}

.subunit-boundary {
  fill: none;
  stroke: #777;
  stroke-dasharray: 2,2;
  stroke-linejoin: round;
}

.subunit-boundary.IRL {
  stroke: #aaa;
}

.subunit-label {
  fill: #777;
  fill-opacity: .5;
  font-size: 20px;
  font-weight: 300;
  text-anchor: middle;
}

.place,
.place-label {
  fill: #444;
}

text {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 10px;
  pointer-events: none;
}
.grant-arcs {
  display: none;
  fill: none;
  stroke: #000;
}

.grant-cell {
  fill: none;
  pointer-events: all;
}

.grant circle {
  fill: steelblue;
  stroke: #fff;
  pointer-events: none;
}

.grant:hover .grant-arcs {
  display: inline;
}

.box{
float : right;
margin-top: 350px;
margin-right : 300px;
}




</style>
<head>
    <meta charset="utf-8">
    <title>Research Perspectives: Project, Heriot-Watt University</title>
    <meta name="description" content="Research Perspectives - project"/>

    <!-- Standard header code -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1200"/>
    <link rel="stylesheet" type="text/css" media="print, screen" href="http://researchperspectives.org/style.css"/>
    <link rel="shortcut icon" href="http://www.researchperspectives.org/favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"/>
    <!--<script src="http://www.researchperspectives.org/jquery.dataTables.min.js"></script> -->
	<script type="text/javascript" src="http://d3js.org/d3.v3.min.js"></script>
	<script type="text/javascript" src="http://d3js.org/d3.hexbin.v0.min.js"></script>	
	<script type="text/javascript"src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script src="http://d3js.org/d3.v3.min.js"></script>
	<script src="http://d3js.org/queue.v1.min.js"></script>
	<script src="http://d3js.org/topojson.v1.min.js"></script>
</head>

<body>
<!-- header code -->
<?php $header_opt = 'home'; ?>
<div id="top_line"></div>
<header>
    <div style=" width:606px; display:inline-block; vertical-align:top; padding:33px 0px">
        <a href="http://www.researchperspectives.org"><img
                src="http://www.researchperspectives.org/researchperspectives.svg"
                alt="Research Perspectives - Tools for Visualisation of Portfolios"
                style="height:70px; padding-top:0px; padding-left:12px;"/></a>

    </div>
    <a href="http://www.epsrc.ac.uk/" alt="EPSRC Web" target="_blank"><img alt="EPSRC logo"
                                                                           style="height:54px; padding-left:220px; padding-top:40px;"
                                                                           src="http://researchperspectives.org/epsrc.svg"/></a>
</header>
<div style="width:100%; height:40px; background-color:#00947E">
    <nav>
        <ul>
            <li <?php if ($header_opt == 'home') echo 'style="background-color:#68003F"'; ?>><a
                    href="http://www.researchperspectives.org/">Home</a></li>
            <li <?php if ($header_opt == 'tools') echo 'style="background-color:#68003F"'; ?>><a
                    href="http://www.researchperspectives.org/tools/">Research Tools</a></li>
            <li <?php if ($header_opt == 'meetings') echo 'style="background-color:#68003F"'; ?>><a
                    href="http://www.researchperspectives.org/meetings.php">Project Meetings</a></li>
            <li <?php if ($header_opt == 'about') echo 'style="background-color:#68003F"'; ?>><a
                    href="http://www.researchperspectives.org/about/">About Perspectives</a></li>
            <li <?php if ($header_opt == 'blog') echo 'style="background-color:#68003F"'; ?>><a
                    href="http://www.researchperspectives.org/blog/">Development Blog</a></li>
            <li <?php if ($header_opt == 'contact') echo 'style="background-color:#68003F"'; ?>><a
                    href="http://www.researchperspectives.org/contact/">Contact Us</a></li>
        </ul>
    </nav>
</div>

<?php

if ($header_source == 'EPSRC') {
    $s_color = '#00947e';
    $s_line_1 = "EPSRC Database";
    $s_line_2 = "Source RCUK EPSRC Data";
    $s_g_to = '#20b49e';
}
if ($header_source == 'RCUK') {
    $s_color = '#1D2767';
    $s_line_1 = "RCUK Database";
    $s_line_2 = "Source GTR API Data";
    $s_g_to = '#3D5797';
}

if ($header_source != '') {
    echo '<a href="http://www.researchperspectives.org/about/#open_dataa" alt="More about open data..."><div style="z-index: 1000;' . //transform-origin: 0% 0%; transform: rotate(-90deg);
        //-webkit-transform: rotate(-90deg); /* Safari and Chrome */
        //-webkit-transform-origin:0% 0%; /* Safari and Chrome */
        'background-color: ' . $s_color . '; border-radius: 6px;
        height: 31px; width:143px;
        position: fixed; left: 8px; bottom:8px;
         border-top: 1px solid #999999;
        border-right: 1px solid #666666;
        border-bottom: 1px solid #333333;
         border-left: 1px solid #666666;
         background-image: linear-gradient(to left, ' . $s_g_to . ', ' . $s_color . ');">
        <p style="padding: 4px 12px; color:#ffffff; font-size: 12px; line-height: 7px; text-align: center ">' . $s_line_1 . '</p>
        <hr style="background-color: #ffffff; height: 1px; border: 0;" />
        <p style="padding: 0px 12px; color:#ffffff; font-size: 9px; line-height: 15px; text-align: center ">' . $s_line_2 . '</p></div></a>';
}
?>




<section class="grey">
    <div class="content">
       
    </div>
</section>

<section class="white">
    <div class="content">
		<script>

var width = 850,
    height = 1100;

var projection = d3.geo.albers()
    .center([0, 55.4])
    .rotate([4.4, 0])
    .parallels([50, 60])
    .scale(1200 * 4.5)
    .translate([450,550]);

var path = d3.geo.path()
    .projection(projection)
    .pointRadius(2);

var voronoi = d3.geom.voronoi()
    .x(function(d) { return d.x; })
    .y(function(d) { return d.y; })
    .clipExtent([[0, 0], [width, height]]);

var svg = d3.select("body").append("svg")
    .attr("width", width)
    .attr("height", height);

queue()
    .defer(d3.json, "uk.json")
    .defer(d3.csv, "../data.php?query=allLocations&format=csv")
    .defer(d3.csv, "../data.php?query=topicLocations&topicID=<?php echo $_GET['topicID']; ?>&format=csv")
    .await(ready);

function ready(error, uk, universities, links) {
var subunits = topojson.feature(uk, uk.objects.subunits);
places = topojson.feature(uk, uk.objects.places);
  var grantById = d3.map(),
      positions = [];
	  
	   svg.selectAll(".subunit")
      .data(subunits.features)
    .enter().append("path")
      .attr("class", function(d) { return "subunit " + d.id; })
      .attr("d", path);

  svg.append("path")
      .datum(topojson.mesh(uk, uk.objects.subunits, function(a, b) { return a !== b && a.id !== "IRL"; }))
      .attr("d", path)
      .attr("class", "subunit-boundary");

  svg.append("path")
      .datum(topojson.mesh(uk, uk.objects.subunits, function(a, b) { return a === b && a.id === "IRL"; }))
      .attr("d", path)
      .attr("class", "subunit-boundary IRL");

  svg.selectAll(".subunit-label")
      .data(subunits.features)
    .enter().append("text")
      .attr("class", function(d) { return "subunit-label " + d.id; })
      .attr("transform", function(d) { return "translate(" + path.centroid(d) + ")"; })
      .attr("dy", ".35em")
      .text(function(d) { return d.properties.name; });

  svg.append("path")
      .datum(places)
      .attr("d", path)
      .attr("class", "place");

  svg.selectAll(".place-label")
      .data(places.features)
    .enter().append("text")
      .attr("class", "place-label")
      .attr("transform", function(d) { return "translate(" + projection(d.geometry.coordinates) + ")"; })
      .attr("x", function(d) { return d.geometry.coordinates[0] > -1 ? 6 : -6; })
      .attr("dy", ".35em")
      .style("text-anchor", function(d) { return d.geometry.coordinates[0] > -1 ? "start" : "end"; })
      .text(function(d) { return d.properties.name; });

  universities.forEach(function(d) {
    grantById.set(d.iata, d);
    d.outgoing = [];
    d.incoming = [];
  });

  links.forEach(function(link) {
    var source = grantById.get(link.origin),
        target = grantById.get(link.destination),
		num = grantById.get(link.count),
        link = {source: source, target: target};
    source.outgoing.push(link);
    target.incoming.push(link);
  });

  universities = universities.filter(function(d) {
    if (d.count = Math.max(d.incoming.length, d.outgoing.length)) {
      d[0] = +d.longitude;
      d[1] = +d.latitude;
      var position = projection(d);
      d.x = position[0];
      d.y = position[1];
      return true;
    }
  });

  voronoi(universities)
      .forEach(function(d) { d.point.cell = d; });


  var grant = svg.append("g")
      .attr("class", "fullGrant")
    .selectAll("g")
      .data(universities)
    .enter().append("g")
      .attr("class", "grant");

  grant.append("path")
      .attr("class", "grant-cell")
	  //This length isnt correct
      .attr("d", function(d) { return d.cell.length ? "M" + d.cell.join("L") + "Z" : null; })
	  .on("mouseover", function(d, i) { d3.select(".orgList").html(d.name.split(",").map(function(d){return "<li>"+d+"</li>" })); })
	  .on("mouseout", function(d, i) { d3.select(".orgList").text(""); });
	  

  grant.append("g")
      .attr("class", "grant-arcs")
    .selectAll("path")
      .data(function(d) { return d.outgoing; })
    .enter().append("path")
      .attr("d", function(d) { return path({type: "LineString", coordinates: [d.source, d.target]}); });

  grant.append("circle")
      .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
      .attr("r", function(d) { return d.count; });
	  
	
	  

}
</script>
</div>

<div class="box">
<h2 class="listTitle">Organisation List</h2><br/>
<ul class="orgList">
</ul>
</div>

</section>


<!-- footer -->
<div class="footer">
    <footer>
        <div style="padding: 33px 0px; width:840px; display:inline-block; vertical-align:top;">
            <a href="http://www.researchperspectives.org"><img
                    src="http://www.researchperspectives.org/researchperspectives_white.svg"
                    alt="Research Perspectives - Tools for Visualisation of Portfolios"
                    style="height:70px; padding-top:0px; padding-left:0px;"/></a>
        </div>
        <div style="display:inline-block;"><a href="http://www.epsrc.ac.uk/" alt="EPSRC Web" target="_blank"><img
                    alt="EPSRC Logo" style="height:54px; margin-top:0px; padding-top:40px;"
                    src="http://researchperspectives.org/epsrc_wo.svg"/></a></div>
        <div class="footer_box_left">
            <p style="color:#FFF">This project will provide new ways of viewing EPSRC's ICT (Information and
                Communications Technology) portfolio to enable researchers, EPSRC staff and other stakeholders to better
                contribute to ICT strategy.</p>
        </div>
        <div class="footer_box">
            <p><strong><a href="http://www.researchperspectives.org/tools/">Research Tools</a></strong></p>
            <ul>
                <li><a href="http://www.researchperspectives.org/tools/themes">Browse Portfolio</a></li>
                <li><a href="http://www.researchperspectives.org/tools/researchareas">Browse by Research Area</a></li>

                <li><a href="http://www.researchperspectives.org/tools/person_search">Person Search</a></li>
                <li><a href="http://www.researchperspectives.org/tools/term_search">Term Search</a></li>
                <li><a href="http://www.researchperspectives.org/search_similar">Search by Abstract</a></li>

                <li><a href="http://www.researchperspectives.org/tools/topics">Topic Browser</a></li>
                <li><a href="http://www.researchperspectives.org/tools/topic_plotter.php">Topic Plotter</a></li>
            </ul>
        </div>
        <div class="footer_box">
            <p><strong><a href="http://www.researchperspectives.org/meetings.php">Project Meetings</a></strong></p>
            <ul>
                <li><a href="http://www.researchperspectives.org/meetings/meetingJuly2013/">2nd NEMODE Community
                        Meeting, London, 07/2013 </a></li>
                <li><a href="http://www.researchperspectives.org/meetings/meetingti3december2012/">EPSRC TI3,
                        Birmingham, 12/2012</a></li>
                <li><a href="http://www.researchperspectives.org/meetings/meetingSeptember2012/">EPSRC NoN & ICT-P
                        Meeting, London, 09/2012</a></li>
                <li><a href="http://www.researchperspectives.org/meetings/meetingMar2012/">EPSRC Working Together,
                        Birmingham, 03/2012</a></li>
                <li><a href="http://www.researchperspectives.org/meetings/meetingNov2011/">ICTP & NoN, London,
                        11/2011</a></li>
            </ul>
        </div>
        <div class="footer_box_right">
            <p><strong><a href="http://www.researchperspectives.org/about">About Perspectives</a></strong></p>
            <ul>
                <li><a href="http://www.researchperspectives.org/about">Description</a></li>
                <li><a href="http://www.researchperspectives.org/about">Steering Committee</a></li>
                <li><a href="http://www.researchperspectives.org/about">Further Information</a></li>
            </ul>
            <br/>

            <p><strong><a href="http://www.researchperspectives.org/blog/">Development Blog</a></strong></p>
            <br/>

            <p><strong><a href="http://www.researchperspectives.org/contact">Contact Us</a></strong></p>
        </div>
    </footer>
</div>
<div class="footer_end">
    <p style="text-align:center; color:#FFF; padding-top:10px; font-size:12px;">
        <a style="color:#ffffff" href="http://www.researchperspectives.org" alt="Research Perspectives">Research Perspectives</a>, <a style="color:#ffffff"
                                 href="http://www.researchperspectives.org/grant/EP/I038845/1_ICT-Perspectives--an-ICT-Next-Decade-Proposal-"
                                 alt="ICT Perspectives">EPSRC Grant Number EP/I038845/1</a>, Copyright &copy; 2011-2013,
        <a
            style="color:#ffffff" href="http://www.hw.ac.uk" alt="Heriot-Watt University" target="_blank">Heriot-Watt
            University</a>.</p>
</div>

<!-- AddThis Smart Layers BEGIN -->
<!-- Go to http://www.addthis.com/get/smart-layers to customize -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51efd599174b2982"></script>
<script type="text/javascript">
    addthis.layers({
        'theme': 'transparent',
        'share': {
            'position': 'left',
            'numPreferredServices': 5,
            'services': 'twitter,linkedin,email,facebook,google_plusone_share'
        }
    });
</script>
<!-- AddThis Smart Layers END -->



</body>
</html>
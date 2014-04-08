<?php 
if (isset($_GET['topicID'])) {
	$topicID = $_GET['topicID'];
}
else {
	$topicID = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Topic Details</title>
<link rel="stylesheet" type="text/css" media="print, screen" href="http://researchperspectives.org/style.css"/>
<script type="text/javascript" src="http://d3js.org/d3.v3.min.js"></script>
<script type="text/javascript" src="D3/d3.layout.cloud.js"></script>
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
  fill: none;
}
</style>
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
<section class="grey">
	<div style="padding:33px 0px;" class="content">
		<h1>Topic <?php echo $topicID; ?></h1>
	</div>
</section>
<section class="white">
	<div style="padding-bottom:33px 0px;" class="content">
		<p id="wordle_chart">
			
		</p>
	</div>
</section>
<section class="grey">
	<div style="padding-bottom:33px 0px;" class="content">
		<p id="total_spend_chart">

		</p>
	</div>
</section>
<section class="white">
	<div style="padding-bottom:33px 0px;" class="content">
		<p id="monthly_spend_chart">

		</p>
	</div>
</section>
<section class="grey">
	<div style="padding-bottom:33px 0px;" class="content">
        <h2>Topic Contribution Map</h2>
		<a href="MAP/semiVoronoiMap.php?topicID=<?php echo $topicID; ?>">Link to Topic Contribution Map for Topic <?php echo $topicID; ?></a>
	</div>
</section>

<section class="white">
    <div style="padding-bottom:33px 0px;" class="content">
        <table>
            <thead>
                <tr><th>Grant Ref Number</th><th>Grant Title</th><th>Department</th></tr>
            </thead>
            <tbody>
<?php
  require_once("config.inc.php");
  $db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
  $grantsQuery = $db->prepare("SELECT GrantRefNumber, GrantTitle, OrganisationDepartment FROM vw_hw_grants i INNER JOIN topicmap_grants_100 t on i.ID = t.ID WHERE topicId = :topicID;");
  $grantsQuery->execute(array(":topicID" => $topicID));
  
  foreach ($grantsQuery->fetchAll(PDO::FETCH_ASSOC) as $row)
  {
    echo '<tr><td>' . $row['GrantRefNumber'] . '</td><td>' . $row['GrantTitle']  .'</td><td>' . $row['OrganisationDepartment'] . '</td></tr>';
  }
  
?>
            </tbody>
        </table>
        </div>
</section>

<?php require_once("svgCharts.php"); ?>
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
        <!--<a style="color:#ffffff" href="http://www.researchperspectives.org" alt="Research Perspectives">Research Perspectives</a>, <a style="color:#ffffff"
                                 href="http://www.researchperspectives.org/grant/EP/I038845/1_ICT-Perspectives--an-ICT-Next-Decade-Proposal-"
                                 alt="ICT Perspectives">EPSRC Grant Number EP/I038845/1</a>, -->Copyright &copy; 2011-2014,
        <a
            style="color:#ffffff" href="http://www.hw.ac.uk" alt="Heriot-Watt University" target="_blank">Heriot-Watt
            University</a>.</p>
</div>
</body>
</html>

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




?><!doctype html>
<html>
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
	<script type="text/javascript" src="D3/d3.layout.cloud.js"></script>
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
        <h1 style="text-align: center">Header 1</h1>


    </div>
</section>

<section class="white">
    <div class="content">
		<div id="chart"></div>
<script type="text/javascript" src="D3/wordleGenerator.js"></script>
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
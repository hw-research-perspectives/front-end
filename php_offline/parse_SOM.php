<?php
/* Design and Code Project 2014
 *  The same php as the somdata.php except a file path change.
 *  It’s for testing and integration purpose
 */

// helper class
class topic
{
    public $topicId;   
    public $words;   
    public $school;
    public $url;
    public $hexX;
    public $hexY;
    public $hexNumber;

    /**
     * Summary of __construct
     * @param mixed $topic Topic ID
     * @param mixed $x X position in grid
     * @param mixed $y Y position in grid
     * @param mixed $t Topic number in hex
     */
    public function __construct($topic, $x, $y, $t)
    {
        global $topicStatement;
        
        $topicStatement->execute(array(":id" => $topic, ":id2" => $topic)); // execute with parameters
        $data = $topicStatement->fetch(PDO::FETCH_ASSOC);
        $topicStatement->closeCursor(); // IMPORTANT! Without closing the refcursor you won't free up resources or the pointer to be reused for the next execution.
        
        $this->words = explode(" ", trim($data['TopicLabel']));
        $this->topicId = $topic;
        $this->hexX = $x;
        $this->hexY = $y;
        $this->hexNumber = $t;
        $this->url = "display.php?topicID=" . $topic;
        $this->school = $data['OrganisationDepartment']; // FIXME
    }
}

set_time_limit(60);

require_once("config.inc.php");

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// disable prepared statement emulation
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// start a transaction so any database edits we make are invisible to other sessions.
$db->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
$db->beginTransaction();

// materialise the view so we can do faster lookups
// this is only visible to our session because of the transaction, and is PK'd to our connection so we don't get blocking inserts.
$db->exec("insert into tmp_vw_hw_topics_100 SELECT *, connection_id() FROM vw_hw_topics_100;");

// prepare the statement once, execute multiple times. It's much faster that way cos you only have to send the new values to the server.
// where clause uses the entire primary key so we can use the index, but the last condition is pointless for data filtering.
$topicStatement = $db->prepare("SELECT t.TopicLabel, td.OrganisationDepartment FROM tmp_vw_hw_topics_100 as t INNER JOIN vw_topic_depts as td ON td.TopicId = t.topicId WHERE t.TopicID = :id AND connection = connection_id() AND td.DeptGrant = ( SELECT MAX(DeptGrant) FROM vw_topic_depts WHERE topicid = :id2 );");

// read file
$file = file_get_contents('..\SIM_and_SOM\SOM.csv');

$topics = array();

$y = 0;
foreach(explode("\n", $file) as $line)
{
    $x = 0;
    foreach(explode(",", $line) as $cell)
    {
        if($cell != "[]")
        {
            $t = 1;
            foreach(explode(" ", $cell) as $topic)
            {               
                if( $topic == "" ) continue;
                
                $topics[] = new topic($topic, $x, $y, $t);
                $t++;
            }
        }
        
        $x++;
    }
    
    $y++;
}

// roll back our database edits
$db->rollBack();

header('Content-Type: text/javascript');
echo "var data = ";
echo json_encode($topics);
echo ";";
// end.
die();
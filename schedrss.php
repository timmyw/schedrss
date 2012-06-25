<?
require_once 'config.php';
require_once 'class_rss_generator.inc.php';
require_once('/usr/share/php-getid3/getid3.php');

function get_files($dir, $suffix, &$ar) {
    $suflen=strlen($suffix);
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if (substr($entry, strlen($entry)-$suflen, $suflen)==$suffix) {
                array_push($ar, $entry);
            }
        }
    }
    sort($ar);
}

$items=array();
$files=array();
get_files($CONFIG['datadir'], '.mp3', $files);
$rss=new rss_generator($CONFIG['title']);

$cnt=0;

$curdate=$CONFIG['startdate'];
$now=new DateTime('now');
$cnt=date_diff($curdate, $now)->format('%a');

/*
print_r($cnt);
exit(-1);
*/
$epcount=1;
foreach ($files as $f) {
    //    $id3=id3_get_tag($CONFIG['datadir'].'/'.$f);
    $id3=new getID3;
    $info=$id3->analyze($CONFIG['datadir'].'/'.$f);
    $v1=$info['tags']['id3v1'];
    //print_r($v1);
    $episode=array();
    print $v1['title']."\n";
    $episode['title']=$v1['title'][0];
    $episode['description']='Episode '.$epcount++;
    $episode['link']=$CONFIG['webdir'].'/'.$f;
    $episode['pubDate']=$curdate->format('U');
    array_push($items, $episode);
    //exit(-1);

    $cnt--;
    if ($cnt<0)
        break;
    $curdate->add(new DateInterval('P1D'));
}
file_put_contents($CONFIG['rssfile'], $rss->get($items));
?>

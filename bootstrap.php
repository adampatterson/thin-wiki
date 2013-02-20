<?
/**
Originally base on code https://github.com/am2064/Minty-Wiki
*/

use \Michelf\Markdown;
require_once "assets/markdown.php";

require_once "assets/textile.php";

/**
@todo: home.extension ( folder index )
@todo: .htaccess for tidy URL's
*/

define('BASE', 'content' );

define('EDIT', TRUE );
define('BACKUP', FALSE );

define('NAME', 'Tentacle CMS');

if ( dirname($_SERVER['PHP_SELF'])  == '/' ):
    $directory = '';
else:
    $directory = dirname($_SERVER['PHP_SELF']);
endif;

define('BASE_URI'      , $_SERVER['REQUEST_URI'] );

define('BASE_URL'      ,'http://'.$_SERVER["SERVER_NAME"].$directory.'/' );


$ignore = array(
		"assets",
	);

$user_nav=array(
/*
		Users can place their own header entries here if they wish for them to be after HOME and EDIT
		"link"=>"url"

		Some manuals for PHP Markdown Extra have been included as an example
*/
	);

$navigation = array( 
		"HOME"=>".",
	);

if(isset($_GET['entry']))
{
	if( !isset($_GET['edit']) && EDIT )
		$navigation += array( "EDIT" => curl_page_url()."&edit=true");
	else if( !isset( $_GET['edit'] ) && !EDIT)
		$navigation += array( "SOURCE" => curl_page_url()."&edit=true");
}

$navigation = array_merge($navigation,$user_nav);



class input
{
    public static function post($field)
    {
        return (isset($_POST[$field])) ? $_POST[$field] : false;
    }

    public static function get($field)
    {
        return (isset($_GET[$field])) ? $_GET[$field] : false;
    }

    public static function cookie($field)
    {
        return (isset($_COOKIE[$field])) ? $_COOKIE[$field] : false;
    }

    public static function files($field)
    {
        return (isset($_FILES[$field])) ? $_FILES[$field] : false;
    }

    public static function request($field)
    {
        return (isset($_REQUEST[$field])) ? $_REQUEST[$field] : false;
    }
}


function string_to_parts($file) {

    $file_parts = explode('/', $file);

    $file_name = end($file_parts);

    $path_parts = array();
    foreach ($file_parts as $key => $value) {
        if ($file_name != $value) {
            $path_parts[] = $value;
        }
    }

    $file_path = '';
    foreach ($path_parts as $part) {
        $file_path .= $part.'/';
    }

    $file_clean['path'] 			= $file_path;
    $file_clean['name'] 			= $file_name;
    $file_clean['full'] 			= $file_path.$file_name;

    $name_parts 					= explode('.', $file_name );

    $file_clean['file_name'] 		= $name_parts[0];
    $file_clean['extension'] 		= $name_parts[1];

    return $file_clean;
}


function curl_page_url() 
{
	$page_url = 'http';
	$page_url .= "://";
	
	if ($_SERVER["SERVER_PORT"] != "80")
		$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else
		$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

	return $page_url;
}

function read_directory( $dir, $ignore, $level=0)
{
    global $url;

    $not_dirs = array();

    echo "<ul class='category' id='$level'>\n";

    if ( $handle = opendir( $dir ) )
    {
        while ( false !== ( $entry = readdir( $handle ) ) )
        {
            if(!preg_match('/^\..*/',$entry))
            {
                if (is_dir("$dir/$entry") and !in_array($entry,$ignore))
                {
                    $level+=1;
                    echo "<li><h4>$entry</h4></li>\n";
                    read_directory("$dir/$entry",$ignore,$level);
                }
            }
            if (preg_match('/^.*\.(md|MD|markdown|MarkDown|textile|text|Text|TEXT|txt|TXT)$/',$entry))
            {
                $base = BASE_URL;

                $entryURL='entry='.urlencode($dir).'/'.urlencode($entry);
                array_push($not_dirs, "<li><h><a href=$base.?".htmlentities($entryURL).">$entry</a><h></li>\n");
            }
        }

        sort($not_dirs);

        $not_dirs=array_reverse($not_dirs);

        while($entryExt=array_pop($not_dirs))
            echo $entryExt;

        closedir($handle);
    }
    echo "</ul>\n";
}

function read_dir_file($dir,$ignore)
{
    $valid_entry = input::get('entry');
    if (strpos($valid_entry, '..') !== false) {
        // entry is invalid, let's show them the main page
        read_directory('.',$ignore);
    } else {
        // entry is valid, carry on
        $file = $dir."/".$valid_entry;
        $mark = fopen($file,"r");

        $content = fread($mark,filesize("$file"));


        $parts = string_to_parts($file);

        switch ($parts['extension']) {
            case 'textile':
                $textile = new Textile();
                echo $textile->TextileThis($content);
                break;
            default:
                echo Markdown::defaultTransform( $content );
                break;
        }

        fclose($mark);
    }
}

function read_dir_file_raw($dir)
{
    $file = "$dir"."/".$_GET['entry'];
    $mark = fopen($file,"r");

    echo fread($mark,filesize("$file"));

    fclose($mark);
}

function update_article( $article, $update )
{

    if(BACKUP)
    {

        $file = fopen($article,"r");

        $fileContents = fread($file,filesize("$article"));

        $new_file = fopen("$article".date("dmYHi"),"w");

        if( is_writeable( "$article".date( "dmYHi" ) ) )
        {
            if(fwrite($new_file,$fileContents) === FALSE)
            {

                echo "Could not write $article backup.<br>";
                exit;

            } else {
                echo "$article has been backed up.<br>";
            }
        } else {
            echo "Could not write to $article".date("dmYHi").".<br>";
        }

        fclose($file);
        fclose($new_file);
    }

    $file = fopen($article,"w");

    if(is_writeable("$article"))
    {
        if(fwrite($file,$update) === FALSE)
        {
            ?><div class="alert alert-warning">
            <a class="close" data-dismiss="alert" href="#">&times;</a>
            <strong>Error</strong> Could not update <em>$article</em>.
            </div><?

            exit;
        }else{

            ?><div class="alert alert-success">
                <a class="close" data-dismiss="alert" href="#">&times;</a>
                <strong>Success</strong> <em><?=$article?></em> has been updated.
            </div><?
        }

    }else{

        ?><div class="alert alert-warning">
        <a class="close" data-dismiss="alert" href="#">&times;</a>
        <strong>Error</strong> Could not write to <em>$article</em>.
        </div><?
    }

    fclose($file);
}

function get_request_url()
{
    // Get the filename of the currently executing script relative to docroot
    $url = (empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '/';

    // Get the current script name (eg. /index.php)
    $script_name = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : $url;

    // Parse URL, check for PATH_INFO and ORIG_PATH_INFO server params respectively
    $url = (0 !== stripos($url, $script_name)) ? $url : substr($url, strlen($script_name));
    $url = (empty($_SERVER['PATH_INFO'])) ? $url : $_SERVER['PATH_INFO'];
    $url = (empty($_SERVER['ORIG_PATH_INFO'])) ? $url : $_SERVER['ORIG_PATH_INFO'];

    // Check for GET __dingo_page
    $url = (input::get('__wiki_page')) ? input::get('__wiki_page') : $url;

    //Tidy up the URL by removing trailing slashes
    $url = (!empty($url)) ? rtrim($url, '/') : '/';

    return $url;
}

function render_page ()
{
    global $ignore;

    $url = curl_page_url();

    if( input::post('article')):

        if( input::post('update') ):
            $article    = input::post( 'article' );
            $update     =  input::post( 'update' );
            update_article( $article, $update );
        endif;

    endif;

    if ( !input::get('edit') ):

        ?><div  class="span4"><?
        #if ( input::get( 'entry' ) == '' )
            read_directory( BASE, $ignore );

        ?></div><div  class="span8"><?
        if ( input::get( 'entry' ) ):

            if ( strpos( input::get( 'entry' ), 'index.php') === false )
                read_dir_file('.',$ignore);

        endif;
        ?></div><?
    endif;


	if ( input::get( 'edit' ) ):

		$valid_entry = input::get( 'entry' );

		if (strpos($valid_entry, '..') !== false):
		// entry is invalid, let's show them the main page
			read_directory(BASE, $ignore);
		else: ?>
			<form action="." method="post">
				<input type="hidden" name="article" value="<?echo input::get( 'entry' ); ?>" >
				<textarea name="update" rows="25" cols="100" class="field span12" <? if(!EDIT) echo "disabled";?>><? read_dir_file_raw('.');?></textarea>
				<br/>
				<? if(EDIT):?>
				<input type="submit" value="Submit">
				<? endif; ?>
			</form>
	<? 	endif;
	endif;
	}
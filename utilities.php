<?
require_once 'config.php';
require_once '/home/oc/public_html/recaptcha/recaptchalib.php';

function getRecaptchaCode()
{
		global $recaptcha_public_key;
		return recaptcha_get_html( $recaptcha_public_key );
}

function recaptchaCheck()
{
	global $recaptcha_private_key;
	$resp = recaptcha_check_answer ( $recaptcha_private_key,
				                                 $_SERVER["REMOTE_ADDR"],
				                                 $_POST["recaptcha_challenge_field"],
				                                 $_POST["recaptcha_response_field"] );
				
				if ( !$resp->is_valid )
				{
					renderError( "Recaptcha failed! (".$resp->error.")" );
					return 0;
				}
				else
				{
					return 1;
				}
}

function redirect( $redirect_page )
{
    global $no_redirects;
    
    if( !$no_redirects )
    {
        echo "<script type='text/javascript'>
            <!--
            window.location = '".$redirect_page."'
            //-->
            </script>";
    }
    //debug( "REDIRECT", $redirect_page );
}

function renderError( $error_message )
{
	echo "<div class='error'>ERROR: ".$error_message."</div>";
}

function mysqlGetSingleValue( $query, $database=null )
{
    global $mysql_debug;

        if( !$database )
	{
		$database = connectToDatabase();
	}

	$result = mysqlQuery( $query, $database );
	
	if( !$result || mysql_num_rows( $result ) != 1 || mysql_error() )
	{
		renderError( "MySQL; ".mysql_error() );
		return 0;
	}
	
	$result_array = mysql_fetch_array( $result );
	
	if( !$result_array || mysql_error() )
	{
		renderError( "MySQL; ".mysql_error() );
		return 0;
	}

        if( $mysql_debug )
        {
            debug( "DONE; result=".$result_array[0] );
        }
	
	return ( $result_array[0] );
}

function mysqlQuery( $query, $database=null )
{
    global $mysql_debug;

    if( $mysql_debug )
    {
        debug( "DOING QUERY", $query );
    }
    
    if( !$database )
    {
            $database = connectToDatabase();
    }

    $result = mysql_query( $query, $database );

    if( mysql_error() )
    {
            renderError( "MySQL; ".mysql_error() );
            return 0;
    }

    if( $mysql_debug )
    {
        debug( "DONE" );
    }

    return $result;
}

function connectToDatabase()
{
	global $database_server, $database_username, $database_password, $database_name;
	
	$database = mysql_connect( $database_server, $database_username, $database_password );
		
	if( !$database )
	{
		renderError( "Database Server Unreachable" );
		return 0;
	}
	
	$query_result = mysql_query( "USE ".$database_name, $database );
		
	if( !$query_result )
	{
		renderError( "Database Unreachable" );
		return 0;
	}
	
	$query_result = mysql_query( "SET NAMES 'utf8'" );
	
	if( !$query_result )
	{
		renderError( "Unable to set charset" );
		return 0;
	}
	
	return $database;
}

function debug( $name, $value=null )
{
	if( $value )
	{
		echo $name." = '".$value."'<br/>";
	}
	else
	{
		echo "### ".$name." ###<br/>";
	}
}

function debugArray( $name, $array )
{
	echo $name." = ";
	print_r( $array );
	echo "<br/>";
}

function addSpacing()
{
	global $spacing;
	
	for( $x = 0; $x < $spacing; $x++ )
	{
		echo "<br/>";
	}
}


function mysql_fetch_alias_array($result)
{
    if (!($row = mysql_fetch_array($result)))
    {
        return null;
    }

    $assoc = Array();
    $rowCount = mysql_num_fields($result);

    for ($idx = 0; $idx < $rowCount; $idx++)
    {
        $table = mysql_field_table($result, $idx);
        $field = mysql_field_name($result, $idx);
        $assoc["$table.$field"] = $row[$idx];
    }

    return $assoc;
}

function validate_email($value) {
   $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZ".
                 "abcdefghijklmnopqrstuvwxyz0123456789@._";
   $at=0;  /* at sign */
   $dot=0; /* dot after at */
   $end_pos = strlen($value)-1;

   for ($i=0;$i<strlen($value);$i++) {
      $c = $value[$i];
      if (stripos($chars, $c)===false) return false;
      if ($c=="@") $at++;
      if ($at==1 && $c==".") $dot++;
      if ($at>1) return false;
      /* Don't start or end with an '@' or a '.'
       * No @'s or .'s next to each other.
       */
      if (($c=="." || $c=="@") &&
             ($i==0 || $i==$end_pos || $prev_c=="." || $prev_c=="@"))
              return false;
      $prev_c=$c;
   }
   return ($at==1 && $dot>0);
}

?>
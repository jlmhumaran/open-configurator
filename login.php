<?
require_once 'account.php';
require_once 'utilities.php';
require_once 'config.php';


$_GET = array_map( 'mysql_real_escape_string', $_GET );
$_POST = array_map( 'mysql_real_escape_string', $_POST );

function isLoggedIn()
{
    return checkLogin( $_SERVER['PHP_SELF'], $_POST );
}

function checkLogin( $referer, $post )
{
    if( isset( $_SESSION['username'] ) )
    {
        return 1;
    }

    return doLogin( $referer, $post );
}

function doLogin( $referer_in, $post )
{	
    extract( $post );

    if( $submit_login )
    {
				if( !recaptchaCheck() )
				{
					return 0;
				}

				$database = connectToDatabase();
        $account = new Account( $username );

        if( $account->checkPassword( $password ) )
        {
            session_name( $username );
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $account->getDatabaseID();

            if( $referer )
            {
                doRedirect( $referer );
            }
            else
            {
                renderError( "Cannot redirect you to the proper place.  Please press the back button and try again." );
                return 0;
            }
        }
        else
        {
            renderError( "Your password is incorrect.  Please try again" );
            return 0;
        }
    }
    else
    {
        renderError( "You need to login to do that." );
        displayLoginForm( $referer_in );
        return 0;
    }
}

function doRedirect( $redirect_in )
{
    global $root_web_directory;

    redirect( $root_web_directory.$redirect_in );

    return 1;
}

function displayLoginForm( $referer_in )
{
	echo "<form method='post' action='".$referer_in."'>";
	echo "<table>";
	echo "<tr><td>Username:</td><td><input type='text' name='username' /></td></tr>";
	echo "<tr><td>Password:</td><td><input type='password' name='password' /></td></tr>";
	echo "<tr><td></td><td>".getRecaptchaCode()."</td></tr>";
	echo "<tr><td><input type='submit' value='Submit' /></td><td><input type='hidden' name='submit_login' value='1' /><input type='hidden' name='referer' value='".$referer_in."'></td></tr></table></form>";
	echo "<br/><br/><a href='create_account.php'>Don't have an account?  Click here to create one.</a>";
	addSpacing();
}
?>

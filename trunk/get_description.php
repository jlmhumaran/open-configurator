<?
require_once 'utilities.php';

if( count( sscanf( $_SERVER['HTTP_REFERER'], "http://oc.ericneill.com/%s.php" ) ) > 0 )
{
    if( !is_numeric( $_GET['id'] ) )
    {
        //renderError( "ID must be an integer!" );
    }
    else if( isset( $_GET['id'] ) && $_GET['id'] > 0 )
    {
        echo displayComponentDescription( $_GET['id'] );
    }
}
else
{
    renderError( "Bad referrer!" );
}

function displayComponentDescription($component_id_in)
{
    global $tables;

    $query = "SELECT description FROM ".$tables['components']." WHERE id=".$component_id_in;
    $result = mysqlGetSingleValue( $query );
    return( $result );
}
?>
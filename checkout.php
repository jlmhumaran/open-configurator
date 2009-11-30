<?php
require_once 'utilities.php';
require_once 'header.php';
require_once 'login.php';
require_once 'customer_functions.php';

$_GET = array_map( 'mysql_real_escape_string', $_GET );
$_POST = array_map( 'mysql_real_escape_string', $_POST );

if( isLoggedIn() && $_POST['CO'] )
{
    displayShippingInfo( $_POST['order_id'] );

}
else if( isLoggedIn() && $_POST['ship_submit'] )
{

    if( !$_POST['last_name'] )
    {
            renderError( "You must enter a last name." );
            $my_error++;
    }

    if( !$_POST['first_name'] )
    {
            renderError( "You must enter a first name." );
            $my_error++;
    }

    if( strlen( $_POST['middle_initial'] ) > 1 )
    {
            renderError( "You may not have more than one letter for middle initial" );
            $my_error++;
    }

    if( !validateZipCode( $_POST['zip_code'] ) )
    {
        renderError("error");
        $my_error++;
    }
    if( !$_POST['street_address'] )
    {
        renderError("error2");
        $my_error++;
    }
    if( !$_POST['city'] )
    {
	renderError( "error3" );
	$my_error++;
    }

    if( $my_error < 1 )
    {
        $query = "UPDATE orders
                 SET  shipping_first_name='".$_POST['first_name']."',
                     shipping_last_name='".$_POST['last_name']."',
                     shipping_middle_initial='".$_POST['middle_initial']."',
                     shipping_address='".$_POST['street_address']."',
                     shipping_city='".$_POST['city']."', shipping_state='".$_POST['state']."' ,
                     shipping_zip_code='".$_POST['zip_code']."', shippinginfo_id='".$_POST['OC_ship']."', placed_date=NOW()
                     WHERE id=".$_POST['order_id']." LIMIT 1";

        $result = mysqlQuery( $query );

        if( !$result || mysql_affected_rows() > 1 )
        {
            renderError("UPDATE FAILED");
        }
        else
        {
            echo "UPDATE SUCCESS!!!<br/>";
        }

    }
    else
    {
        displayShippingInfoFromPOST( $_POST['order_id'] );
    }
  
}

require_once 'footer.php';

?>

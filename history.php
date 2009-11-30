<?
require_once 'config.php';
require_once 'header.php';
require_once 'utilities.php';
require_once 'login.php';
require_once 'customer_functions.php';

if( isLoggedIn() && $_POST['ship_submit'] )
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
else if( isLoggedIn() && $_GET['view_id'] )
{
    //display order view_id
    $query = "SELECT id FROM ".$tables['orderlines']." WHERE order_id=".$_GET['view_id'];
    $result = mysqlQuery( $query );

    $number = 1;

    while( $result_array = mysql_fetch_array( $result ) )
    {
        echo "<div class='cart_item'>Item #".$number."</div>";
        displayPreviouslySelectedComponentList( $result_array[0], 'cart.php' );
        echo "<br/>";
        $number++;
    }
}
else if( isLoggedIn() && $_GET['edit_id'] )
{
    //edit order edit_id
    displayShippingInfo( $_GET['edit_id'] );
}
else if( isLoggedIn() )
{
    $database = connectToDatabase();

    $query = "SELECT *
                    FROM ".$tables['orders']."
                    WHERE account_id=".$_SESSION['id']." AND placed_date IS NOT NULL";


    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows < 1 )
    {
        renderError("You have not placed any orders yet.");
    }
    else if( $rows >= 1 )
    {
        echo "
            <table border='1' width='100%'>
            <thead>
            <tr>
            <th><span class='edit_account_table_header'>Shipping Name</span></th>
            <th><span class='edit_account_table_header'>Shipping Address</span></th>
            <th><span class='edit_account_table_header'>Created Date</span></th>
            <th><span class='edit_account_table_header'>Placed Date</span></th>
            <th><span class='edit_account_table_header'>Payment Date</span></th>
            <th><span class='edit_account_table_header'>Shipped Date</span></th>
            <th><span class='edit_account_table_header'>Subtotal</span></th>
            <th/>
            <th/>
            </tr>
            </thead><tbody>";

        for( $row = 0; $row < $rows; $row++ )
        {
            $result_array = mysql_fetch_array( $result );

            $name = $result_array['shipping_last_name'].", ".$result_array['shipping_first_name']." ".$result_array['shipping_middle_initial'];
            $address = $result_array['shipping_address']."<br/>".$result_array['shipping_city'].", ".$result_array['shipping_state']." ".$result_array['shipping_zip_code'];
            $subtotal= number_format( $result_array['subtotal'], 2, '.', ',' );
            echo "
            <tr>
            <td>".$name."</td>
            <td>".$address."</td>
            <td>".$result_array['created_date']."</td>
            <td>".$result_array['placed_date']."</td>
            <td>".$result_array['payment_date']."</td>
            <td>".$result_array['shipped_date']."</td>
            <td>$".$subtotal."</td>";

            echo "<td><span class='edit_accounts'><a href='?view_id=".$result_array['id']."'>[View]</a></span></td>";

            if( $result_array['shipped_date'] )
            {
                echo "<td></td></tr>";
            }
            else
            {
                echo "
                <td><span class='edit_accounts'><a href='?edit_id=".$result_array['id']."'>[Edit]</a></span></td>
                </tr>
                     ";
            }
        }

        echo "</tbody></table>";
    }
}

addSpacing();

require_once 'footer.php';
?>

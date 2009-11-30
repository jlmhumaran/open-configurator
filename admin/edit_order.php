<?
require_once '../config.php';
require_once 'admin_header.php';
require_once '../utilities.php';


$_GET = array_map( 'mysql_real_escape_string', $_GET );
$_POST = array_map( 'mysql_real_escape_string', $_POST );

global $tables;

if( $_POST['order_changes_submitted'] )
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

    if( !validateZipCodeAdmin( $_POST['zip_code'] ) )
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
        $query = "UPDATE ".$tables['orders']."
                 SET  shipping_first_name='".$_POST['first_name']."',
                     shipping_last_name='".$_POST['last_name']."',
                     shipping_middle_initial='".$_POST['middle_initial']."',
                     shipping_address='".$_POST['street_address']."',
                     shipping_city='".$_POST['city']."', shipping_state='".$_POST['state']."' ,
                     shipping_zip_code='".$_POST['zip_code']."', shippinginfo_id='".$_POST['OC_ship']."'
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

        if( $_POST['payment'] === "on" )
        {
            $query = "UPDATE ".$tables['orders']." SET payment_date=NOW() WHERE id=".$_POST['order_id']." LIMIT 1";
            mysqlQuery( $query );
        }

        if( $_POST['shipped'] === "on" )
        {
            $query = "UPDATE ".$tables['orders']." SET shipped_date=NOW() WHERE id=".$_POST['order_id']." LIMIT 1";
            mysqlQuery( $query );
        }
    }
    else
    {
        displayShippingInfoFromPOSTAdmin( $_POST['order_id'] );
    }
}
else if( $_GET['order_id'] )
{
    //display the edit order form
    displayShippingInfoAdmin( $_GET['order_id'] );
}
else if( $_GET['id'] )
{
    $database = connectToDatabase();

    displaySearchForm();

    $query = "SELECT *
                    FROM ".$tables['orders']."
                    WHERE account_id=".$_GET['id']." AND placed_date IS NOT NULL";


    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows < 1 )
    {
        renderError("There are no orders for the selected user.");
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
            <td>$".$subtotal."</td>
            <td><span class='edit_accounts'><a href='?order_id=".$result_array['id']."'>[Edit]</a></span></td>
            </tr>
                 ";
        }

        echo "</tbody></table>";
    }







}
else if( $_POST['edited'] )
{
    displaySearchForm();
    extract( $_POST );
    //account edited, update it

    $query = "UPDATE ".$tables['orders']." SET shipping_first_name='".$_POST['shipping_first_name']."',
                                               shipping_last_name='".$_POST['shipping_last_name']."',
                                               shipping_middle_initial='".$_POST['shipping_middle_initial']."',
                                               shipping_address='".$_POST['shipping_street_address']."',
                                               shipping_city='".$_POST['shipping_city']."',
                                               shipping_state='".$_POST['shipping_state']."',
                                               shipping_zip_code='".$_POST['shipping_zip_code']."',
                                               shippinginfo_id='".$_POST['shippinginformation_id']."'
                                               WHERE id='".$_POST['shipping_order_id']."'";




    if( $status )
    {
        echo "Account update successful!<br/>";
    }
    else
    {
        echo "Account could not be updated!<br/>";
    }
}
else if( $_POST['submitted'] )
{

    displaySearchForm();
    extract( $_POST );

    $search_name = str_replace( "%", "\%", $search );
    $search_name = str_replace( "_", "\_", $search_name );
    $search_name = str_replace( "*", "%", $search_name );
    $search_name = str_replace( "?", "_", $search_name );

    $database = connectToDatabase();
    $query = "SELECT *
              FROM ".$tables['accounts']."
              WHERE last_name LIKE '".$search_name."' OR
                    first_name LIKE '".$search_name."' OR
                    email_address LIKE '".$search_name."' OR
                    INSTR( username, '".$search."' ) > 0
              ORDER BY ".$sort_order;

   $result = mysqlQuery( $query );


   $rows = mysql_num_rows( $result );

    if( $rows < 1 )
    {
        echo "Cannot find the given data.<br/>";
    }
    else if( $rows >= 1 )
    {
        echo "
            <table border='1' width='100%'>
            <thead>
            <tr>
            <th><span class='edit_account_table_header'>Username</span></th>
            <th><span class='edit_account_table_header'>Name</span></th>
            <th><span class='edit_account_table_header'>Address</span></th>
            <th><span class='edit_account_table_header'>Phone Number</span></th>
            <th><span class='edit_account_table_header'>Email Address</span></th>
            <th/>
            </tr>
            </thead><tbody>";

        for( $row = 0; $row < $rows; $row++ )
        {
            $result_array = mysql_fetch_array( $result );

            $name = $result_array['last_name'].", ".$result_array['first_name']." ".$result_array['middle_initial'];
            $address = $result_array['street_address']."<br/>".$result_array['city'].", ".$result_array['state']." ".$result_array['zip_code'];
            $phone_number = "(".$result_array['area_code'].") ".$result_array['phone_number'];
            $email_address = $result_array['email_address'];
            echo "
            <tr>
            <td>".$result_array['username']."</td>
            <td>".$name."</td>
            <td>".$address."</td>
            <td>".$phone_number."</td>
            <td>".$email_address."</td>
            <td><span class='edit_accounts'><a href='?id=".$result_array['id']."'>[Edit ".$result_array['username']."'s Orders]</a></span></td>
            </tr>
                 ";
        }

        echo "</tbody></table>";
    }

}
else
{
    displaySearchForm();
}

addSpacing();


function displaySearchForm()
{
    echo "Enter an exact First Name, Last Name, Email Address or enter an approximate Username.<br/>";
    echo "<form action='edit_order.php' method='post'>
            <input type=hidden name=submitted value=1>
            Search: <input type=text name=search><br/>
            Sort by Username<input type='radio' name='sort_order' value='username' checked='checked' /><br/>
            Sort by Last Name<input type='radio' name='sort_order' value='last_name' /><br/>
            Pattern Matching: Can use * (any number of any character) and ? (one of any character)<br/>
            <input type=submit name='Submit'>
      </form><br/><br/>";
}

function displayShippingInfoAdmin( $order_id_in )
{
    global $tables;

    $database = connectToDatabase();

    $query = "SELECT * FROM ".$tables['shippinginfo'];
    $result = mysqlQuery( $query );

    $query_2 = "SELECT shipping_first_name, shipping_last_name,shipping_middle_initial, shipping_address, shipping_city, shipping_state, shipping_zip_code FROM ".$tables['orders'];
    $result_2 = mysqlQuery ( $query_2 );

    if( !$result || mysql_num_rows($result) < 1 )
    {
        renderError("SHIPPING INFO TABLE EMPTY");
        return;
    }
    if( !$result_2 || mysql_num_rows($result_2) < 1 )
    {
        renderError("ORDER PASSED IN DOESN'T EXIST");
        return;
    }
    $result_array_2 = mysql_fetch_array( $result_2 );
    echo "<form method='post'>";
        echo "<table>";
        echo "<tr><td>First Name:</td><td><input type=text name='first_name' value='".$result_array_2['shipping_first_name']."'></td></tr>";
	echo "<tr><td>Last Name:</td><td><input type=text name='last_name' value='".$result_array_2['shipping_last_name']."'></td></tr>";
	echo "<tr><td>Middle Initial:</td><td><input type=text name='middle_initial' value='".$result_array_2['shipping_middle_initial']."'></td></tr>";
	echo "<tr><td>Street Address:</td><td><input type=text name='street_address' value='".$result_array_2['shipping_address']."'></td></tr>";
	echo "<tr><td>City:</td><td><input type=text name='city' value='".$result_array_2['shipping_city']."'></td></tr>";
        echo "<tr><td>State:</td><td><select name='state'>";
         $states = "<option>AL</option> <option>AK</option> <option>AS</option> <option>AZ</option>
	<option>AR</option> <option>CA</option> <option>CO</option> <option>CT</option>
	<option>DE</option> <option>DC</option> <option>FM</option> <option>FL</option>
	<option>GA</option> <option>GU</option> <option>HI</option> <option>ID</option>
	<option>IL</option> <option>IN</option> <option>IA</option> <option>KS</option>
	<option>KY</option> <option>LA</option> <option>ME</option> <option>MH</option>
	<option>MD</option> <option>MA</option> <option>MI</option> <option>MN</option>
	<option>MS</option> <option>MO</option> <option>MT</option> <option>NE</option>
	<option>NV</option> <option>NH</option> <option>NJ</option> <option>NM</option>
	<option>NY</option> <option>NC</option> <option>ND</option> <option>MP</option>
	<option>OH</option> <option>OK</option> <option>OR</option> <option>PW</option>
	<option>PA</option> <option>PR</option> <option>RI</option> <option>SC</option>
	<option>SD</option> <option>TN</option> <option>TX</option> <option>UT</option>
	<option>VT</option> <option>VI</option> <option>VA</option> <option>WA</option>
	<option>WV</option> <option>WI</option> <option>WY</option></select></td></tr>";
        if( isset($result_array_2['shipping_state'] ) )
        {
            $states = str_replace( ">".$result_array_2['shipping_state'], " selected='selected'>".$result_array_2['shipping_state'], $states );

        }
        else
        {
            $states = str_replace( ">AL", " selected='selected'>AL", $states );
        }
        echo $states;
        echo "<tr><td>Zip-Code:</td><td><input type=text name='zip_code' value='".$result_array_2['shipping_zip_code']."'></td></tr>";

    echo "<tr><td>Shipping Method:</td><td><select name='OC_ship'>";

    while ( $result_array = mysql_fetch_array( $result ) )
    {

    echo "<option value='".$result_array['id']."'>".$result_array['name']."- \$".number_format( $result_array['cost'], 2, '.', ',' )."</option>";

    }

    echo "</select></td></tr>";

    echo "<tr>";
    echo "<td>Payment Received:</td>";
    echo "<td><input type='checkbox' name='payment' /></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>Order Shipped:</td>";
    echo "<td><input type='checkbox' name='shipped' /></td>";
    echo "</tr>";


    echo"<input type='hidden' name='order_id' value='".$order_id_in."' />
    <tr><td><input type='submit' value='SUBMIT SHIPPING INFO' name='ship_submit' /></td><td><input type='hidden' name='order_changes_submitted' value='1' /></td>
    </table>
    </form>";
}

function displayShippingInfoFromPOSTAdmin($order_id_in)
{
        global $tables;

    $database = connectToDatabase();

    $query = "SELECT * FROM ".$tables['shippinginfo'];
    $result = mysqlQuery( $query );

    if( !$result || mysql_num_rows($result) < 1 )
    {
        renderError("SHIPPING INFO TABLE EMPTY");
        return;
    }


        echo "<form method='post'>";
        echo "<table>";
        echo "<tr><td>First Name:</td><td><input type=text name='first_name' value='".$_POST['first_name']."'></td></tr>";
	echo "<tr><td>Last Name:</td><td><input type=text name='last_name' value='".$_POST['last_name']."'></td></tr>";
	echo "<tr><td>Middle Initial:</td><td><input type=text name='middle_initial' value='".$_POST['middle_initial']."'></td></tr>";
	echo "<tr><td>Street Address:</td><td><input type=text name='street_address' value='".$_POST['street_address']."'></td></tr>";
	echo "<tr><td>City:</td><td><input type=text name='city' value='".$_POST['city']."'></td></tr>";
        echo "<tr><td>State:</td><td><select name='state'>";
         $states = "<option>AL</option> <option>AK</option> <option>AS</option> <option>AZ</option>
	<option>AR</option> <option>CA</option> <option>CO</option> <option>CT</option>
	<option>DE</option> <option>DC</option> <option>FM</option> <option>FL</option>
	<option>GA</option> <option>GU</option> <option>HI</option> <option>ID</option>
	<option>IL</option> <option>IN</option> <option>IA</option> <option>KS</option>
	<option>KY</option> <option>LA</option> <option>ME</option> <option>MH</option>
	<option>MD</option> <option>MA</option> <option>MI</option> <option>MN</option>
	<option>MS</option> <option>MO</option> <option>MT</option> <option>NE</option>
	<option>NV</option> <option>NH</option> <option>NJ</option> <option>NM</option>
	<option>NY</option> <option>NC</option> <option>ND</option> <option>MP</option>
	<option>OH</option> <option>OK</option> <option>OR</option> <option>PW</option>
	<option>PA</option> <option>PR</option> <option>RI</option> <option>SC</option>
	<option>SD</option> <option>TN</option> <option>TX</option> <option>UT</option>
	<option>VT</option> <option>VI</option> <option>VA</option> <option>WA</option>
	<option>WV</option> <option>WI</option> <option>WY</option></select></td></tr>";
        if( isset($_POST['state'] ) )
        {
            $states = str_replace( ">".$_POST['state'], " selected='selected'>".$_POST['state'], $states );

        }
        else
        {
            $states = str_replace( ">AL", " selected='selected'>AL", $states );
        }
        echo $states;
        echo "<tr><td>Zip-Code:</td><td><input type=text name='zip_code' value='".$_POST['zip_code']."'></td></tr>";

    echo "<tr><td>Shipping Method:</td><td><select name='OC_ship'>";

    while ( $result_array = mysql_fetch_array( $result ) )
    {

    echo "<option value='".$result_array['id']."'>".$result_array['name']."- \$".number_format( $result_array['cost'], 2, '.', ',' )."</option>";

    }

    echo "</select></td></tr>";

    echo "<tr>";
    echo "<td>Payment Received:</td>";
    echo "<td><input type='checkbox' name='payment' /></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>Order Shipped:</td>";
    echo "<td><input type='checkbox' name='shipped' /></td>";
    echo "</tr>";

    echo"<input type='hidden' name='order_id' value='".$order_id_in."' />
    <tr><td><input type='submit' value='SUBMIT SHIPPING INFO' name='ship_submit' /></td><td></td>
    </table>
    </form>";

}

 function validateZipCodeAdmin( $zip_code_in )
	{
		global $zip_code_length;

		if( !$zip_code_in )
		{
			//renderError( "You must enter a Zip-code" );
			return 0;
		}
		else if( strlen( $zip_code_in ) != $zip_code_length ) //switch to math method for zip
		{
			//renderError( "Your zip-code has an improper length" );
			return 0;
		}
		else if( ( intval($zip_code_in) / pow( 10, $zip_code_length ) ) >= 1.0 || intval($zip_code_in) < 10000 )
		{
			//renderError( "Your zip-code contains invalid characters" );
			return 0;
		}

		return 1;
        }

require_once '../footer.php';
?>

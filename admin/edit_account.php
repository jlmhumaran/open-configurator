<?
require_once '../config.php';
require_once 'admin_header.php';
require_once '../utilities.php';
require_once '../account.php';

$_GET = array_map( 'mysql_real_escape_string', $_GET );
$_POST = array_map( 'mysql_real_escape_string', $_POST );

global $tables;

if( $_GET['id'] )
{
    $database = connectToDatabase();
    $account = new Account;
    $account->loadFromDatabase( $_GET['id'], $database );

    displayForm( $account );
}
else if( $_POST['edited'] )
{
    displaySearchForm();
    extract( $_POST );
    //account edited, update it
    $database = connectToDatabase();
    $account = new Account;

    $account->loadFromDatabase( $database_id, $database );
    $account->setFirstName( $first_name );
    $account->setLastName( $last_name );
    $account->setMiddleInitial( $middle_initial );
    $account->setStreetAddress( $street_address );
    $account->setCity( $city );
    $account->setState( $state );
    $account->setZipCode( $zip_code );
    $account->setAreaCode( $area_code );
    $account->setPhoneNumber( $phone_number );
    $status = $account->update( $database );

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

    $result = mysqlQuery( $query, $database );
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
            <td><span class='edit_accounts'><a href='?id=".$result_array['id']."'>[Edit ".$result_array['username']."]</a></span></td>
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
require_once '../footer.php';

function displayForm( $account_in )
{
	echo "<form method='post' action='edit_account.php'><table>";
	echo "<tr><td>Username:</td><td>".$account_in->getUsername()."</td></tr>";
	echo "<tr><td>First Name:</td><td><input type='text' name='first_name' value='".$account_in->getFirstName()."' /></td></tr>";
	echo "<tr><td>Last Name:</td><td><input type='text' name='last_name' value='".$account_in->getLastName()."' /></td></tr>";
	echo "<tr><td>Middle Initial:</td><td><input type='text' name='middle_initial' value='".$account_in->getMiddleInitial()."' /></td></tr>";
	echo "<tr><td>Street Address:</td><td><input type='text' name='street_address' value='".$account_in->getStreetAddress()."' /></td></tr>";
	echo "<tr><td>City:</td><td><input type='text' name='city' value='".$account_in->getCity()."' /></td></tr>";
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
        $state = $account_in->getState();
        $states = str_replace( ">".$state, " selected='selected'>".$state, $states );
        echo $states;
	echo "<tr><td>Zip-Code:</td><td><input type='text' name='zip_code' value='".$account_in->getZipCode()."' /></td></tr>";
	echo "<tr><td>Email Address:</td><td>".$account_in->getEmailAddress()."</td></tr>";
	echo "<tr><td>Phone Number:</td><td>(<input type='text' name='area_code' size='1' value='".$account_in->getAreaCode()."' />)<input type='text' name='phone_number' size='8' value='".$account_in->getPhoneNumber()."' /></td></tr>";
        echo "<tr><td></td><td>SEND PASSWORD RESET EMAIL?</td></tr>";
	echo "<tr><td><input type='submit' value='Submit' /></td><td><input type='hidden' name='database_id' value='".$account_in->getDatabaseID()."' /><input type='hidden' name='edited' value='1' /></td></tr></table></form>";
}

function displaySearchForm()
{
    echo "Enter an exact First Name, Last Name, Email Address or enter an approximate Username.<br/>";
    echo "<form action='edit_account.php' method='post'>
            <input type=hidden name=submitted value=1>
            Search: <input type=text name=search><br/>
            Sort by Username<input type='radio' name='sort_order' value='username' checked='checked' /><br/>
            Sort by Last Name<input type='radio' name='sort_order' value='last_name' /><br/>
            Pattern Matching: Can use * (any number of any character) and ? (one of any character)<br/>
            <input type=submit name='Submit'>
      </form><br/><br/>";
}
?>
<?
require_once 'config.php';
require_once 'header.php';
require_once 'utilities.php';
require_once 'account.php';

$_GET = array_map( 'mysql_real_escape_string', $_GET );
$_POST = array_map( 'mysql_real_escape_string', $_POST );

if( $_SESSION['username'] )
{
	echo "You are already logged in.  Welcome, ".$_SESSION['username']."!";
}
else if( $_POST['submit'] && recaptchaCheck() )
{
	extract( $_POST );


	//CALL CHECKPASSWORD
	$database = connectToDatabase();
	$new_account = new Account;

	$new_account->setFirstName( $first_name );
	$new_account->setLastName( $last_name );
	$new_account->setMiddleInitial( $middle_initial );
	$new_account->setStreetAddress( $street_address );
	$new_account->setCity( $city );
	$new_account->setState( $state );
	$new_account->setZipCode( $zip_code );
	$new_account->setEmailAddress( $email_address );
	$new_account->setAreaCode( $area_code );
	$new_account->setPhoneNumber( $phone_number );
	$new_account->setUsername( $username, $database );
	
	if( $password === $password2 )
	{
		$new_account->setPassword( $password );
	}
	
	if( $new_account->updateDatabase( $database ) == 0 )
	{
                displayForm();
	}
	else
	{
		echo "Account created successfully!<br/>";
	}
}
else
{
	displayForm();
}

function displayForm()
{
	echo "<form method='post'>";
        echo "<table>";
	echo "<tr><td>Desired Username:</td><td><input type=text name='username' value='".$_POST['username']."'></td></tr>";
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
        if( isset( $_POST['state'] ) )
        {
            $states = str_replace( ">".$_POST['state'], " selected='selected'>".$_POST['state'], $states );

        }
        else
        {
            $states = str_replace( ">AL", " selected='selected'>AL", $states );
        }
        echo $states;
	echo "<tr><td>Zip-Code:</td><td><input type=text name='zip_code' value='".$_POST['zip_code']."'></td></tr>";
	echo "<tr><td>Email Address:</td><td><input type=text name='email_address' value='".$_POST['email_address']."'></td></tr>";
	echo "<tr><td>Phone Number:</td><td>(<input type=text name='area_code' size='1' value='".$_POST['area_code']."'>)<input type=text name='phone_number' size='8' value='".$_POST['phone_number']."'></td></tr>";
	echo "<tr><td>Desired Password:</td><td><input type=password name='password'></td></tr>";
	echo "<tr><td>Re-Type password:</td><td><input type=password name='password2'></td></tr>";
	echo "<tr><td></td><td>".getRecaptchaCode()."</td></tr>";
	echo "<input type='hidden' name='submit' value='1'>";
	echo "<tr><td><input type=submit value='Submit'></td><td></td></tr></table></form>";
}

require_once 'footer.php';
?>
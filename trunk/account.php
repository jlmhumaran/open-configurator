<?
require_once 'utilities.php';

/*$query = "SELECT * FROM accounts WHERE id=2";
$result = mysqlQuery( $query, connectToDatabase() );
$result_array = mysql_fetch_array( $result );
$name = $result_array['first_name']." ".$result_array['last_name'];*/

/*
SECURE CONNECTION VIA SSL FOR USERNAME/PASSWORD TRANSMISSION!!!
*/

class Account
{
	private $database_id = 0;
	private $last_name;
	private $first_name;
	private $middle_initial;
	private $street_address;
	private $state;
	private $city;
	private $zip_code;
	private $email_address;
	private $area_code;
	private $phone_number;
	private $username;
	private $password;
	
	function __construct( $username_in = null, $database = null )
	{
		global $tables;
		
		if( !$username_in && !$database )
		{
			//$this->database_id = 0;
			return;
		}
		
		$query_result = mysqlQuery( "SELECT * FROM ".$tables['accounts']." WHERE username='".$username_in."'", $database );
		
		if( mysql_error() )
		{
			renderError( "MySQL; ".mysql_error() );
			return;
		}
		
		if( mysql_num_rows( $query_result ) > 1 )
		{
			renderError( "There is more than one account by that username.  Please contact the administrator" );
			return;
		}
		
		$result_array = mysql_fetch_array( $query_result );
		
		if( !$result_array )
		{
			renderError( "That username does not exist" );
			return;
		}
		
		$this->database_id = $result_array['id'];
		$this->last_name = $result_array['last_name'];
		$this->first_name = $result_array['first_name'];
		$this->middle_initial = $result_array['middle_initial'];
		$this->street_address = $result_array['street_address'];
		$this->city = $result_array['city'];
		$this->state = $result_array['state'];
		$this->zip_code = $result_array['zip_code'];
		$this->email_address = $result_array['email_address'];
		$this->area_code = $result_array['area_code'];
		$this->phone_number = $result_array['phone_number'];
		$this->password = $result_array['password'];
	}
	
        function loadFromDatabase( $id_in, $database )
        {
            global $tables;

            $this->database_id = $id_in;
            $query = "SELECT * FROM ".$tables['accounts']." WHERE id=".$id_in;
            $result = mysqlQuery( $query, $database );
            
            if( mysql_num_rows( $result ) < 1 )
            {
                renderError( "Database cannot find an account by that ID." );
                return 0;
            }
            else if( mysql_num_rows( $result ) > 1 )
            {
                renderError( "MySQL; The database has serious issues because it has allowed rows with duplicate keys." );
                return 0;
            }
            
            $result_array = mysql_fetch_array( $result );
            
            $this->last_name = $result_array['last_name'];
            $this->first_name = $result_array['first_name'];
            $this->middle_initial = $result_array['middle_initial'];
            $this->street_address = $result_array['street_address'];
            $this->city = $result_array['city'];
            $this->state = $result_array['state'];
            $this->zip_code = $result_array['zip_code'];
            $this->email_address = $result_array['email_address'];
            $this->area_code = $result_array['area_code'];
            $this->phone_number = $result_array['phone_number'];
            $this->username = $result_array['username'];
            $this->password = $result_array['password'];
            return 1;
        }
        
	function getLastName()
	{
		return $this->last_name;
	}
	
	function getFirstName()
	{
		return $this->first_name;
	}
	
	function getMiddleInitial()
	{
		return $this->middle_initial;
	}
	
	function getStreetAddress()
	{
		return $this->street_address;
	}
	function getCity()
	{
		return $this->city;
	}
	
	function getState()
	{
		return $this->state;
	}
	
	function getZipCode()
	{
		return $this->zip_code;
	}
	
	function getEmailAddress()
	{
		return $this->email_address;
	}
	
	function getAreaCode()
	{
		return $this->area_code;
	}
	
	function getPhoneNumber()
	{
		return $this->phone_number;
	}
	
	function getUsername()
	{
		return $this->username;
	}
	
	function getDatabaseID()
	{
		return $this->database_id;
	}
	
	function checkPassword( $password_in )
	{
		$password_in = hash( 'whirlpool', $password_in );
		
		return ( $this->password === $password_in );
	}
	
	function setLastName( $last_name_in )
	{
		if( !$last_name_in )
		{
			renderError( "You must enter a last name." );
			return 0;
		}
		
		$this->last_name = $last_name_in;
		return 1;
	}
	
	function setFirstName( $first_name_in )
	{
		if( !$first_name_in )
		{
			renderError( "You must enter a first name." );
			return 0;
		}
		
		$this->first_name = $first_name_in;
		return 1;
	}
	
	function setMiddleInitial( $middle_initial_in )
	{
		if( strlen( $middle_intial_in ) > 1 )
		{
			renderError( "You may not have more than one letter for middle initial" );
			return 0;
		}
		
		$this->middle_initial = $middle_initial_in;
		return 1;
	}
	
	function setStreetAddress( $street_address_in )
	{
		if( !$street_address_in )
		{
			renderError( "You must enter a street address" );
			return 0;
		}
		
		$this->street_address = $street_address_in;
		return 1;
	}
	function setCity( $city_in )
	{
		if( !$city_in )
		{
			renderError( "You must enter a city" );
			return 0;
		}
		
		$this->city = $city_in;
		return 1;
	}
	
	function setState( $state_in )
	{
		$this->state = $state_in;
	}
	
	function setZipCode( $zip_code_in )
	{
		global $zip_code_length;
		
		if( !$zip_code_in )
		{
			renderError( "You must enter a Zip-code" );
			return 0;
		}
		else if( strlen( $zip_code_in ) != $zip_code_length ) //switch to math method for zip
		{
			renderError( "Your zip-code has an improper length" );
			return 0;
		}
		else if( ( intval($zip_code_in) / pow( 10, $zip_code_length ) ) >= 1.0 || intval($zip_code_in) < 10000 )
		{
			renderError( "Your zip-code contains invalid characters" );
			return 0;
		}
		
		$this->zip_code = $zip_code_in;
		return 1;
        }

	function setEmailAddress( $email_address_in )
	{
		if( !$email_address_in )
		{
			renderError( "You must enter an email address" );
			return 0;
		}

                /*if( !validate_email( $email_address_in ) )
                {
                    renderError( "Email Address must be in the correct format (ex. billy123@uhv.edu)" );
                    return 0;
                }*/
		if( !preg_match( "/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9]([-a-z0-9_]?[a-z0-9])*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z]{2})|([1]?\d{1,2}|2[0-4]{1}\d{1}|25[0-5]{1})(\.([1]?\d{1,2}|2[0-4]{1}\d{1}|25[0-5]{1})){3})(:[0-9]{1,5})?$/i", $email_address_in ) )
		{
        	        renderError( "Email Address must be in the correct format (ex. billy123@uhv.edu)" );
			return 0;		
		}

                $this->email_address = $email_address_in;
		return 1;
	}
	
	function setAreaCode( $area_code_in )
	{
		if( !$area_code_in )//phone number area code
		{
			renderError( "You must enter an Area code for your phone number" );
			return 0;
		}
		else if( strlen( $area_code_in ) != 3 )
		{
			renderError( "Your area code is not in the correct 3-digit format" );
			return 0;
		}
		else if( intval( $area_code_in ) < 100 )
		{
			renderError( "Your area code is not in the correct 3-digit format" );
			return 0;
		}
		
		$this->area_code = $area_code_in;
		return 1;
	}
	
	function setPhoneNumber( $phone_number_in )
	{
		if( !$phone_number_in )
		{
			renderError( "You must enter a phone number" );
			return 0;
		}
		else if( strlen( $phone_number_in ) < 7 || strlen( $phone_number_in ) > 8 )
		{
			renderError( "Your phone number has an improper length." );
			return 0;
		}
		else if( strlen( $phone_number_in ) == 7 )
		{
			if( intval( $phone_number_in ) / 10000000.0 >= 1.0 )
			{
				renderError( "You did not enter your phone number properly" );
				return 0;
			}

                        $phone_number_in = substr( $phone_number_in, 0, 3 )."-".substr( $phone_number_in, 3, 4 );
		}
		else if( strlen( $phone_number_in ) == 8 )
		{		
			sscanf( $phone_number_in, "%u-%u", $first_part, $second_part );
			
			if( $first_part < 100 || $second_part < 1000 || $first_part / 1000.0 >= 1.0 || 
					$second_part / 10000.0 >= 1.0 )
			{
				renderError( "You did not enter your phone number properly" );
				return 0;
			}
		}
		
		$this->phone_number = $phone_number_in;
		return 1;
	}
	
	function setUsername( $username_in, $database )
	{
		global $username_length, $tables;
		
		if( !$database )
		{
			renderError( "Failed to set username due to null database resource!" );
			return 0;
		}
		
		if( $database_id )
		{
			return 0;
		}
		else if( !$username_in )
		{
			renderError( "You must enter a username" );
			return 0;
		}
		else if( strlen( $username_in ) < $username_length )
		{
			renderError( "Usernames must be at least $username_length characters long" );
			return 0;
		}
		
		$query = "SELECT username FROM ".$tables['accounts']." WHERE username='".$username_in."'";
		$query_result = mysqlQuery( $query, $database );
	
		if( mysql_num_rows( $query_result ) > 0 )
		{
			renderError( "That username is already taken!" );
			return 0;
		}
		
		$this->username = $username_in;
		return 1;
	}
	
	function setPassword( $password_in )
	{
		global $password_length;
		
		if( !$password_in )
		{
			renderError( "You must enter a password" );
			return 0;
		}
		else if( strlen( $password_in ) < $password_length )
		{
			renderError( "Passwords must be at least $password_length characters in length" );
			return 0;
		}

		$this->password = hash( 'whirlpool', $password_in );
		return 1;
	}
	
	function updateDatabase( $database )
	{
		global $tables;
		
		if( !$database )
		{
			renderError( "Account update method was passed a null database resource" );
			return 0;
		}
		
		if( !$this->first_name || !$this->last_name || !$this->street_address || !$this->city ||
			  !$this->state || !$this->zip_code || !$this->email_address || !$this->area_code ||
			  !$this->phone_number || !$this->password || !$this->username )
		{
			renderError( "The update cannot be processed." );
			return 0;
		}
		
		if( $this->database_id )
		{
			//UPDATE table SET field='newval', field2='newval2' WHERE id='$database_id'
			$query = "UPDATE ".$tables['accounts']." SET ".
							 "first_name='".$this->first_name."',".
							 "last_name='".$this->last_name."',".
							 "middle_initial='".$this->middle_initial."',".
							 "street_address='".$this->street_address."',".
							 "city='".$this->city."',".
							 "state='".$this->state."',".
							 "zip_code='".$this->zip_code."',".
							 "email_address='".$this->email_address."',".
							 "area_code='".$this->area_code."',".
							 "phone_number='".$this->phone_number."',".
							 "password='".$this->password.
							 "' WHERE id='".$this->database_id."'";
		}
		else
		{
			$query = "INSERT INTO ".$tables['accounts']." VALUES ( ".
					 "NULL, ".
                                         "'".$this->last_name."',".
					 "'".$this->first_name."',".
					 "'".$this->middle_initial."',".
					 "'".$this->street_address."',".
					 "'".$this->city."',".
					 "'".$this->state."',".
					 "'".$this->zip_code."',".
					 "'".$this->email_address."',".
					 "'".$this->area_code."',".
					 "'".$this->phone_number."',".
					 "'".$this->username."',".
					 "'".$this->password."' )";
		}
		
		if( !query )
		{
			renderError( "Update Failed -- Account update query was empty!!!" );
			return 0;
		}
		
		$query_result = mysqlQuery( $query, $database );
		
		if( mysql_error() )
		{
			//debug( "query", $query );
			renderError( "Unknown MySQL Error; ".mysql_error() );
			return 0;
		}
		
		$query = "SELECT id FROM ".$tables['accounts']." WHERE username='".$this->username."'";
		$query_result = mysqlQuery( $query, $database );
		$result_array = mysql_fetch_array( $query_result );
		$this->database_id = $result_array[0];
		
		if( !$this->database_id )
		{
			renderError( "Account creation successful, but failed to set database id." );
			return 0;
		}
		
		return 1;
	}
	
	function update()
	{
		return $this->updateDatabase( connectToDatabase() );
	}
}
?>

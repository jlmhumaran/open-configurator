<?
require_once 'utilities.php';

//Database Server
$database_server = "localhost";

//Database Username
$database_username = "oc";

//Database Password
$database_password = "barfbag";

//Name of the database on the MySQL Server
$database_name = "oc";

////////////////////////////////////////////////////////////////////////////////////////////////
//Administrators: do not touch anything below this line unless you're a skilled PHP developer!//
////////////////////////////////////////////////////////////////////////////////////////////////

$query = "SELECT * FROM vars_text";
$query_2 = "SELECT * FROM vars_numeric";

$database = connectToDatabase();

$result = mysqlQuery( $query, $database );
$result_2 = mysqlQuery( $query_2, $database );

while( $result_array = mysql_fetch_array( $result ) )
{
    //debugArray( "text", $result_array );

    $first = stripos( $result_array['name'], "'" );
    $second = stripos( $result_array['name'], "'", $first+1 );

    if( $first > 0 && $second > 0 && $second > $first )
    {
        $table_var = substr( $result_array['name'], $first + 1, $second - $first - 1 );
        $tables[$table_var] = $result_array['value'];
    }
    else
    {
        $$result_array['name'] = $result_array['value'];
    }
}

while( $result_array_2 = mysql_fetch_array( $result_2 ) )
{
    //debugArray( "numeric", $result_array_2 );
    $$result_array_2['name'] = $result_array_2['value'];
}

//****************NOTHING BUT COMMENTS BELOW THIS LINE!!!***********************

//Root Document Directory (no trailing slash)
//$root_directory = "/home/oc/public_html";

//Root Web Directory (no trailing slash)
//$root_web_directory = "http://oc.ericneill.com";

//Table name for accounts
//$tables['accounts'] = "accounts";

//Table name for classes
//$tables['classes'] = "class";

//Table name for families
//$tables['families'] = "family";

//Table name for components
//$tables['components'] = "component";

//Table name for dependabilities
//$tables['dependabilities'] = "dependability";

//Table name for family compatibilites
//$tables['familycompatibilities'] = "familycompatibility";

//Table name for component non-compatibilities
//$tables['componentnoncompatibilities'] = "componentnoncompatibility";

//Table name for orders
//$tables['orders'] = "orders";

//Table name for selected components
//$tables['selectedcomponents'] = "selectedcomponent";

//Table name for line items or order lines
//$tables['orderlines'] = "orderline";

//Table name for shipping methods
//$tables['shippinginfo'] = "shippinginfo";

//minimum username length
//$username_length = 3;

//minimum password length
//$password_length = 6;

//zip code length
//$zip_code_length = 5;

//assembly fee
//$assembly_fee = 50;

//maximum session duration (IN SECONDS)
//$session_duration = 60 * 60 * 8;

//number of break lines to add below short sections
//$spacing = 10;

//recaptcha public key
//$recaptcha_public_key = "6LcLJwkAAAAAAAAYrZNSi89kkhNFAGmN1wEWBGNC";

//recaptcha private key
//$recaptcha_private_key = "6LcLJwkAAAAAABCf6M2Iv4ZVr77knMCIq543wpus";

//disable redirects?
//$no_redirects = 0;

//enable debugging of all mysql queries
//$mysql_debug = 1;
?>
<?
require_once '../config.php';
require_once 'admin_header.php';
require_once '../utilities.php';

if( $_POST['submitted'] )
{
    $database = connectToDatabase();

    foreach( $_POST as $key => $value )
    {
        if( substr( $key, 0, 5 ) === "text_" )
        {
            //do text parsing
            $name = substr( $key, 5 );

            $query = "UPDATE vars_text SET value='".$_POST[$key]."' WHERE name='".$name."'";
            $result = mysqlQuery( $query, $database );

            if( mysql_error() )
            {
                $errors++;
            }
        }
        else
        {
            $query = "UPDATE vars_numeric SET value='".$_POST[$key]."' WHERE name='".$key."'";
            $result = mysqlQuery( $query, $database );

            if( mysql_error() )
            {
                $errors++;
            }
        }
    }

    if( !$errors )
    {
        echo "Configuration updated successfully!<br/>";
    }
    else
    {
        echo "There were errors updating the configuration.<br/>";
    }

    displayConfigForm();
}
else
{
    displayConfigForm();
}

function displayConfigForm()
{
    $query = "SELECT * FROM vars_text";
    $query_2 = "SELECT * FROM vars_numeric";

    $database = connectToDatabase();

    $result = mysqlQuery( $query, $database );
    $result_2 = mysqlQuery( $query_2, $database );

    echo "<form method='post'><table>";

    while( $result_array = mysql_fetch_array( $result ) )
    {
        //debugArray( "text", $result_array );

        echo "<tr>";
        echo "<td>";
        echo "<input type='text' name='text_".$result_array['name']."' value='".$result_array['value']."' size='50' />";
        echo "</td>";
        echo "<td>";
        echo $result_array['description'];
        echo "</td>";
        echo "</tr>";
    }

    while( $result_array_2 = mysql_fetch_array( $result_2 ) )
    {
        echo "<tr>";
        echo "<td>";
        echo "<input type='text' name='".$result_array_2['name']."' value='".$result_array_2['value']."' size='50' />";
        echo "</td>";
        echo "<td>";
        echo $result_array_2['description'];
        echo "</td>";
        echo "</tr>";
    }

    echo "<tr>";
    echo "<td><input type='submit' value='Submit' /></td><td><input type='hidden' name='submitted' value='1' /></td></tr>";
    echo "</table></form>";
}

require_once "../footer.php";
?>
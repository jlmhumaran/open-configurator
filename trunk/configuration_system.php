<?php
require_once 'utilities.php';
require_once 'header.php';
require_once 'login.php';
require_once 'customer_functions.php';

$_GET = array_map( 'mysql_real_escape_string', $_GET );
$_POST = array_map( 'mysql_real_escape_string', $_POST );

if( isLoggedIn() )
{
    global $tables;
    if($_POST['CS'])
    {
      createNewOrderline($_POST['order_id']);
      setup();
    }    
    else if( $_POST['submit'] && is_numeric( $_POST['configuration_system_name'] ) )
    {
        $database = connectToDatabase();
        $query = "SELECT ".$tables['classes'].".id FROM ".$tables['components']." JOIN ( ".$tables['classes'].", ".$tables['families']." ) ON ( ".$tables['components'].".family_id=".$tables['families'].".id AND ".$tables['families'].".class_id=".$tables['classes'].".id ) WHERE ".$tables['components'].".id=".$_POST['configuration_system_name'];
        $class_id = mysqlGetSingleValue( $query, $database );
        
        $query = "SELECT ".$tables['selectedcomponents'].".id FROM ".$tables['selectedcomponents']." JOIN ( ".$tables['orderlines'].", ".$tables['components'].", ".$tables['families'].", ".$tables['classes']." ) ON ( ".$tables['selectedcomponents'].".orderline_id=".$tables['orderlines'].".id AND ".$tables['components'].".id=".$tables['selectedcomponents'].".component_id AND ".$tables['families'].".id=".$tables['components'].".family_id AND ".$tables['classes'].".id=".$tables['families'].".class_id ) WHERE ".$tables['orderlines'].".id=".$_POST['orderline_id']." AND ".$tables['classes'].".id=".$class_id;
        $result = mysqlQuery( $query, $database );

        if( mysql_num_rows( $result ) > 0 )
        {
            $result_array = mysql_fetch_array( $result );
            $query = "UPDATE ".$tables['selectedcomponents']." SET component_id=".$_POST['configuration_system_name']." WHERE ".$tables['selectedcomponents'].".id=".$result_array[0]." LIMIT 1";
        }
        else
        {
            $query = "INSERT INTO ".$tables['selectedcomponents']." VALUES ( NULL, ".$_POST['configuration_system_name'].", ".$_POST['orderline_id'].", 0 )";
        }

        mysqlQuery( $query, $database );
        updateFlags( $class_id, $database );
        if( $_GET['finished'] )
        {
            redirect( $_GET['finished'] );
        }
        else if( $_POST['finished'] )
        {
            redirect( $_POST['finished'] );
        }
        else if( isOrderLineFull( $_POST['orderline_id'] ) )
        {
            if( $_GET['return_to_class'] )
            {
                $class_id = $_GET['return_to_class'];
                setup();
            }
            else
            {
                orderComplete( $_POST['orderline_id'] );
            }
        }
        else
        {
            setup();
        }
    }
    else
    {
        setup();
    }
}

addSpacing();
require_once 'footer.php';
?>
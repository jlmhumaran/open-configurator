<?
require_once 'utilities.php';

function orderComplete( $orderline_id_in, $order_id_in=null, $supress_display=null, $supress_error=null )
{
    $database = connectToDatabase();

    if( !$order_id_in )
    {
        $query = "SELECT order_id FROM orderline WHERE id=".$orderline_id_in;
        $order_id_in = mysqlGetSingleValue( $query );
    }

    //IF YOU EVER USE $orderline_id_in BELOW THIS LINE, YOU DID SOMETHING WRONG IDIOT

    $query = "SELECT orderline.id,selectedcomponent.incompatible_flag FROM orders JOIN ( orderline, selectedcomponent, component ) ON ( component.id=selectedcomponent.component_id AND selectedcomponent.orderline_id=orderline.id AND orderline.order_id=orders.id ) WHERE orders.id=".$order_id_in;
    $result = mysqlQuery( $query, $database );

    $orderlines = array();

    while( $result_array = mysql_fetch_array( $result ) )
    {
        if( !in_array( $result_array['id'], $orderlines ) && $result_array['incompatible_flag'] )
        {
            if( !$supress_display )
            {
                displayPreviouslySelectedComponentList( $result_array['id'] );
            }

            if( count( $orderlines )  < 1 && !$supress_error )
            {
                echo "<br/>";
                orderError();
                echo "12341212";
                echo "<br/><br/>";
            }

            $orderlines[] = $result_array['id'];
        }
    }

    if( count( $orderlines ) > 0 )
    {
        return;
    }
///////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

    if( !$supress_display )
    {
        displayPreviouslySelectedComponentList($orderline_id_in);
    }

    echo"<form action='checkout.php' method='post'>
    <input type='hidden' name='order_id' value='".$order_id_in."' />
    <input type='submit' value='CHECKOUT' name='CO' />
    </form>";


    echo"<form action='configuration_system.php' method='post'>
    <input type='hidden' name='order_id' value='".$order_id_in."' />
    <input type='submit' value='CONTINUE SHOPPING' name='CS'/>
    </form>";

    //handle shit here for completeing the order.

}

function orderError()
{
    renderError( "Please select compatible components for the highlighted incompatibilities." );
}

function setup()
{
    $order_id = getCurrentOrder();

    if( $order_id <= 0 )
    {
        renderError( "Order could not be created or obtained by any known method" );
    }
    else
    {
        $orderline_id = getCurrentOrderline( $order_id );

        if( isset( $_GET['return_to_class'] ) )
        {
            $class_id = $_GET['return_to_class'];
        }
        else
        {
            $class_id = getCurrentClass( $orderline_id );
        }

        if( $orderline_id > 0 )
        {
            displayPreviouslySelectedComponentList( $orderline_id );
            displayConfiguredComponentList( $class_id, $orderline_id );
        }
        else if( $orderline_id >= 0 )
        {
            echo "<br/>";
            orderError();
            echo "<br/><br/>";
        }
    }
}

function createNewOrderline( $order_id_in )
{
    global $tables;

    $database = connectToDatabase();
    $query = "INSERT INTO ".$tables['orderlines']." VALUES ( NULL, ".$order_id_in.", 1, 0 )";
    mysqlQuery( $query, $database );

    $query = "SELECT id FROM ".$tables['orderlines']." WHERE order_id=".$order_id_in;
    $result = mysqlQuery( $query, $database );
    $result_array = mysql_fetch_array( $result );
    return $result_array[0];
}

function getCurrentOrderline( $order_id_in )
{
    if( $_GET['orderline_id'] )
    {
        return $_GET['orderline_id'];
    }

    global $tables;

    $database = connectToDatabase();
    $query = "SELECT id FROM ".$tables['orderlines']." WHERE order_id=".$order_id_in;
    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows <= 0 )
    {
        return createNewOrderline( $order_id_in );
    }

    for( $row = 0; $row < $rows; $row++ )
    {
        $result_array = mysql_fetch_array( $result );
        $orderline_id = $result_array[0];

        if( !isOrderlineFull( $orderline_id ) )
        {
            return $orderline_id;
        }
    }
    
    orderComplete( $orderline_id, $order_id, 0, 1 );
    return -1;
}

function isOrderlineFull( $orderline_id_in )
{
    global $tables;

    $database = connectToDatabase();
    $query = "SELECT ".$tables['classes'].".name, ".$tables['classes'].".id,
            ".$tables['components'].".name, ".$tables['orderlines'].".order_id, ".$tables['classes'].".priority FROM ".$tables['selectedcomponents']." JOIN
            (".$tables['classes'].", ".$tables['families'].", ".$tables['components'].", ".$tables['orders'].", ".$tables['orderlines']." ) ON
            ( ".$tables['classes'].".id=".$tables['families'].".class_id AND
            ".$tables['families'].".id=".$tables['components'].".family_id AND
            ".$tables['components'].".id=".$tables['selectedcomponents'].".component_id AND
            ".$tables['selectedcomponents'].".orderline_id=".$tables['orderlines'].".id AND
            ".$tables['orderlines'].".order_id=".$tables['orders'].".id )
            WHERE ".$tables['selectedcomponents'].".orderline_id=".$orderline_id_in."
            ORDER BY ".$tables['classes'].".priority";

    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows <= 0 )
    {
        return 0;
    }

    for( $row = 0; $row < $rows; $row++ )
    {
        $result_array = mysql_fetch_alias_array( $result );
    }

    $priority = $result_array[$tables['classes'].'.priority'] + 1;
    $query = "SELECT id FROM ".$tables['classes']." WHERE priority=".$priority;
    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows <= 0 )
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

function getCurrentClass( $orderline_id_in )
{
    global $tables;

    $database = connectToDatabase();
    $query = "SELECT ".$tables['classes'].".name, ".$tables['classes'].".id,
            ".$tables['classes'].".priority,
            ".$tables['components'].".name, ".$tables['orderlines'].".order_id FROM ".$tables['selectedcomponents']." JOIN
            (".$tables['classes'].", ".$tables['families'].", ".$tables['components'].", ".$tables['orders'].", ".$tables['orderlines']." ) ON
            ( ".$tables['classes'].".id=".$tables['families'].".class_id AND
            ".$tables['families'].".id=".$tables['components'].".family_id AND
            ".$tables['components'].".id=".$tables['selectedcomponents'].".component_id AND
            ".$tables['selectedcomponents'].".orderline_id=".$tables['orderlines'].".id AND
            ".$tables['orderlines'].".order_id=".$tables['orders'].".id )
            WHERE ".$tables['selectedcomponents'].".orderline_id=".$orderline_id_in."
            ORDER BY ".$tables['classes'].".priority";
    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows <= 0 )
    {
        $query_2 = "SELECT id FROM ".$tables['classes']." WHERE priority=1";
        return mysqlGetSingleValue( $query_2 );
    }

    for( $row = 0; $row < $rows; $row++ )
    {
        $result_array = mysql_fetch_alias_array( $result );
    }

    $priority = $result_array[$tables['classes'].'.priority'] + 1;
    $query = "SELECT id FROM ".$tables['classes']." WHERE priority=".$priority;
    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows <= 0 )
    {
        return createNewOrderline( $result_array[$tables['orderline'].'.order_id'] );
    }
    else
    {
        $result_array = mysql_fetch_array( $result );
        return $result_array[0];
    }
}

function getCurrentOrder()
{
    global $tables;

    $database = connectToDatabase();
    $query = "SELECT id FROM ".$tables['orders']." WHERE account_id=".$_SESSION['id']." AND placed_date IS NULL";
    $result = mysqlQuery( $query, $database );
    $rows = mysql_num_rows( $result );

    if( $rows <= 0 )
    {
        return createNewOrder( $database );
    }
    else if( $rows == 1 )
    {
        $result_array = mysql_fetch_array( $result );
        return $result_array[0];
    }
    else
    {
        while( $result_array = mysql_fetch_array( $result ) )
        {
            $query_2 = "DELETE FROM ".$tables['orders']." WHERE id=".$result_array[0];
            mysqlQuery( $query_2, $database );
        }

        return createNewOrder( $database );
    }
}

function createNewOrder( $database )
{
    global $tables;

    $query = "SELECT * FROM ".$tables['accounts']." WHERE id=".$_SESSION['id'];
    $result = mysqlQuery( $query, $database );
    $account_information = mysql_fetch_array( $result );

    $query = "INSERT INTO ".$tables['orders']." VALUES ( NULL,
        '".$account_information['first_name']."',
        '".$account_information['last_name']."',
        '".$account_information['middle_initial']."',
         ".$_SESSION['id'].",
        '".$account_information['street_address']."',
        '".$account_information['city']."',
        '".$account_information['state']."',
        '".$account_information['zip_code']."',
           NULL, 1, NULL, NULL, NULL, NULL, 0
         )";
    $result = mysqlQuery( $query, $database );

    if( !$result || mysql_error() )
    {
        return -1;
    }
    else
    {
        $query = "SELECT id FROM ".$tables['orders']." WHERE account_id=".$_SESSION['id']." ORDER BY id DESC LIMIT 1";
        return mysqlGetSingleValue( $query );
    }
}

function displayPreviouslySelectedComponentList($orderline_id_in, $redirect_in=null)
{
    global $tables, $assembly_fee;

    $query = "SELECT ".$tables['classes'].".name, ".$tables['classes'].".id,
            ".$tables['components'].".name, component.price, ".$tables['selectedcomponents'].".incompatible_flag FROM ".$tables['selectedcomponents']." JOIN
            (".$tables['classes'].", ".$tables['families'].", ".$tables['components']." ) ON
            ( ".$tables['classes'].".id=".$tables['families'].".class_id AND
            ".$tables['families'].".id=".$tables['components'].".family_id AND
            ".$tables['components'].".id=".$tables['selectedcomponents'].".component_id  )
            WHERE ".$tables['selectedcomponents'].".orderline_id=".$orderline_id_in."
            ORDER BY ".$tables['classes'].".priority";

    $result = mysqlQuery( $query );
    $num_rows = mysql_num_rows( $result );

    echo "<table border='1' class='cart'>";

    $is_flagged = 0;
    $price = $assembly_fee;

    for( $index = 0; $index < $num_rows; $index++)
    {
        $result_array = mysql_fetch_alias_array( $result );
        $price += $result_array[$tables['components'].'.price'];
         echo "<tr>
                <td>
                    <div class='configuration_class_return_hyperlink'>
                        <span class='configuration_class_return_hyperlink_bold'>
                            <a href='configuration_system.php?return_to_class=".$result_array[$tables['classes'].'.id']."&orderline_id=".$orderline_id_in."&finished=".$redirect_in."'>".$result_array[$tables['classes'].'.name']."</a>
                            <span class='colon'>:</span>
                        </span></div>
                </td>
                <td>
                    <span class='cart_component'>
                        ";
                    if( $result_array[$tables['selectedcomponents'].'.incompatible_flag'] )
                    {
                        $is_flagged = 1;
                        echo "<span class='flagged'>*";
                    }
                    echo $result_array[$tables['components'].'.name'];

                    if( $result_array[$tables['selectedcomponents'].'.incompatible_flag'] )
                    {
                        echo "</span>";
                    }
                    echo "
                    </span>
                </td>
                <td>
                    <p class='cart_price'>
                        $".number_format( $result_array[$tables['components'].'.price'], 2, '.', ',' )."
                    </p>
                </td>
                </tr>";
    }

    echo "<tr><td></td><td><span class='cart_assembly_fee'>Item Assembly</span></td><td><p class='cart_price_assembly'>$".number_format( $assembly_fee, 2, '.', ',' )."</p></td></tr>";
    echo "<tr><td></td><td><span class='cart_total'>Item Price</span></td><td><p class='cart_price_total'>$".number_format( $price, 2, '.', ',' )."</p></td></tr>";

    echo "</table>";

    $query = "SELECT price FROM orderline WHERE id=".$orderline_id_in;
    $db_price = mysqlGetSingleValue( $query, $database );

    if( intval( $price * 100 ) !== intval( $db_price * 100 ) )
    {
        renderError( "The stored price and the calculated price are different; you will be charged $".number_format( $db_price, 2, '.', ',' )." for this item.  Please contact customer service and provide the following information to customer service: orderline.id=".$orderline_id_in );
    }

    if( $is_flagged == 1 )
    {
        renderError("You must re-select items for the above red area(s).");
    }
}

function displayConfiguredComponentList( $class_id_in, $orderline_id_in )
{
    global $tables;

    $database = connectToDatabase();

    $query = "SELECT component.id
                FROM orderline
                JOIN ( selectedcomponent, component, family, class )
                ON ( selectedcomponent.component_id=component.id
                    AND orderline.id=selectedcomponent.orderline_id
                    AND component.family_id=family.id
                    AND family.class_id=class.id)
                WHERE orderline.id=".$orderline_id_in."
                    AND class.id
                        IN (
                            SELECT independent_id
                            FROM dependability
                            WHERE dependent_id=".$class_id_in.") ";  //AND selectedcomponent.flag=0
    $result = mysqlQuery( $query, $database );

    $rows = mysql_num_rows( $result );

    $good_components = array();
    $good_components['id'] = array();
    $good_components['name'] = array();
    $good_components['price'] = array();
    $times = array();

    $components = array();
    $components['id'] = array();
    $components['name'] = array();
    $components['price'] = array();

    if( mysql_num_rows( $result ) < 1 )
    {
        $query = "SELECT id,name,price FROM component WHERE family_id IN ( SELECT id FROM family WHERE class_id=".$class_id_in." )";
        $result = mysqlQuery( $query, $database );

        if( mysql_num_rows( $result ) > 0 )
        {
            while( $result_array = mysql_fetch_array( $result ) )
            {
                $components['id'][] = $result_array['id'];
                $components['name'][] = $result_array['name'];
                $components['price'][] = $result_array['price'];
            }
        }
    }
    else
    {
        while( $result_array = mysql_fetch_array( $result ) )
        {
            $query = "SELECT id,name,price
              FROM component
              WHERE family_id
                IN (
                    SELECT family_id
                    FROM familycompatibility
                    WHERE component_id=".$result_array[0]." )
                        AND id
                            NOT IN (
                                SELECT noncompatible_component_id
                                FROM componentnoncompatibility
                                WHERE component_id=".$result_array[0]." ) AND family_id IN ( SELECT id FROM family WHERE class_id=".$class_id_in.")";
             $result_2 = mysqlQuery( $query, $database );
             
             if( mysql_num_rows( $result_2 ) > 0 )
             {
                 while( $result_array_2 = mysql_fetch_array( $result_2 ) )
                 {
                     $good_components['id'][] = $result_array_2['id'];
                     $good_components['name'][] = $result_array_2['name'];
                     $good_components['price'][] = $result_array_2['price'];

                     if( !isset( $times[$result_array_2['id']] ) )
                     {
                         $times[$result_array_2['id']] = 1;
                     }
                     else
                     {
                         $times[$result_array_2['id']]++;
                     }
                 }
             }
        }
    }

    for( $component = 0; $component < count( $good_components['id'] ); $component++ )
    {
        if( $times[$good_components['id'][$component]] == $rows && !in_array($good_components['id'][$component], $components['id']) )
        {
            $components['id'][] = $good_components['id'][$component];
            $components['name'][] = $good_components['name'][$component];
            $components['price'][] = $good_components['price'][$component];
        }
    }

    echo "<form class='configuration_drop_down' action='configuration_system.php' method='post'>
            <p><select name='configuration_system_name' onchange='displayDescription(this)'>";

    //$query_2 = "SELECT required FROM ".$tables['classes']." WHERE id=".$class_id_in;
    $result_2 = mysqlGetSingleValue($query_2, $database);

     for( $component = 0; $component < count( $components['id'] ); $component++ )
    {
        if( $component == 0 )
        {
            $first_id = $components['id'][$component];
        }

        $query = "SELECT orderline.id FROM orderline JOIN ( selectedcomponent, component ) ON ( orderline.id=selectedcomponent.orderline_id AND selectedcomponent.component_id=component.id ) WHERE component.id=".$components['id'][$component]." AND orderline.id=".$orderline_id_in;
        $result = mysqlQuery( $query, $database );
        $is_selected = mysql_num_rows( $result );

        echo "<option value='".$components['id'][$component]."' ".($is_selected?"selected='selected'":"").">".$components['name'][$component]." - \$".number_format( $components['price'][$component], 2, '.', ',' )."</option>";
    }

    if(!isset($first_id))
    {
        echo"<option>EMPTY</option>";
    }

    echo "<input type='hidden' name='previous_class' value='".$class_id_in."' />";
    echo "<input type='hidden' name='orderline_id' value='".$orderline_id_in."' />";
    echo "<input type='hidden' name='submit' value='1' />";
    echo "<input type='hidden' name='finished' value='".$_GET['finished']."' />";
    echo "<input type='submit' value='Submit' />";
    echo "</select></p></form>";
    echo "<div class='component_description'><iframe class='component_description_box' id='description_frame' src='http://oc.ericneill.com/get_description.php?id=".$first_id."'></iframe></div>";
}

function updateFlags( $class_id_in, $database )
{
    setIncompatibleFlag( $_POST['orderline_id'], $_POST['configuration_system_name'], 0 );

    $database = connectToDatabase();

    $query = "SELECT component.id
                FROM orderline
                JOIN ( selectedcomponent, component, family, class )
                ON ( selectedcomponent.component_id=component.id
                    AND orderline.id=selectedcomponent.orderline_id
                    AND component.family_id=family.id
                    AND family.class_id=class.id)
                WHERE orderline.id=".$_POST['orderline_id']."
                    AND class.id
                        IN (
                            SELECT dependent_id
                            FROM dependability
                            WHERE independent_id=".$class_id_in.") ";  //AND selectedcomponent.flag=0
    $result = mysqlQuery( $query, $database );

    $rows = mysql_num_rows( $result );

    //debug( "THIS COMPONENT", $_POST['configuration_system_name'] );

    if( mysql_num_rows( $result ) >= 1 )
    {
        while( $result_array = mysql_fetch_array( $result ) )
        {
            $query_2 = "SELECT component_id FROM familycompatibility WHERE family_id=(SELECT family_id FROM component WHERE id=".$result_array[0]." LIMIT 1)";
            //debug( "query_2", $query_2 );
            $result_2 = mysqlQuery( $query_2, $database );
            $families = array();

            while( $result_array_2 = mysql_fetch_array( $result_2 ) )
            {
                $families[] = $result_array_2[0];
            }

           // debugArray( "families", $families );

            $component_id = $result_array[0];
            //debug( "component_id", $component_id );

            if( mysql_num_rows( $result_2 ) < 1 )
            {
                setIncompatibleFlag( $_POST['orderline_id'], $component_id, 1 );
                //debug( "flag1", $component_id );
                continue;
            }

            //$query_3 = "SELECT family_id FROM component WHERE id=".$component_id;
            //$family_id = mysqlGetSingleValue( $query_3, $database );

            if( !in_array( $_POST['configuration_system_name'], $families ) )
            {
                setIncompatibleFlag( $_POST['orderline_id'], $component_id, 1 );
                //debug( "flag2", $component_id );
                continue;
            }

            $query_4 = "SELECT id FROM componentnoncompatibility WHERE component_id=".$component_id." AND noncompatible_component_id=".$_POST['configuration_system_name'];
            $result_4 = mysqlQuery( $query_4, $database );

            if( mysql_num_rows( $result_4 ) > 0 )
            {
                setIncompatibleFlag( $_POST['orderline_id'], $component_id, 1 );
                //debug( "flag3", $component_id );
                continue;
            }

            //setIncompatibleFlag( $_POST['orderline_id'], $component_id, 0 );
            //debug( "unflag", $component_id );
            $query_99 = "SELECT component.id
                FROM orderline
                JOIN ( selectedcomponent, component, family, class )
                ON ( selectedcomponent.component_id=component.id
                    AND orderline.id=selectedcomponent.orderline_id
                    AND component.family_id=family.id
                    AND family.class_id=class.id)
                WHERE orderline.id=".$_POST['orderline_id']."
                    AND class.id
                        IN (
                            SELECT independent_id
                            FROM dependability
                            WHERE dependent_id=(SELECT class_id FROM family WHERE id=(SELECT family_id FROM component WHERE id=".$component_id.")))";
            //debug( "query_99", $query_99 );
            $result_99 = mysqlQuery( $query_99, $database );

            if( mysql_num_rows( $result_99 ) >= 1 )
            {
                $good = 1;

                while( $result_array_99 = mysql_fetch_array( $result_99 ) )
                {
                    $query_21 = "SELECT family_id FROM familycompatibility WHERE component_id=".$result_array_99[0];
                    //debug( "query_21", $query_21 );
                    $result_21 = mysqlQuery( $query_21, $database );
                    $families = array();

                    while( $result_array_21 = mysql_fetch_array( $result_21 ) )
                    {
                        $families[] = $result_array_21[0];
                    }

                    $component_id1 = $result_array_99[0];
                    //debug( "component_id", $component_id1 );

                    if( mysql_num_rows( $result_21 ) < 1 )
                    {
                        //debug( "ungood1" );
                        $good = 0;
                        continue;
                    }

                    //$query_3 = "SELECT family_id FROM component WHERE id=".$component_id;
                    //$family_id = mysqlGetSingleValue( $query_3, $database );

                    $query_98 = "SELECT family_id FROM component WHERE id=".$component_id;
                    $family = mysqlGetSingleValue( $query_98, $database );

                    //debugArray( "families", $families );
                    //debug( "family", $family );

                    if( !in_array( $family, $families ) )
                    {
                        //debug( "ungood2" );
                        $good = 0;
                        continue;
                    }

                    $query_41 = "SELECT id FROM componentnoncompatibility WHERE component_id=".$component_id1." AND noncompatible_component_id=".$component_id;
                    $result_41 = mysqlQuery( $query_41, $database );

                    if( mysql_num_rows( $result_41 ) > 0 )
                    {
                        //debug( "ungood3" );
                        //setIncompatibleFlag( $_POST['orderline_id'], $component_id, 1 );
                        $good = 0;
                        continue;
                    }
                }

                //unflag if all while loops are successful
                if( $good == 1 )
                {
                    setIncompatibleFlag( $_POST['orderline_id'], $component_id, 0 );
                    //debug( "unflag99", $component_id );
                }
                else
                {
                    setIncompatibleFlag( $_POST['orderline_id'], $component_id, 1 );
                    //debug( "flag99", $component_id );
                }
            }
        }
    }
}

function setIncompatibleFlag( $orderline_id_in, $component_id_in, $set_in )
{
    if( !$set_in )
    {
        $set_in = "0";
    }

    global $tables;

    $query = "UPDATE ".$tables['selectedcomponents']." SET 
                incompatible_flag=".$set_in."  WHERE orderline_id=".$orderline_id_in." AND component_id=".$component_id_in." LIMIT 1";

    $result = mysqlQuery( $query );
}

function displayShippingInfoFromPOST($order_id_in)
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

    echo "</select></td>";




    echo"<input type='hidden' name='order_id' value='".$order_id_in."' />
    <tr><td><input type='submit' value='SUBMIT SHIPPING INFO' name='ship_submit' /></td><td></td>
    </table>
    </form>";


    echo"<form action='configuration_system.php' method='post'>
    <input type='hidden' name='order_id' value='".$order_id_in."' />
    <input type='submit' value='CANCEL' name='Cancel'/>
    </form>";
}

    function validateZipCode( $zip_code_in )
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


function displayShippingInfo( $order_id_in )
{
    global $tables;

    $database = connectToDatabase();

    $query = "SELECT * FROM ".$tables['shippinginfo'];
    $result = mysqlQuery( $query );

    $query_2 = "SELECT shipping_first_name, shipping_last_name,shipping_middle_initial, shipping_address, shipping_city, shipping_state, shipping_zip_code FROM ".$tables['orders']." WHERE id=".$order_id_in;
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

    echo "</select></td>";




    echo"<input type='hidden' name='order_id' value='".$order_id_in."' />
    <tr><td><input type='submit' value='SUBMIT SHIPPING INFO' name='ship_submit' /></td><td></td>
    </table>
    </form>";


    echo"<form action='configuration_system.php' method='post'>
    <input type='hidden' name='order_id' value='".$order_id_in."' />
    <input type='submit' value='CANCEL' name='Cancel'/>
    </form>";



}



?>
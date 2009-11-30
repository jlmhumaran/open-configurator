<?
require_once 'header.php';
require_once 'login.php';
require_once 'customer_functions.php';

if( isLoggedIn() )
{
    $order = getCurrentOrder();

    if( !$order )
    {
        noItemsInCart();
    }
    else
    {
        $database = connectToDatabase();
        $query = "SELECT id FROM orderline WHERE order_id=".$order;
        $result = mysqlQuery( $query, $database );

        if( mysql_num_rows( $result ) < 1 )
        {
            noItemsInCart();
        }

        while( $result_array = mysql_fetch_array( $result ) )
        {
            $orderlines++;

            displayOrderline( $result_array[0], $orderlines );

            if( !isOrderlineFull( $result_array[0] ) )
            {
                $incomplete++;
                renderError( "This item is not full!" );
            }
        }

        if( !$orderlines )
        {
            noItemsInCart();
        }

        if( !$incomplete )
        {
            orderComplete( 0, $order, 1 );
        }
        else
        {
            renderError( "Please complete all incomplete items before checking out." );
        }

        echo "<br/><br/>";
    }
}

function displayOrderline( $orderline_id_in, $orderline_number_in )
{
    //SELECT component.name,component.description,class.id,class.name
    //FROM selectedcomponent
    //JOIN ( component, family, class )
    //ON ( selectedcomponent.component_id=component.id
    //AND component.family_id=family.id
    //AND family.class_id=class.id )
    //WHERE orderline_id=3

    echo "<div class='cart_item'>Item #".$orderline_number_in."</div>";
    displayPreviouslySelectedComponentList( $orderline_id_in, 'cart.php' );
    echo "<br/>";
}

function noItemsInCart()
{
    echo "There are currently no items in your cart.  {CLICK HERE TO ORDER SOME}<br/>";
    addSpacing();
    require_once 'footer.php';
    die();
}

addSpacing();
require_once 'footer.php';
?>
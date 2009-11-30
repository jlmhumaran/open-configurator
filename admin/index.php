<?
require_once '../config.php';
require_once 'admin_header.php';
require_once '../utilities.php';
require_once '../account.php';

/*JAVASCRIPT CHECK ALL CHECKBOX
<script language="javascript">
function checkAll(checkname, exby) {
  for (i = 0; i < checkname.length; i++)
  checkname[i].checked = exby.checked? true:false
}

function unchecked(checkname, stuff)
{
	if( stuff.checked == false )
	{
		document.getElementsByName( "all" )[0].checked = false;
	}
	else if( stuff.checked == true )
	{
		var go = true;
		
		for( i = 0; i < checkname.length; i++ )
		{
			if( checkname[i].checked == false )
			{
				go = false;
			}
		}
		
		if( go == true )
		{
			document.getElementsByName( "all" )[0].checked = true;
		}
	}
}
</script>

<form name ="form1">+
  <input type="checkbox" name="checkGroup" value ="first"  onClick="unchecked(document.form1.checkGroup,this)">First<br>
  <input type="checkbox" name="checkGroup" value ="second"  onClick="unchecked(document.form1.checkGroup,this)">Second<br>
  <input type="checkbox" name="checkGroup" value ="third"  onClick="unchecked(document.form1.checkGroup,this)">Third<br>
  <input type="checkbox" name="checkGroup" value ="fourth" onClick="unchecked(document.form1.checkGroup,this)">Fourth<br>
  <input type="checkbox" name="all" onClick="checkAll(document.form1.checkGroup,this)">Check/Uncheck All<br>
</form>*/


$_GET = array_map( 'mysql_real_escape_string', $_GET );
//$_POST = array_map( 'mysql_real_escape_string', $_POST ); 

if( $_GET )
{	
	if( $_GET['edit_class'] )
	{
		$database = connectToDatabase();
	
		if( !$database )
		{
			return;
		}
		
		$query = "SELECT * FROM ".$tables['classes']." WHERE id='".$_GET['edit_class']."'";
		$result = mysqlQuery( $query, $database );
		
		if( !$result )
		{
			renderError( "That component's information could not be loaded!" );
			return;
		}
		
		$result_array = mysql_fetch_array( $result );
		
		displayClassForm( $result_array['name'], 1 );
	}
	else if( $_GET['edit_family'] )
	{
		$database = connectToDatabase();
	
		if( !$database )
		{
			return;
		}
		
		$query = "SELECT * FROM ".$tables['families']." WHERE id='".$_GET['edit_family']."'";
		$result = mysqlQuery( $query, $database );
		
		if( !$result )
		{
			renderError( "That families' information could not be loaded!" );
			return;
		}
		
		$result_array = mysql_fetch_array( $result );
		
		displayFamilyForm( $result_array['name'], $result_array['class_id'] );
	}
	else if( $_GET['edit_component'] )
	{
		$database = connectToDatabase();
	
		if( !$database )
		{
			return;
		}
		
		$query = "SELECT * FROM ".$tables['components']." WHERE id='".$_GET['edit_component']."'";
		$result = mysqlQuery( $query, $database );
		
		if( !$result )
		{
			renderError( "That components' information could not be loaded!" );
			return;
		}
		
		$result_array = mysql_fetch_array( $result );
		
		displayComponentForm( $result_array['name'], $result_array['family_id'], $result_array['price'], $result_array['description'] );
	}
	else if( $_GET['remove_class'] ) //case 9
	{
		echo "<form method='post' action='index.php'>";
		echo "<p><span class='delete_component_warning'>The class you are about to delete (".$_GET['name'].") CANNOT be recovered if you continue.  Select the appropriate option and click Submit if you wish to continue.</span></p><br/>";
		echo "<select name='delete'>";
		echo "	<option value='1'>I understand the risks; delete class ".$_GET['name']."</option>";
		echo "	<option value='0' selected='seleted'>I do not wish to delete this item.  Please take me back.</option>";
		echo "<input type='hidden' name='submitted' value='1' />";
		echo "<input type='hidden' name='next_page' value='9' />";
		echo "<input type='hidden' name='id' value='".$_GET['remove_class']."' />";
		echo "<br/><input type='submit' value='Submit' />";
		echo "</form>";
	}
	else if( $_GET['remove_family'] ) //case 10
	{
		echo "<form method='post' action='index.php' />";
		echo "<p><span class='delete_component_warning'>The family you are about to delete (".$_GET['name'].") CANNOT be recovered if you continue.  Select the appropriate option and click Submit if you wish to continue.</span></p><br/>";
		echo "<select name='delete' />";
		echo "	<option value='1'>I understand the risks; delete family ".$_GET['name']."</option>";
		echo "	<option value='0' selected>I do not wish to delete this item.  Please take me back.</option>";
		echo "<input type='hidden' name='submitted' value='1' />";
		echo "<input type='hidden' name='next_page' value='10' />";
		echo "<input type='hidden' name='id' value='".$_GET['remove_family']."' />";
		echo "<br/><input type='submit' value='Submit' />";
		echo "</form>";
	}
	else if( $_GET['remove_component'] ) //case 11
	{
		echo "<form method='post' action='index.php' />";
		echo "<p><span class='delete_component_warning'><b><u>The component you are about to delete (".$_GET['name'].") CANNOT be recovered if you continue.  Select the appropriate option and click Submit if you wish to continue.</span></p><br/>";
		echo "<select name='delete' />";
		echo "	<option value='1'>I understand the risks; delete component ".$_GET['name']."</option>";
		echo "	<option value='0' selected>I do not wish to delete this item.  Please take me back.</option>";
		echo "<input type='hidden' name='submitted' value='1' />";
		echo "<input type='hidden' name='next_page' value='11' />";
		echo "<input type='hidden' name='id' value='".$_GET['remove_component']."' />";
		echo "<br/><input type='submit' value='Submit' />";
		echo "</form>";
	}
	else if ( $_GET['increase_priority'] ) 
	{
		global $tables;
	
		$database = connectToDatabase();
	
		if( !$database )
		{
			return 0;
		}
		
		increaseClassPriority($_GET['increase_priority'], $database);
		
		
	}
	else if( $_GET['decrease_priority'] )
	{
		global $tables;
	
		$database = connectToDatabase();
	
		if( !$database )
		{
			return 0;
		}
		
		decreaseClassPriority($_GET['decrease_priority'], $database);
		
		
	}
}
else if( $_POST['submitted'] )
{
	extract( $_POST );
	
	switch( $_POST['next_page'] )
	{
		case 1: //class name submitted
			displayClassForm( $name );
		break;
		
		case 2: //family name submitted
			createFamily( $name, $class_id );
		break;
		
		case 3: //component name submitted
			displayComponentForm( $name, $family_id );
		break;
		
		case 4: //class fully submitted
                        renderError( "Page 4 has been hit.  Please contact OC technical support to resolve this issue." );
			//createClass( $name, $required, $dependability );
		break;
		
		case 5: //component fully submitted
			if( $ignore )
			{
				$noncompatible_components = array();
			}
			
			createComponent( $name, $family_id, $price, $description, $compatible_families, $noncompatible_components );
		break;
		
		case 6: //update class; don't create a new one
			createClass( $name, /*$required,*/ $dependability, $id );
		break;
		
		case 7: //update family; don't create a new one
			createFamily( $name, $class_id,$id );
		break;
		
		case 8: //update component; don't create a new one
			if( $ignore )
			{
				$noncompatible_components = array();
			}
		
			createComponent( $name, $family_id, $price, $description, $compatible_families, $noncompatible_components, $id );
		break;
		
		case 9: //remove class
			if( $delete )
                        {
				removeClass( $id );
                        }
		break;
		
		case 10: //remove family
			if( $delete )
				removeFamily( $id );
		break;
		
		case 11: //remove component
			if( $delete )
				removeComponent( $id );
		break;
	}
}

displayComponentHierarchy();
addSpacing();

require_once '../footer.php';

function removeClass( $id )
{
	global $tables;
	$database = connectToDatabase();
	
	if( !$database )
	{
		return 0;
	}
	
	$query = "SELECT priority FROM ".$tables['classes']." WHERE id='".$id."'";
	$result = mysqlQuery( $query, $database );
	
	if( !$result )
	{
		renderError( "Deletion failed!" );
		return 0;
	}
	
	$result_array = mysql_fetch_array( $result );
	$priority = $result_array[0];
	
	$query = "DELETE FROM ".$tables['classes']." WHERE id='".$id."' LIMIT 1";
	$result = mysqlQuery( $query, $database );
	
	if( !$result )
	{
		renderError( "Deletion failed!" );
		return 0;
	}
	
	$query = "SELECT id FROM ".$tables['classes']." WHERE priority > ".$priority." ORDER BY priority";
	$result = mysqlQuery( $query, $database );
	
	if( !$result )
	{
		renderError( "Deletion failed!" );
		return 0;
	}
	
	$num_rows = mysql_num_rows( $result );
	
	if( $num_rows > 0 )
	{
		for( $row = 0; $row < $num_rows; $row++ )
		{
			$result_array = mysql_fetch_array( $result );
			$query = "UPDATE ".$tables['classes']." SET priority=priority-1 WHERE id=".$result_array[0];
			$result_2 = mysqlQuery( $query, $database );
				
			if( !$result_2 )
			{
				renderError( "Deletion failed!" );
				return 0;
			}
		}
	}
	
	
	
	echo "Deletion successful!<br/>";
	return 1;
}

function removeFamily( $id )
{
	global $tables;
	$database = connectToDatabase();
	
	if( !$database )
	{
		return 0;
	}
	
	$query = "DELETE FROM ".$tables['families']." WHERE id='".$id."' LIMIT 1";
	$result = mysqlQuery( $query, $database );
	
	if( !$result )
	{
		renderError( "Deletion failed!" );
		return 0;
	}
	else
	{
		echo "Deletion successful!<br/>";
		return 1;
	}
}

function removeComponent( $id )
{
	global $tables;
	$database = connectToDatabase();
	
	if( !$database )
	{
		return 0;
	}
	
	$query = "DELETE FROM ".$tables['components']." WHERE id='".$id."' LIMIT 1";
	$result = mysqlQuery( $query, $database );
	
	if( !$result )
	{
		renderError( "Deletion failed!" );
		return 0;
	}
	else
	{
		echo "Deletion successful!<br/>";
		return 1;
	}
}

function displayFamilyForm( $name_in, $class_id )
{
	?>
		<form method="post" action="index.php">
		<input type=hidden name=submitted value=1>
	<?
		echo "<input type='hidden' name='next_page' value='7' />";
		echo "<input type='hidden' name='class_id' value='".$class_id."' />";
		echo "<input type='hidden' name='id' value='".$_GET['edit_family']."' />";
		echo "Family Name:<input type='text' name='name' value='$name_in' /><br/>";
	?>
		<input type='submit' value='Submit' />
		</form>
	<?
}

function createComponent( $name_in, $family_id_in, $price_in, $description_in, $compatible_families_in, $noncompatible_components_in, $update_id=null )
{
	global $tables;
	
	if( !is_numeric( $price_in ) )
	{
		renderError( "You entered an invalid price value." );
		return 0;
	}
	
	sscanf( $price_in, "%u.%u", $part_before_decimal, $part_after_decimal );
	
	if( $part_before_decimal < 0 || ( $part_after_decimal && 
		( $part_after_decimal > 99 || $part_after_decimal < 0 )  ) )
	{
		renderError( "You entered an invalid price value." );
		return 0;
	}
	
	$database = connectToDatabase();
	
	if( !$database )
	{
		return 0;
	}
	
	if( $update_id > 0 )
	{
		$query = "UPDATE ".$tables['components']." SET ".
						 "name='".$name_in."', ".
						 "price=".$price_in.", ".
						 "description='".$description_in."' WHERE id=".$update_id;
	}
	else
	{
		$query = "INSERT INTO ".$tables['components']." VALUES ( NULL, '".$name_in."', ".$family_id_in.", ".$price_in.", '".$description_in."' )";
	}
	$result = mysqlQuery( $query, $database );
	
	if( $update_id <= 0 && !$result )
	{
		renderError( "The component could not be added!" );
		return 0;
	}
	else if( $update_id > 0 && !$result )
	{
		renderError( "The component could not be updated!" );
		return 0;
	}
	
	//START OF PASTED FAMILY SHIT
	$query = "SELECT id FROM ".$tables['components']." WHERE name='".$name_in."'";
	$result = mysqlQuery( $query, $database );
	$result_array = mysql_fetch_array( $result );
	$current_id = $result_array[0];
	
	$query = "SELECT family_id FROM ".$tables['familycompatibilities']." WHERE component_id=".$current_id;
	$result = mysqlQuery( $query, $database );
	
	if( mysql_error() )
	{
		return 0;
	}
	
	//$result_array = mysql_fetch_array( $result );
	$num_rows = mysql_num_rows( $result ); //number of dependablity rows
	$current_compatibilities = array();
	
	for( $row = 0; $row < $num_rows; $row++ )
	{
		$result_array = mysql_fetch_array( $result );
		$current_compatibility = $result_array[0];
		$current_compatibilities[] = $current_compatibility; //IDs of current dependabilities
		
		if( !$compatible_families_in || ( $compatible_families_in && !in_array( $current_compatibility, $compatible_families_in ) ) )
		{
			//remove $current_dependability from table
			$query_2 = "DELETE FROM ".$tables['familycompatibilities']." WHERE family_id=".$current_compatibility." AND component_id=".$current_id;
			$result_2 = mysqlQuery( $query_2, $database );
			
			if( !$result_2 || mysql_error() )
			{
				renderError( "Family compatibility could not be removed!" );
			}
			
			$query_2 = "SELECT component.family_id,component.id FROM ".$tables['componentnoncompatibilities']." JOIN ".$tables['components']." ON ".$tables['componentnoncompatibilities'].".noncompatible_component_id=".$tables['components'].".id WHERE ".$tables['components'].".family_id=".$current_compatibility." AND ".$tables['componentnoncompatibilities'].".component_id=".$current_id;
			$result_2 = mysqlQuery( $query_2, $database );
			
			if( $noncompatible_components_in )
			{
				while( $array = mysql_fetch_array( $result_2 ) )
				{	
					$size = count( $noncompatible_components_in );
					
					for( $component = 0; $component < $size; $component++ )
					{
						if( $noncompatible_components_in[$component] == $array['id'] )
						{
							unset( $noncompatible_components_in[$component] );
						}
					}
				}
			}
			
			$query_3 = "DELETE ".$tables['componentnoncompatibilities'].".* FROM ".$tables['componentnoncompatibilities']." JOIN ".$tables['components']." ON ".$tables['componentnoncompatibilities'].".noncompatible_component_id=".$tables['components'].".id WHERE ".$tables['components'].".family_id=".$current_compatibility." AND component.id=".$current_id;
			$result_3 = mysqlQuery( $query_3, $database );
			
			if( !$result_3 || mysql_error() )
			{
				renderError( "Component compatibilities could not be removed!" );
			}
		}
	}
	
	if( $compatible_families_in )
	{
		foreach( $compatible_families_in as $compatibility )
		{
			if( ($current_compatibilities && !in_array( $compatibility, $current_compatibilities )) || !$current_compatibilities )
			{
				$query_2 = "INSERT INTO ".$tables['familycompatibilities']." VALUES ( NULL, ".$current_id.", ".$compatibility." )";
				$result_2 = mysqlQuery( $query_2, $database );
				
				if( !$result_2 || mysql_error() )
				{
					renderError( "Family compatibility could not be added!" );
				}
			}
		}
	}
	//END OF PASTED FAMILY SHIT
	
	//START OF PASTED COMPONENT SHIT	
	//might need to change to WHERE compatible_component_id=!!!
	if( $compatible_families_in )
	{
		//$query = "SELECT noncompatible_component_id FROM ".$tables['componentnoncompatibilities']." WHERE component_id=".$current_id;
		$query = "SELECT noncompatible_component_id,family_id FROM ".$tables['componentnoncompatibilities']." JOIN ".$tables['components']." ON ".$tables['componentnoncompatibilities'].".noncompatible_component_id=".$tables['components'].".id WHERE ".$tables['componentnoncompatibilities'].".component_id=".$current_id;
		$result = mysqlQuery( $query, $database );
		
		if( mysql_error() )
		{
			return 0;
		}
		
		//$result_array = mysql_fetch_array( $result );
		$num_rows = mysql_num_rows( $result ); //number of dependablity rows
		$current_compatibilities = array();
		
		for( $row = 0; $row < $num_rows; $row++ )
		{
			$result_array = mysql_fetch_array( $result );
			$current_compatibility = $result_array[0];
			$current_compatibilities[] = $current_compatibility; //IDs of current dependabilities
			
			if( !$noncompatible_components_in || ( $noncompatible_components_in && !in_array( $current_compatibility, $noncompatible_components_in ) ) )
			{
				//remove $current_dependability from table
				$query_2 = "DELETE FROM ".$tables['componentnoncompatibilities']." WHERE noncompatible_component_id=".$current_compatibility." AND component_id=".$current_id;
				$result_2 = mysqlQuery( $query_2, $database );
				
				if( !$result_2 || mysql_error() )
				{
					renderError( "Component compatibility could not be removed!" );
				}
			}
			else if( $compatible_families_in && !in_array( $result_array['family_id'], $compatible_families_in ) )
			{
				$query_2 = "DELETE FROM ".$tables['componentnoncompatibilities']." WHERE noncompatible_component_id=".$current_compatibility." AND component_id=".$current_id;
				$result_2 = mysqlQuery( $query_2, $database );
				
				if( !$result_2 || mysql_error() )
				{
					renderError( "Component compatibility could not be removed!" );
				}
			}
		}
		
		if( $noncompatible_components_in )
		{
			foreach( $noncompatible_components_in as $compatibility )
			{
				$query_2 = "SELECT family_id FROM ".$tables['components']." WHERE id=".$compatibility;
				$family_id = mysqlGetSingleValue( $query_2, $database );
				
				if( in_array( $family_id, $compatible_families_in ) )
				{
					if( ($current_compatibilities && !in_array( $compatibility, $current_compatibilities )) || !$current_compatibilities )
					{
						$query_2 = "INSERT INTO ".$tables['componentnoncompatibilities']." VALUES ( NULL, ".$current_id.", ".$compatibility." )";
						$result_2 = mysqlQuery( $query_2, $database );
						
						if( !$result_2 || mysql_error() )
						{
							renderError( "Dependability could not be added!" );
						}
					}
				}
			}
		}
	}
	else
	{
		$query_2 = "DELETE FROM ".$tables['componentnoncompatibilities']." WHERE component_id=".$current_id;
		$result_2 = mysqlQuery( $query_2, $database );
		
		if( !$result_2 || mysql_error() )
		{
			renderError( "Error deleting all components in the non-compatibility list!" );
		}
	}
	//END OF PASTED COMPONENT SHIT
	
	if( $update_id <= 0 )
	{
		echo "Component added successfully!";
	}
	else
	{
		echo "Component updated successfully!";
	}
	
	return 1;
}

function displayComponentForm( $name_in, $family_id_in, $price_in=null, $description_in=null )
{
	global $tables;
	$database = connectToDatabase();
	
	if( !$database )
	{
		return 0;
	}
	
	if( $_GET['edit_component'] )
	{
		$query = "SELECT id FROM ".$tables['components']." WHERE name='".$name_in."'";
		$component_id = mysqlGetSingleValue( $query, $database );
	}
	
	$query = "SELECT class_id FROM ".$tables['families']." WHERE id=".$family_id_in;
	$class_id = mysqlGetSingleValue( $query, $database );
	
	$query = "SELECT dependent_id FROM ".$tables['dependabilities']." WHERE independent_id=".$class_id;
	$result = mysqlQuery( $query, $database );
	//$dependent_classes = mysqlGetArray( $query, $database );
	
	$dependent_classes = array();
	$result_size = mysql_num_rows( $result );
	
	while( $dependent_class = mysql_fetch_array( $result ) )
	{
		$dependent_classes[] = $dependent_class[0];
	}
	
	if( $result_size > 0 )
	{
		$families = array();
		$family_names = array();
		
		foreach( $dependent_classes as $dependent_class )
		{
			$query = "SELECT id,name FROM ".$tables['families']." WHERE class_id=".$dependent_class;
			$result = mysqlQuery( $query, $database );
			
			while( $dependent_family = mysql_fetch_array( $result ) )
			{
				$families[] = $dependent_family['id'];
				$family_names[] = $dependent_family['name'];
			}
		}
		
		$components = array();
		$component_names = array();
		
		foreach( $families as $family )
		{
			$query = "SELECT id,name FROM ".$tables['components']." WHERE family_id=".$family;
			$result = mysqlQuery( $query, $database );
			
			while( $dependent_component = mysql_fetch_array( $result ) )
			{
				$components[] = $dependent_component['id'];
				$component_names[] = $dependent_component['name'];
			}
		}
		
		if( $_GET['edit_component'] )
		{
			$query = "SELECT family_id FROM ".$tables['familycompatibilities']." WHERE component_id=".$component_id;
			$result = mysqlQuery( $query, $database );
			$marked_families = array();
			
			while( $marked_family = mysql_fetch_array( $result ) )
			{
				$marked_families[] = $marked_family[0];
			}
			
			$query = "SELECT noncompatible_component_id FROM ".$tables['componentnoncompatibilities']." WHERE component_id=".$component_id;
			$result = mysqlQuery( $query, $database );
			$marked_components = array();
			
			while( $marked_component = mysql_fetch_array( $result ) )
			{
				$marked_components[] = $marked_component[0];
			}
		}
	}
	?>
		<form method="post" action='index.php' />
		<input type='hidden' name='submitted' value='1' />
		<input type='hidden' name='next_page' value='8' />
	<?
		echo "Component Name:<input type='text' name='name' value='$name_in' /><br/>";
		echo "<input type='hidden' name='id' value='".$_GET['edit_component']."' />";
		echo "<input type='hidden' name='family_id' value='".$family_id_in."' />";
		
		if( $_GET['edit_component'] > 0 )
		{
			echo "Price: $<input type='text' name='price' value='".str_replace( ",", "", number_format($price_in,2))."' /><br/><br/>";
			echo "Description: <br/><textarea name='description' cols=80 rows=4>".$description_in."</textarea><br/>";
		
			if( $dependent_classes )
			{
				echo "Families<br/>";
				
				for( $family = 0; $family < count( $families ); $family++ )
				{
					$family_id = $families[$family];
					$family_name = $family_names[$family];
					echo "<input type='checkbox' name='compatible_families[]' value='".$family_id."' ".( in_array( $family_id, $marked_families ) ? "checked" : "" )." />".$family_name."<br/>";
				}
				
				echo "<br/><br/>Components Not Compatible<br/>";
				echo "Ignore List<input type='checkbox' name='ignore' value='1' /><br/>";
				echo "<select multiple='multiple' name='noncompatible_components[]' size='10'>";
				
				for( $component = 0; $component < count( $components ); $component++ )
				{
					$component_id = $components[$component];
					$component_name = $component_names[$component];
					echo "<option value='".$component_id."' ".( in_array( $component_id, $marked_components ) ? "selected" : "" ).">".$component_name."</option><br/>";
				}
				
				echo "</select><br/>";
			}
		}
		else
		{
			echo "Price: $<input type='text' name='price' /><br/><br/>";
			echo "Description: <br/><textarea name='description' cols='80' rows='4'></textarea><br/>";
			
			if( $dependent_classes )
			{
				echo "Families<br/>";
				
				for( $family = 0; $family < count( $families ); $family++ )
				{
					$family_id = $families[$family];
					$family_name = $family_names[$family];
					echo "<input type='checkbox' name='compatible_families[]' value='".$family_id."' />".$family_name."<br/>";
				}
				
				echo "<br/><br/>Components Not Compatible<br/>";
				echo "Ignore List<input type='checkbox' name='ignore' value='1' /><br/>";
				echo "<select multiple='multiple' name='noncompatible_components[]' size='10'>";
				
				for( $component = 0; $component < count( $components ); $component++ )
				{
					$component_id = $components[$component];
					$component_name = $component_names[$component];
					echo "<option value='".$component_id."'>".$component_name."</option><br/>";
				}
				
				echo "</select><br/>";
			}
			else if( $_GET['edit_component'] )
			{
				echo "Universal!<br/>";
			}
		}
	?>
		<input type='submit' value='Submit' />
		</form>
	<?
}

function createFamily( $name_in, $class_id_in, $update_id = null )
{
	global $tables;
	
	$database = connectToDatabase();
	
	if( !$database )
	{
		return 0;
	}
	
	if( $update_id > 0 )
	{
		$query = "UPDATE ".$tables['families']." SET ".
						 "name='".$name_in."' WHERE id=".$update_id;
	}
	else
	{
		$query = "INSERT INTO ".$tables['families']." VALUES ( NULL, '".$name_in."', ".$class_id_in." )";
	}
	
	$result = mysqlQuery( $query, $database );
	
	if( $update_id <= 0 && !$result )
	{
		renderError( "The family could not be added!" );
		return 0;
	}
	else if( $update_id > 0 && !result )
	{
		renderError( "The family could not be updated!" );
		return 0;
	}
	
	if( $update_id > 0 )
	{
		echo "Family updated successfully!";
	}
	else
	{
		echo "Family added successfully!";
	}
	 
	return 1;
}

function createClass( $name_in, /*$required_in,*/ $dependability_in, $update_id=null )
{
	global $tables;
	$database = connectToDatabase();
	
	if( !$database )
	{
		return 0;
	}
	
	//SELECT MAX(column_name) FROM table_name
	$query = "SELECT MAX(priority) FROM ".$tables['classes'];
	$result = mysqlQuery( $query, $database );

	if( !$result )
	{
		return 0;
	}

	$result_array = mysql_fetch_array( $result );
	
	if( !$result_array[0] )
	{
		$priority = 1;
	}
	else
	{
		$priority = $result_array[0] + 1;
	}
	
/*	if( $required_in === "on" )
	{
		$required_in = 1;
	}
	else
	{
		$required_in = 0;
	}
	*/
	if( $update_id > 0 )
	{
		$query = "UPDATE ".$tables['classes']." SET ".
							"name='".$name_in."' "./*
							"required=".$required_in." */"WHERE id=".$update_id;
	}
	else
	{
		$query = "INSERT INTO ".$tables['classes']." VALUES ( NULL, '".$name_in."', ".$priority." )";
	}
	
	$final_result = mysqlQuery( $query, $database );
	
	if( $update_id <= 0 )
	{
		if( $result )
		{
			echo "Class created successfully!<br/>";
		}
		else
		{
			renderError( "The class could not be added." );
		}
	}
	else
	{
		if( $result )
		{
			echo "Class updated successfully!<br/>";
		}
		else
		{
			renderError( "The class could not be updated." );
		}
	}
	
	$query = "SELECT id FROM ".$tables['classes']." WHERE name='".$name_in."'";
	$result = mysqlQuery( $query, $database );
	$result_array = mysql_fetch_array( $result );
	$current_id = $result_array[0];
	
	$query = "SELECT id FROM ".$tables['dependabilities']." WHERE dependent_id=".$current_id;
	
	$result = mysqlQuery( $query, $database );
	
	if( mysql_error() )
	{
		return 0;
	}
	
	//$result_array = mysql_fetch_array( $result );
	$num_rows = mysql_num_rows( $result ); //number of dependablity rows
	$current_dependabilities = array();
	
	for( $row = 0; $row < $num_rows; $row++ )
	{
		$result_array = mysql_fetch_array( $result );
		$current_dependability = $result_array[0];
		$current_dependabilities[] = $current_dependability; //IDs of current dependabilities
		
		if( !$dependability_in || ( $dependability_in && !in_array( $current_dependability, $dependability_in ) ) )
		{
			//remove $current_dependability from table
			$query_2 = "DELETE FROM ".$tables['dependabilities']." WHERE id=".$current_dependability." LIMIT 1";
			//debug( "query_2", $query_2 );
			$result_2 = mysqlQuery( $query_2, $database );
			
			if( !$result_2 || mysql_error() )
			{
				renderError( "Dependability could not be removed!" );
			}
		}
	}
	
	if( $dependability_in )
	{
		foreach( $dependability_in as $dependability )
		{
			if( ($current_dependabilities && !in_array( $dependability, $current_dependabilities )) || !$current_dependabilities )
			{
				$query_2 = "INSERT INTO ".$tables['dependabilities']." VALUES ( NULL, ".$current_id.", ".$dependability." )";
				//debug( "query_2", $query_2 );
				$result_2 = mysqlQuery( $query_2, $database );
				
				if( !$result_2 || mysql_error() )
				{
					renderError( "Dependability could not be added!" );
				}
			}
		}
	}
	
	return $final_result;
}

function increaseClassPriority( $class_id_in, $database )
{
	//$class_id_in is the item we are moving up in priority.
	global $tables;
	
	$query = "SELECT priority FROM ".$tables['classes']." WHERE id=".$class_id_in;
	
	$result = mysqlQuery( $query, $database );
	
	if( mysql_error() )
	{
		return 0;
	}
	
	if( mysql_num_rows($result) != 1)
	{
		renderError( "Mysql failed to in increased class priority");
		return 0;
	}
	$result_array = mysql_fetch_array( $result );
	
	$next_highest_priority = ( $result_array[0] - 1 );
	if($next_highest_priority < 1)
	{
		renderError("You can not increase this classes priority it already is the highest priorty.");
		return 0;
	}
	
	$query = "SELECT id FROM ".$tables['classes']." WHERE priority=".$next_highest_priority;
	
	$result = mysqlQuery($query, $database);
	
	if( mysql_error() )
	{
		return 0;
	}
	
	if( mysql_num_rows($result) != 1)
	{
		renderError( "Mysql failed to in increased class priority NUMBER TWO");
		return 0;
	}
	$result_array = mysql_fetch_array( $result );
	$higher_class_id = $result_array[0];
	fixCompatibilityTableWhenPriorityChanges( $higher_class_id, $class_id_in, $database );
	
	$query = "UPDATE ".$tables['classes']." SET ".
					 "priority=0 WHERE id=".$higher_class_id;
					 
	
	mysqlQuery($query, $database);

	if( mysql_error() )
	{
		return 0;
	}
	
	if( mysql_affected_rows() != 1)
	{
		renderError( "Mysql failed to in increased class priority NUMBER THREE");
		return 0;
	}
	
	$query = "UPDATE ".$tables['classes']." SET ".
					 "priority=".$next_highest_priority." WHERE id=".$class_id_in;

	
	
	mysqlQuery($query, $database);
	
	if( mysql_error() )
	{
		return 0;
	}
	if( mysql_affected_rows() != 1)
	{
		renderError( "Mysql failed to in increased class priority NUMBER FOUR");
		return 0;
	}	
	
	$query = "UPDATE ".$tables['classes']." SET ".
					 "priority=".($next_highest_priority + 1)." WHERE id=".$higher_class_id;
	
	mysqlQuery($query, $database);
	
	if( mysql_error() )
	{
		return 0;
	}
		
	if( mysql_affected_rows() != 1)
	{
		renderError( "Mysql failed to in increased class priority NUMBER FIVE");
		return 0;
	}
		
	$query = "DELETE FROM ".$tables['dependabilities']." WHERE dependent_id=".$class_id_in." AND independent_id=".$higher_class_id;
	mysqlQuery( $query, $database );
	return 1;
}

function decreaseClassPriority( $class_id_in, $database )
{
	//$class_id_in is the item we are moving down in priority.\
	global $tables;
	
	
	$query = "SELECT priority FROM ".$tables['classes']." WHERE id=".$class_id_in;
	
	$result = mysqlQuery($query, $database);
	if( mysql_error() )
	{
		return 0;
	}
	
	if( mysql_num_rows($result) != 1)
	{
		renderError( "Mysql failed to in decrease class priority");
		return 0;
	}
	$result_array = mysql_fetch_array( $result );
	
	$next_lowest_priority = ( $result_array[0] + 1 );
	
	

	
	$query = "SELECT id FROM ".$tables['classes']." WHERE priority=".$next_lowest_priority;
	
	$result = mysqlQuery($query, $database);
	if( mysql_error() )
	{
		return 0;
	}
	
	if( mysql_num_rows($result) != 1)
	{
		renderError( "Mysql failed to in decrease class priority NUMBER TWO");
		return 0;
	}
	$result_array = mysql_fetch_array( $result );
	$lower_class_id = $result_array[0];
	fixCompatibilityTableWhenPriorityChanges( $class_id_in, $lower_class_id, $database );
	
	$query = "UPDATE ".$tables['classes']." SET ".
					 "priority=0 WHERE id=".$lower_class_id;
	
	mysqlQuery($query, $database);

	if( mysql_error() )
	{
		return 0;
	}
	
	if( mysql_affected_rows() != 1)
	{
		renderError( "Mysql failed to in decrease class priority NUMBER THREE");
		return 0;
	}
	
	$query = "UPDATE ".$tables['classes']." SET ".
					 "priority=".$next_lowest_priority." WHERE id=".$class_id_in;
	
	mysqlQuery($query, $database);
	
	if( mysql_error() )
	{
		return 0;
	}
	if( mysql_affected_rows() != 1)
	{
		renderError( "Mysql failed to in decrease class priority NUMBER FOUR");
		return 0;
	}	
	
	$query = "UPDATE ".$tables['classes']." SET ".
					 "priority=".($next_lowest_priority - 1)." WHERE id=".$lower_class_id;
	
	mysqlQuery($query, $database);
	
	if( mysql_error() )
	{
		return 0;
	}
		
	if( mysql_affected_rows() != 1)
	{
		renderError( "Mysql failed to in decrease class priority NUMBER FIVE");
		return 0;
	}
	
	$query = "DELETE FROM ".$tables['dependabilities']." WHERE dependent_id=".$lower_class_id." AND independent_id=".$class_id_in;
	mysqlQuery( $query, $database );
	return 1;
}

function fixCompatibilityTableWhenPriorityChanges( $upper_class_id_in, $lower_class_id_in, $database )
{
	global $tables;
	
	$query = "SELECT ".$tables['familycompatibilities'].".id FROM ".$tables['familycompatibilities']." JOIN (".$tables['families'].", ".$tables['classes'].") ON (".$tables['familycompatibilities'].".family_id=".$tables['families'].".id AND ".$tables['classes'].".id=".$tables['families'].".class_id) WHERE ".$tables['classes'].".id=".$lower_class_id_in;
	$result = mysqlQuery( $query, $database );
	
	while( $row_to_delete = mysql_fetch_array( $result ) )
	{
		$query_2 = "DELETE FROM ".$tables['familycompatibilities']." WHERE id=".$row_to_delete[0];
		mysqlQuery( $query_2, $database );
	}
	
	$query = "SELECT * FROM ".$tables['componentnoncompatibilities']." JOIN (".$tables['components'].", ".$tables['families'].", ".$tables['classes'].") ON (".$tables['componentnoncompatibilities'].".component_id = ".$tables['components'].".id AND ".$tables['components'].".family_id = ".$tables['families'].".id AND ".$tables['families'].".class_id = ".$tables['classes'].".id) WHERE ".$tables['classes'].".id=".$upper_class_id_in;
	$result = mysqlQuery( $query, $database );

	while( $row_to_delete = mysql_fetch_array( $result ) )
	{
		$query_2 = "DELETE FROM ".$tables['componentnoncompatibilities']." WHERE id=".$row_to_delete[0];
		mysqlQuery( $query_2, $database );
	}
}

function displayClassForm( $name_in, $required_in=null )
{
    if( !isset( $required_in ) )
    {
        $required_in = 0;
        createClass( $name_in, 0, 0 );

        $query = "SELECT id FROM class WHERE name='".$name_in."'";
        $_GET['edit_class'] = mysqlGetSingleValue( $query );
    }
	?>
		<form method="post" action='index.php'>
		<input type='hidden' name='submitted' value='1' />
	<?
		if( isset( $required_in ) )
		{
			echo "<input type='hidden' name='next_page' value='6' />";
			echo "<input type='hidden' name='id' value='".$_GET['edit_class']."' />";
		}
		
		echo "Class Name:<input type='text' name='name' value='$name_in' /><br/>";
		//echo "Required?<input type='checkbox' name='required' ".(($required_in == 1)?"checked":"")." /><br/>";
		echo "<br/><br/>";
		
		global $tables;
		$database = connectToDatabase();
		
		if( $database )
		{
			$result_array = mysql_fetch_array( mysqlQuery( "SELECT id,priority FROM ".$tables['classes']." WHERE name='".$name_in."'", $database ) );
			$priority = $result_array['priority'];
			$id = $result_array['id'];
			
			$query = "SELECT id,name FROM ".$tables['classes']." WHERE priority<'".$priority."' ORDER BY priority";
			
			$result = mysqlQuery($query, $database);
			
			if( !mysql_error() && mysql_num_rows( $result ) > 0 )
			{
				$num_rows = mysql_num_rows( $result );
				echo "<span class='dependability'>Dependabilities</span><br/>";
				
				for( $row = 0; $row < $num_rows; $row++ ) //iterates over every class except the one we're editing/creating
				{
					$result_array = mysql_fetch_array( $result );
					
					if( isset( $required_in ) )
					{
						$query_2 = "SELECT id FROM ".$tables['dependabilities']." WHERE dependent_id=".$id." AND independent_id=".$result_array['id'];
						$result_2 = mysqlQuery( $query_2, $database );
						
						if( mysql_num_rows( $result_2 ) > 0 )
						{
							echo $result_array['name']."<input type='checkbox' name='dependability[]' value='".$result_array['id']."' checked /><br/>";
						}
						else
						{
							echo $result_array['name']."<input type='checkbox' name='dependability[]' value='".$result_array['id']."' /><br/>";
						}
					}
					else
					{
						echo $result_array['name']."<input type='checkbox' name='dependability[]' value='".$result_array['id']."' /><br/>";
					}
				}
			}
		}
	?>
		<input type='submit' value='Submit' />
		</form>
	<?
}

function displayComponentHierarchy()
{
	global $tables;
	
	$database = connectToDatabase();
	
	if( !$database )
	{
		return;
	}
	
	$result_class = mysqlQuery( "SELECT id,name FROM ".$tables['classes']." ORDER BY priority", $database );
	
	if( !$result_class )
	{
		renderError( "Cannot obtain classes list!" );
		return;
	}
	
	
	
	while( $result_array_class = mysql_fetch_array( $result_class ) ) //class while loop
	{
		$class_count++;
		
		echo "<p class='class'>".$result_array_class['name']." ";
	
		if( $class_count > 1 )
		{
			echo "<span class='up'><a href='?increase_priority=".$result_array_class['id']."'>[UP]</a></span>";
		}
		
		if( $class_count < mysql_num_rows( $result_class ) )
		{
			echo "<span class='down'><a href='?decrease_priority=".$result_array_class['id']."'>[DOWN]</a></span>";
		}
	
		$result_family = mysqlQuery( "SELECT id,name FROM ".$tables['families']." WHERE class_id=".$result_array_class['id'], $database );
		
		if( !$result_family )
		{
			renderError( "Cannot obtain families list!" );
			return;
		}
			
		$first_family = true;
		
		while( $result_array_family = mysql_fetch_array( $result_family ) )
		{
			if( $first_family )
			{
				echo "<span class='edit'><a href='?edit_class=".$result_array_class['id']."'>[Edit]</a></span></p>";
			}
			$first_family = false;
			
			
			$family_count++;
				
			echo "<p class='family'>*".$result_array_family['name']." <span class='edit'><a href='?edit_family=".$result_array_family['id']."'>[Edit]</a></span>";
		
			$result_component = mysqlQuery( "SELECT id,name FROM ".$tables['components']." WHERE family_id=".$result_array_family['id'], $database );
		
			
			if( !$result_component )
			{
				renderError( "Cannot obtain components list!" );
				return;
			}
		
			$first_component = true;
		
			while( $result_array_component = mysql_fetch_array( $result_component ) )
			{
				$first_component = false;		
				
				if( $first_component )
				{
					echo "</p>";
				}
								
				echo "<p class='component'>-".$result_array_component['name']." <span class='edit'><a href='?edit_component=".$result_array_component['id']."'>[Edit]</a></span><span class='remove'><a href='?remove_component=".$result_array_component['id']."&amp;name=".$result_array_component['name']."'>[Remove]</a></span></p>";
								
			}
			
			if( $first_component )
			{
				echo	"<span class='remove'><a href='?remove_family=".$result_array_family['id']."&amp;name=".$result_array_family['name']."'>[Remove]</a></span></p>";
			}
		
			echo "
					<form class='component' method='post' action='index.php'><p>-
					<input type='hidden' name='submitted' value='1' />
					<input type='hidden' name='next_page' value='3' />
					<input type='hidden' name='family_id' value='".$result_array_family['id']."' />
					<input type='text' name='name' />
					<input type='submit' value='Submit' /></p>
					</form>
					";
		}
		
		if( $first_family )
		{
			echo "<span class='edit'><a href='?edit_class=".$result_array_class['id']."'>[Edit]</a></span>";
			echo	"<span class='remove'><a href='?remove_class=".$result_array_class['id']."&amp;name=\"".$result_array_class['name']."\"'>[Remove]</a></span></span><br/>";
		}
		//display all families in this class and all components in those families and the new component form for each family
		
		echo "
					<form class='family' method='post' action='index.php'><p>*
					<input type='hidden' name='submitted' value='1' />
					<input type='hidden' name='next_page' value='2' />
					<input type='hidden' name='class_id' value='".$result_array_class['id']."' />
					<input type='text' name='name' />
					<input type='submit' value='Submit' /></p>
					</form><br/>
					";
	}
	
	?>
		<form class='class' method="post"  action="index.php"><p>
		<input type='hidden' name='submitted' value='1' />
		<input type='hidden' name='next_page' value='1' />
		<input type='text' name='name' />
		<input type='submit' value="Submit" /></p>
		</form>
	<?
}
?>
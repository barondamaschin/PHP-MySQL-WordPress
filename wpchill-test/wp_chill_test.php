<?php

/*
  Plugin Name: Wp Chill - ian 2022 
  Plugin URI: 
  Description: Wordpress plugin develoment test
  Author: catalinbaron
  Version: 1.0
  Author URI: #
 */

/****************************************************************************
 * Code : 'Register a custom post type car'
 ****************************************************************************/

function wpchill_custom_post_type() {
    register_post_type('car',
        array(
                'labels' => array(
                'name' => 'Cars',    
                'fuel' => 'Combustibil',
                'manufacturer' => 'Producator',
                'color' => 'Culoare'
            ),
            'public'      => true,
            'menu_position' => 10,
            'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),
            'taxonomies' => array( 'car' ),
            'menu_icon' => plugins_url( 'car-icon.png', __FILE__ ),
            'has_archive' => true,
        )
    );
}
add_action('init', 'wpchill_custom_post_type');

/****************************************************************************
 * Code : 'Add a new section to the custom post type editor'
 ****************************************************************************/

// Register function to be called when admin interface is visited

function wpchill_br_admin_init() {
	add_meta_box( 'wpchill_br_car_details_meta_box', 'Car Details', 'wpchill_br_car_details_meta_box', 'car', 'normal', 'high' );
}

// Function to display meta box contents
function wpchill_br_car_details_meta_box( $car) { 
	// Retrieve current color, fuel and manufacturer based on car ID
	$car_color = esc_html(get_post_meta( $car->ID, 'color', true ) );
	$car_fuel = esc_html(get_post_meta( $car->ID, 'fuel', true ) );
    $car_manufacturer = esc_html(get_post_meta( $car->ID, 'manufacturer', true ) );

	
	$options_fuel = array('GAS','GPL','DIESEL','ELECTRIC');

	?>
	<table>
		<tr>
			<td style="width: 150px">Fuel</td>
			<td>
				<select style="width: 100px" name="car_fuel">
					<!-- Loop to generate all items in dropdown list -->
					<?php foreach($options_fuel as $key => $option ) { ?>
					<option value="<?php  if($option == $car_fuel) {continue;}else{ echo $option; }  ; ?>" ><?php echo  $option; ?></option>		
					
					<?php } ?>
					<option value="<?php echo $car_fuel; ?>" selected>
					<?php  echo $car_fuel; ?> </option>
				</select>
			</td>
			<?php //if (selected($car_fuel)) {echo selected($car_fuel);} else {echo 'Nu a returnat nimic';};?>
		</tr>
        <tr>
			<td style="width: 150px">Manufacurer</td>
			<td><input type='text' size='80' name='car_manufacturer' value='<?php echo $car_manufacturer; ?>' /></td>
		</tr>
        <tr>
			<td style="width: 150px">Color</td>
			<td><input type='text' size='80' name='car_color' value='<?php echo $car_color; ?>' /></td>
		</tr>
	</table>

<?php }

add_action( 'admin_init', 'wpchill_br_admin_init' );

// Register function to be called when posts are saved
// The function will receive 2 arguments
add_action( 'save_post', 'wpchill_br_add_car_details_fields', 10, 2 );

function wpchill_br_add_car_details_fields( $post_id = false, $post = false ) {
	// Check post type for car
	if ( 'car' == $post->post_type ) {
		// Store data in post meta table if present in post data
		if ( isset( $_POST['car_fuel'] ) ) {
			update_post_meta( $post_id, 'fuel', sanitize_text_field( $_POST['car_fuel'] ) );
		}
		
		if ( isset( $_POST['car_manufacturer'] ) )  {
			update_post_meta( $post_id, 'manufacturer', sanitize_text_field( $_POST['car_manufacturer'] ) );
		}

        if ( isset( $_POST['car_color'] ) ) {
			update_post_meta( $post_id, 'color', sanitize_text_field( $_POST['car_color'] ) );
		}

	}
}
/****************************************************************************
 * Code : 'Displaying custom post type data in shortcodes'
 ****************************************************************************/

function button_shortcode( $atts, $content = null) {

// Retrieve current color, fuel and manufacturer based on car ID
$select_color = esc_html(get_post_meta( $car->ID, 'color', true ) );
$select_fuel = esc_html(get_post_meta( $car->ID, 'fuel', true ) );
$select_manufacturer = esc_html(get_post_meta( $car->ID, 'manufacturer', true ) );	


 extract( shortcode_atts( array(
		'manufacturer' => '',
		'fuel' => '',
		'color' => '',
		'showfilters' => 0,
		 // attribute default

		), $atts, 'carlist' ) );

$loop = new WP_Query($args = array(  
	'post_type' => 'car',
	'post_status' => 'publish',
	'posts_per_page' => 10, 
	'orderby' => 'title', 
	'order' => 'ASC', 
	'meta_query' => array(
					
								array(
									'key' => 'manufacturer',
									'value' => $manufacturer ? $manufacturer : array('Honda','Dacia','Toyota', 'Hyundai'),
								),	

								array(
									'key' => 'fuel',
									'value' => $fuel ? $fuel : array('GAS','GPL','DIESEL','ELECTRIC'),
								),

								array(
									'key' => 'color',
									'value' => $color ? $color : array('Silver', 'White', 'Blue', 'Brown'),
								)
						)
					)
				); 

if ( $loop->have_posts() ) {
	$output = '<table>';

	$output .= '<th><strong>Car name</strong></th>';	

	//Show filters based on showfilters atribute values 0 and 1
	if($showfilters == 1) {

		$output .= '<th>
						<select>							
							<!-- Option element with selected attribute -->
							<option value="'.($color ? $color : 'All') .'" selected>'.($color ? $color : 'All').'</option>
						</select>
					</th>';
		$output .= '<th>
						<select>							
							<!-- Option element with selected attribute -->
							<option value="'.($fuel ? $fuel : 'All') .'" selected>'.($fuel ? $fuel : 'All').'</option>
						</select>
					</th>';

		$output .= '<th>
				<select>					
					<!-- Option element with selected attribute -->
					<option value="'.($manufacturer ? $manufacturer : 'All') .'" selected>'.($manufacturer ? $manufacturer : 'All').'</option>
				</select>
			</th></tr>';
		} else {
		$output .= '<th><strong>Color</strong></th>';
		$output .= '<th><strong>Fuel</strong></th>';
		$output .= '<th><strong>Manufacurer</strong></th></tr>';

		}


        
    while ( $loop->have_posts()  ) : $loop->the_post(); 

		$output .= '<tr><td style="white-space:nowrap">'.get_the_title().'</td>';
		$output .= '<td>'.esc_html( get_post_meta( get_the_ID(), 'color', true )).'</td>';
		$output .= '<td>'.esc_html( get_post_meta( get_the_ID(), 'fuel', true )).'</td>';
		$output .= '<td>'.esc_html( get_post_meta( get_the_ID(), 'manufacturer', true )).'</td></tr>';
	 
    endwhile;
	$output .= '</table>';
	unset($args);
    wp_reset_postdata(); 

	return $output;
  }
}


add_shortcode('carlist', 'button_shortcode'); 




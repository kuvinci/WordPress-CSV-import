<?php

namespace Talent_Age_Import\classes;

use Talent_Age_Import\App;

class Import {

	public static function init() {
		add_action('admin_menu', function(){
			add_menu_page( 'Import', 'Import', 'manage_options', 'import', [__CLASS__, 'addImportPage'], '', 4 );
		} );
		add_action('admin_init', [__CLASS__, 'handleForm']);
	}

	public static function addImportPage() {
		?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title() ?></h2>

			<form action="" method="POST">
                <label for="row_from">Row From</label>
                <input type="number" name="row_from" id="row_from">
                <label for="row_to">Row To</label>
                <input type="number" name="row_to" id="row_to">
				<input name="start_import" type="submit" value="Start Import">
			</form>
		</div>
		<?php
	}

	public static function handleForm() {
		if(!isset($_POST['start_import']) || empty($_POST['start_import'])) {
			return;
		}

        global $wpdb;
		$CSV = new CSVReader(App::$pluginPath . "6.csv");
        $from = $_POST['row_from'] ?? 0;
        $to = $_POST['row_to'] ?? 10;

        for ($i = intval($from); $i <= intval($to); $i++) {
	        $CSV->setPosition($i);
	        $row = $CSV->getData();

            if(empty($row[10])){
                continue;
            }
            
            $category = $row[5];
            $product_page_name = $row[10];

            $taxonomy = 'attachments_tax';

            $meta_arr = [
                'row_id' => $row[0],
                'equipment_type' => $row[1],
                'subcat' => $row[7],
                'sort_rank' => $row[2],
                'weight_class' => $row[3],
                'weight_sort_rank' => $row[4],
                'category_sort_rank' => $row[6],
                'sub_category_sort_rank' => $row[8],
                'product_page_ID' => $row[9],
                'sub_description' => $row[11],
                'part' => $row[12] ?: 'NoPart',
                'frame' => $row[13],
                'part_level_description' => $row[14],
                'width' => $row[15],
                'capacity' => $row[16],
                'teeth_qty' => $row[17],
                'est_weight' => $row[18],
                'cutting_edge' => $row[19],
            ];
            
            $post_arr = [
                'post_title' => $product_page_name,
                'post_type' => 'attachments', // Specify any CPT you need to import TO
                'post_status' => 'publish'
            ];

            $post_ID = wp_insert_post( $post_arr );

            // Brands that attachment can be used on
            $brands = '';
            for($k = 20; $k < 51; $k++) {

                // If no Brand specified
                if(empty($row)){
                    break;
                }

                if(!empty($row[$k])){
                    $brands .= $row[$k] . ',';
                }

            }
            $brands = substr($brands,0,-1);
            update_post_meta($post_ID, 'brands', $brands);

            // Create term/sub term and attach it to a post
            self::addTerms($post_ID, $taxonomy, $category);

            if(!is_wp_error($post_ID)){
                foreach ($meta_arr as $key => $meta ) {
                    update_post_meta( $post_ID, $key, $meta);
                }
            }

        }
	}

    public static function addTerms($post_ID, $taxonomy, $category) {

        // Main category
        if (!term_exists($category)) {
            $cat = wp_insert_term($category, $taxonomy);
            if(!is_wp_error($cat)) {
                wp_set_post_terms($post_ID, $cat['term_id'], $taxonomy, true);
            }
        } else {
            $cat = get_term_by('name', $category, $taxonomy);
            if(!is_wp_error($cat)) {
                wp_set_post_terms($post_ID, $cat->term_id, $taxonomy, true);
            }
        }

    }

}

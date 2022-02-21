<?php

namespace Sf\Popup;

class Metaboxes {

	private static $post_types;
	private static $instance;

	private static $metaboxesConfig = [
		// BOXES FOR PAGE
		'popup_post_section' => [
				'post_type' => 'sf_popup',
				'name' => 'Popup options',
				'context' => 'normal',
				'priority' => 'default',
				'fields' => [
					[
						'name' => 'popup_form_id',
						'title' => 'Hubspot form ID',
						'type'  => 'text',
						'description' => '',
                        'class' => 'wide'
					],
					[
						'name' => 'popup_active',
						'title' => 'Popup active',
						'type'  => 'boolean',
						'description' => 'Set this popup active',
//                        'save_cb'   => 'setActivePopup'  // onSave callback function
					],
					[
						'name' => 'popup_priority',
						'title' => 'Popup priority',
						'type'  => 'number',
                        'default' => 2,
						'description' => 'Set this popup priority',
					],
					[
						'name' => 'popup_start_date',
						'title' => 'Start date',
						'type'  => 'date',
						'description' => 'Set start date',
					],
                    [
                        'name' => 'popup_end_date',
                        'title' => 'End date',
                        'type'  => 'date',
                        'description' => 'Set end date',
                    ],
                    [
                        'name' => 'popup_url_regex',
                        'title' => 'URLs allowed to show the popup',
                        'type'  => 'textarea',
                        'description' => 'Supports multiple urls separated by whitespace',
                    ],
                    [
                        'name' => 'popup_url_cat_relation',
                        'title' => 'Relation between urls and categories',
                        'type'  => 'select',
                        'description' => '',
                        'options'     => ['or' => 'OR', 'and' => 'AND'],
                        'default'     => 'and',
                    ],
                    [
                        'name' => 'popup_categories',
                        'title' => 'Categories and tags',
                        'type'  => 'multiselect',
                        'description' => '',
                        'options'     => [],
                        'save_cb'     => 'saveMultiselect'
                    ],
                ],
    		],
		'popup_page_setting' => [
				'post_type' => 'page',
				'name' => 'Popup settings',
				'context' => 'side',
				'priority' => 'default',
				'fields' => [
					[
						'name' => 'disable_popup',
						'title' => 'Disable popup',
						'type'  => 'boolean',
						'description' => 'Disable popup for this page',
					],
                ],
    		],
	];
    public function __construct() {
        static::init();
    }

    public static function init(){
		add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
		add_action('save_post', [__CLASS__, 'saveMetaBox']);
        add_action( 'new_to_publish', [__CLASS__, 'saveMetaBox'] );
    }

	public static function addMetaBoxes() {
		foreach (self::$metaboxesConfig as $id => $mbox) {
			$ptype = $mbox['post_type'];
			add_meta_box( $id, $mbox['name'], [__CLASS__, 'renderMeta'], $ptype, $mbox['context'], $mbox['priority']);
		}
	}

	public static function renderMeta( $post, $metabox ) {
		$fields = self::$metaboxesConfig[$metabox['id']]['fields'];
		foreach ( $fields as $field ) {
		    if ( isset($field['show_cb']) && self::{$field['show_cb']}($post) == false ) {
		        continue;
            }
			$value = get_post_meta( $post->ID, $field['name'], true );
		    if ($value === '') {
                $value =  $field['default'] ?? $value;
		    }
			$full_name = $metabox['id'] . "[" . $field['name'] . "]";

            wp_nonce_field( basename( __FILE__ ), 'sf_popup_nonce' );
            $field_class = isset($field['class']) ? "class='{$field['class']}'" : '';
			echo "<div class='dpw-meta-row'>";
			echo "<label for='" . $full_name . "' class='dpw-meta-label'>" . $field['title'] . "</label>";
			switch ( $field['type'] ) {
                case 'text':
                case 'url':
                case 'number':
                    echo "<input type='" . $field['type'] . "' name='" . $full_name . "' value='$value' $field_class><br>";
                    break;
                case 'textarea':
                    echo "<textarea name='" . $full_name . "' " . $field_class . " cols='60' rows='6'>" . $value . "</textarea><br>";
                    break;
                case 'boolean':
                    echo "<input type='hidden' name='$full_name' value='0'>";
                    echo "<input type='checkbox' name='$full_name' value='1' " . checked($value, 1, false) . ">";
                    break;
                case 'float':
	                echo "<input type='number' name='" . $full_name . "' value='" . ($value ?: $field['default']) . "' min='0' max='1' step='0.1'><br>";
	                break;
                case 'image':
                    self::renderImageMeta($full_name, $value);
                    break;
                case 'select':
                    self::renderSelectMeta($full_name, $value, $field['options']);
                    break;
                case 'multiselect':
                    self::renderMultiselectMeta($full_name, $field['name'], $value);
                    break;
                case 'color':
                    self::renderColorMeta($full_name, $value);
                    break;
                case 'date':
                    self::renderDateMeta($full_name, $value);
                    break;
                default:
                    break;
            }
			if ( isset($field['description']) ) {
				echo "<span class='dpw-meta-field-descr'>" . $field['description'] . "</span>";
			}
			echo "</div>";
		}
	}

	public static function saveMetabox($post_id) {
        if ( !isset($_POST['sf_popup_nonce']) || !wp_verify_nonce($_POST['sf_popup_nonce'], basename(__FILE__)) ) {
            return 'nonce not verified';
        }

        if ( wp_is_post_autosave( $post_id ) ) {
            return 'autosave';
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return 'revision';
        }

        if ( 'project' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return 'cannot edit page';
        } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
            return 'cannot edit post';
        }

		foreach (array_keys(self::$metaboxesConfig) as $box_id) {
			if ( isset( $_POST[$box_id] ) && is_array($_POST[$box_id]) ) {
				self::sanitizeMetabox($box_id);
				foreach ($_POST[$box_id] as $meta_name => $meta_value) {
					update_post_meta( $post_id, $meta_name, $meta_value );

                    foreach ( self::$metaboxesConfig[$box_id]['fields'] as $field) {
                        if ( ( $field['name'] === $meta_name ) && isset($field['save_cb']) ) {
                            $save_callback_name = $field['save_cb'];
                            self::$save_callback_name($post_id, $meta_name, $meta_value);
//                            break;
                        }
                    }
				}
			}
		}
	}

	public static function sanitizeMetabox($mbox_id) {
		$meta_fields = self::$metaboxesConfig[ $mbox_id ]['fields'];
		foreach ( $meta_fields as $field ) {
			$field_name = $field['name'];
			if ( !isset($_POST[ $mbox_id ][ $field_name ]) ) {
			    if ( $field['type'] === 'boolean' ) {
				    $_POST[ $mbox_id ][ $field_name ] = 0;
                } elseif ( $field['type'] === 'image' ) {
				    $_POST[ $mbox_id ][ $field_name ] = '';
                }
				continue;
			}
			switch ($field['type']) {
				case 'text':
				case 'textarea':
					$_POST[ $mbox_id ][ $field_name ] = filter_var( $_POST[ $mbox_id ][ $field_name ], FILTER_SANITIZE_STRING );
					break;
				case 'number':
					$_POST[ $mbox_id ][ $field_name ] = intval( $_POST[ $mbox_id ][ $field_name ]);
					break;
				case 'boolean':
					$_POST[ $mbox_id ][ $field_name ] = intval( !!$_POST[ $mbox_id ][ $field_name ]);
					break;
				case 'image':
					$_POST[ $mbox_id ][ $field_name ] = filter_var( $_POST[ $mbox_id ][ $field_name ], FILTER_SANITIZE_URL );
					break;
				case 'color':
					$_POST[ $mbox_id ][ $field_name ] = self::checkColorValue( $_POST[ $mbox_id ][ $field_name ], $field['default'] );
					break;
				default:
					break;
			}
		}
	}

	public static function renderImageMeta($full_name, $value) {
		?>
                <div class="image_container">
                    <img src="<?php echo $value;?>" id="page_bg_image" alt="meta field bg image">
                </div>
				<input type="hidden" class="regular-text" name="<?php echo $full_name;?>" id="page_bg_image_url" value="<?php echo esc_attr( $value ); ?>">
				<button type="button" class="button" id="page_bg_upload_btn" data-media-uploader-target="#page_bg_image_url" data-media-img="#page_bg_image">
                    <?php _e( 'Select image', 'devpro-website' )?>
                </button>
		<?php
	}

	public static function renderSelectMeta($full_name, $value, $options) {
        echo "<select  class='options-select' name='$full_name'>";

        foreach( $options as $key => $val ) {
            echo "<option value='$key' ". selected($key, $value).">$val</option>";
        }
        echo "</select>";
    }

	public static function renderMultiselectMeta($full_name, $field_name, $value) {
        if ( $field_name == 'popup_categories') {
            self::renderTerms($full_name, $value);
        }
    }

    private static function renderTerms($full_name, $value) {
        $allowed_post_types = Options::getOption('popup_enabled_post_types');

        $taxonomies = [];

        foreach ($allowed_post_types as $post_type) {
            $taxes = get_object_taxonomies( $post_type, 'objects' );
            $taxonomies = array_merge( $taxonomies, $taxes );
        }

        $all_terms = [];
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms($taxonomy->name);
            $all_terms = array_merge($all_terms, $terms);
        }
        echo"<fieldset class='options-multiselect'>";
        foreach ($all_terms as $ind => $term) {
            echo "<div class='input-checkbox-group'>";
            $checked = in_array($term->slug, (array) $value);
            echo "<input type='hidden' value='0' name='{$full_name}[{$term->slug}]'>";
            echo "<input type='checkbox' value='1' ". checked(1, $checked, false)."  name='" .
                $full_name . "[{$term->slug}]'>";
            echo"<label>".$term->name . "</label></div>";
        }
        echo "</fieldset>";
    }

	public static function renderColorMeta($field_name, $value) {
	    echo "<input type='text' class='small-text dpw-admin-color-picker' name='$field_name' value='$value'>";
	}

	public static function renderDateMeta($field_name, $value) {
	    echo "<input type='text' class='dp-popup-datepicker' name='$field_name' value='$value'>";
        echo "<script>
        jQuery(function() {
            jQuery( '.dp-popup-datepicker' ).datepicker({
                dateFormat : 'dd-mm-yy'
            });
        });
        </script>";

	}

	public static function checkColorValue( $value, $default = '#fff' ) {

		if ( 0 === preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) {
		    $value = $default;
		}

		return $value;
	}

	public static function hasParentPost($post) {
	    return (bool) ($post->post_parent != 0);
	}

    /**
     * Sets the only active popup
     *
     * @param $post_id
     * @param $meta_name
     * @param null $meta_value
     */
	public static function setActivePopup($post_id, $meta_name, $meta_value = null) {
        if ( $meta_value ) {
            $popups = get_posts(
                [
                    'post_type' => 'sf_popup',
                    'numberposts' => -1,
                    'exclude' => [$post_id],
                    'suppress_filters' => true
                ]
            );
            foreach ($popups as $popup) {
                update_post_meta($popup->ID, $meta_name, 0);
            }
        } else {
            update_post_meta($post_id, $meta_name, 1);
        }
	}
	public static function saveMultiselect($post_id, $meta_name, $meta_value = null) {
        $meta_value = array_keys(array_filter($meta_value, function($val) { return intval($val) !== 0;}));
	    update_post_meta($post_id, $meta_name, (array) $meta_value);
	}
}

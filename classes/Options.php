<?php

namespace Sf\Popup;

class  Options {

    const PLUGIN_OPTIONS_NAME = 'sf_popup_options';
    const PLUGIN_OPTIONS_PAGE = 'sf_popup_options';

    private static $optionSections = [
        'general'    => [
            'title' => 'General options',
        ],
//        'layout' => [
//            'title' => 'Layout',
//        ],
        'hubspot_integration'    => [
            'title' => 'Hubspot integration',
        ],
        'admin' =>  [
            'title' => 'Admin options',
        ],
    ];
    /**
     * @var array
     * type : ['number', 'boolean', 'text', 'textarea', 'select', 'multiselect', 'url', 'color']
     */
    private static $optionsConfig = array(
        'debug_mode' => array(
            'title'         => 'Debug mode',
            'type'          => 'boolean',
            'section'       => 'admin',
            'default_value' => 1,
            'description' => 'If checked, cookies will be ignored',
            'filter_rule'   => FILTER_VALIDATE_BOOLEAN,
        ),
// GENERAL
        'popup_global_enable' => array(
            'title'         => 'Enable popup global feature for this site',
            'type'          => 'boolean',
            'section'       => 'general',
            'default_value' => 1,
            'filter_rule'   => FILTER_VALIDATE_BOOLEAN
        ),
        'popup_mobile_enable' => array(
            'title'         => 'Enable popup for mobile devices',
            'type'          => 'boolean',
            'section'       => 'general',
            'default_value' => 0,
            'filter_rule'   => FILTER_VALIDATE_BOOLEAN
        ),
        'popup_enabled_search_page' => array(
            'title'         => 'Show popups on search results page',
            'type'          => 'boolean',
            'section'       => 'general',
            'default_value' => 0,
            'filter_rule'   => FILTER_VALIDATE_BOOLEAN
        ),
        'popup_enabled_404_page' => array(
            'title'         => 'Show popups on 404 page',
            'type'          => 'boolean',
            'section'       => 'general',
            'default_value' => 0,
            'filter_rule'   => FILTER_VALIDATE_BOOLEAN
        ),
        'popup_enabled_post_types' => array(
            'title'         => 'Show popups on post types',
            'type'          => 'multiselect',
            'section'       => 'general',
            'default_value' => ['page'],
            'options'       => 'getPostTypes'
        ),
        'popup_countries' => array(
            'title'         => 'Show popups in countries',
            'type'          => 'multiselect',
            'section'       => 'general',
            'default_value' => [],
            'options'       => 'getCountriesList'
        ),
        'popup_continents' => array(
            'title'         => 'Show popups in continents',
            'type'          => 'multiselect',
            'section'       => 'general',
            'default_value' => [],
            'options'       => 'getContinentsList'
        ),
        'timeout_on_site_open' => array(
            'title'         => 'Delay to show popup after session start (sec)',
            'type'          => 'number',
            'section'       => 'general',
            'default_value' => 40,
            'min'           => 0,
            'max'           => 180,
            'step'          => 10,
            'filter_rule'   => FILTER_VALIDATE_INT
        ),
        'timeout_on_close_popup' => array(
            'title'         => 'Delay to show popup after its intentional close (minutes)',
            'type'          => 'number',
            'section'       => 'general',
            'default_value' => 30,
            'min'           => 5,
            'max'           => 60,
            'step'          => 1,
            'filter_rule'   => FILTER_VALIDATE_INT
        ),
// LAYOUT
//        'color_1' => array(
//            'title'         => 'Main color 1',
//            'type'          => 'color',
//            'section'       => 'layout',
//            'default_value' => '#000',
//        ),
// INTEGRATION
        // HUBSPOT INTEGRATION
        'hubspot_api_key' => array(
            'title' => 'Hubspot API key',
            'type'          => 'text',
            'section'       => 'hubspot_integration',
            'default_value' => '',
            'filter_rule'   => FILTER_DEFAULT
        ),
        'popup_hubspot_portal_id' => array(
            'title' => 'Hubspot portal ID',
            'type'          => 'text',
            'section'       => 'hubspot_integration',
            'default_value' => '',
            'class'         => 'wide',
            'filter_rule'   => FILTER_DEFAULT
        ),
    );

    private static $optionsCache = null;
    public function __construct() {
        static::init();
    }

    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'registerOptions' ) );
        add_action( 'admin_menu', array( __CLASS__,'add_plugin_options_page' ) );

        self::$optionsCache = (array) get_option(self::PLUGIN_OPTIONS_NAME);
        foreach (self::$optionsConfig as $name => $option_config) {
            if ( !isset( self::$optionsCache[$name] ) ) {
                self::$optionsCache[ $name ] = $option_config['default_value'];
            }
        }
        update_option( self::PLUGIN_OPTIONS_NAME, self::$optionsCache );
    }

    public static function add_plugin_options_page() {
        add_menu_page(
            'DP Popup options',
            'DP Popup',
            'manage_options',
            'sf_popup_options',
            [__CLASS__,'renderSettings']
        );
    }

    public static function registerOptions() {
        register_setting( self::PLUGIN_OPTIONS_PAGE, self::PLUGIN_OPTIONS_NAME, [ 'sanitize_callback' => __CLASS__ . '::validateOption', ]);
        foreach (self::$optionSections as $id => $section) {
            add_settings_section(
                $id,
                $section['title'],
                '',
                self::PLUGIN_OPTIONS_PAGE
            );
        }
        foreach (self::$optionsConfig as $option_name => $option_data ) {
            add_settings_field( $option_name, $option_data['title'], array(
                __CLASS__, 'renderOption'), self::PLUGIN_OPTIONS_PAGE, $option_data['section'] );
        }
    }

    /**
     * @param array $option
     */
    public static function setOption( $name, $value ): bool {
        $result = false;
        if ( isset(self::$optionsConfig[ $name ]) ) {
            if ( self::$optionsConfig[ $name ]['type'] !== 'multiselect' ) {
                self::$optionsCache[ $name ] = filter_var( $value, self::$optionsConfig[ $name ]['filter_rule'] );
            } else {
                self::$optionsCache[ $name ] = $value;
            }
            $result = update_option( self::PLUGIN_OPTIONS_NAME, self::$optionsCache );
        }
        return $result;
    }

    private static function updateCache() {
        static::$optionsCache = get_option(self::PLUGIN_OPTIONS_NAME);
    }

    public static function getOption($name, $default = null) {
        $value = null;
        if ( isset( self::$optionsConfig[ $name ] ) ) {
            if ( ! isset( self::$optionsCache[ $name ] ) ) {
                self::updateCache();
                self::$optionsCache[ $name ] = ($default !== null) ? $default : self::$optionsConfig[ $name ]['default_value'];
            }
            if ( self::$optionsConfig[ $name ]['type'] === 'select' ) {
                if (is_array(self::$optionsConfig[ $name ]['options'])) {
                    $value = self::$optionsConfig[ $name ]['options'][ $value ];
                }
            } elseif ( self::$optionsConfig[ $name ]['type'] === 'multiselect' ) {
                $value = (array) self::$optionsCache[ $name ];
            } elseif ( self::$optionsConfig[ $name ]['type'] === 'boolean' ) {
                $value = (bool) self::$optionsCache[ $name ];
            } else {
                $value = self::$optionsCache[ $name ];
            }
        } else {
            $value = get_option($name, $default);
        }
        return $value;
    }
    public static function getOptionsBySection($section_name) {
        $result = array();
        $filter = function ($option) use ($section_name) {
            return $option['section'] == $section_name;
        };
        if ( isset( self::$optionSections[ $section_name ] ) ) {
            $result = array_filter(self::$optionsConfig, $filter);
        }
        foreach ( array_keys($result) as $name ) {
            $result[ $name ]['value'] = self::getOption($name);
        }
        return $result;
    }

    private static function getOptionType($name) {
        $type = null;
        if ( isset( self::$optionsConfig[ $name ] ) ) {
            $type = self::$optionsConfig[ $name ][ 'type' ];
        }
        return $type;
    }

    private static function getOptionAttributes($name) {
        $atts = null;
        if ( isset( self::$optionsConfig[ $name ] ) ) {
            $atts = self::$optionsConfig[ $name ];
        }
        return $atts;
    }
    private static function getOptionAttr($option_name, $attr_name) {
        $value = null;
        $atts = self::getOptionAttributes($option_name);
        if ( in_array($attr_name, array_keys($atts)) ) {
            if ( is_string($atts[ $attr_name ]) && method_exists(__CLASS__, $atts[ $attr_name ]) ) {
                $value = self::{$atts[ $attr_name ]}();
            } else {
                $value = $atts[ $attr_name ];
            }
        }
        return $value;
    }

	public static function getPostTypes() {
		$post_types = get_post_types( [ 'publicly_queryable' => 1 ] );
		$post_types['page'] = 'page';
		unset( $post_types['attachment'] );
		foreach ($post_types as $slug => $post_type) {
            $post_type_obj = get_post_type_object( $slug );
            $post_types[$slug] = $post_type_obj->labels->singular_name;
        }

		return $post_types;
	}

	public static function getCountriesList() {
        $result = [];
        require_once __DIR__ . '/../includes/country-codes.php';
		$countries = json_decode(preg_replace('/[ \s\s+]/', '', get_countries_list()));
		foreach ($countries as $country) {
            $result[$country->Code] = $country->Name;
        }

		return $result;
	}

	public static function getContinentsList() {
        $result = [];
        require_once __DIR__ . '/../includes/country-codes.php';
		$continents = json_decode(preg_replace('/[ \s\s+]/', '', get_continents_list()));
		foreach ($continents as $countinent) {
            $result[$countinent->Code] = $countinent->Name;
        }

		return $result;
	}
//
//	public static function getTaxonomies() {
//		$taxonomies = get_taxonomies( [ 'public' => true ] );
//
//		return $taxonomies;
//	}

    public static function renderOption($name, $value) {
        $type = self::getOptionType($name);
        $renderer = 'render' . ucfirst($type) . 'Option';
        if ( method_exists(__CLASS__, $renderer) ) {
            self::$renderer($name, $value);
        }
        return null;
    }

    public static function renderSettings() {
        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>

            <form action="options.php" method="POST">
                <?php
                settings_fields( self::PLUGIN_OPTIONS_NAME );
                self::renderSections();
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function renderSections() {
        foreach ( self::$optionSections as $section_name => $section_atts) {
            $title = $section_atts['title'];
            echo "<div class='dpw-option-section' id='dpw-option-section-$section_name'>";
            echo "<span class='dpw-option-section-title'>" . $title . "</span>";
            self::renderSection( $section_name );
            echo "</div>";
        }
    }
    public static function renderSection($section_name) {
        echo "<div class='section_content'>";
        foreach ( self::$optionsConfig as $option_name => $option_atts) {
            if ( $option_atts['section'] !== $section_name ) continue;
            $value = self::getOption($option_name);
            echo "<div class='section_content-option'>";
            self::renderOption( $option_name, $value );
            echo "</div>";
        }
        echo "</div>";
    }

    private static function renderTextOption($name, $value) {
        $option_class = "class='" . self::getOptionAttr($name, 'class') . "'";
        $option_description = "<span>" . self::getOptionAttr($name, 'description') . "</span>";
        echo "<label>" . self::getOptionAttr($name, 'title') .
            "</label><input type='text' name='" . self::PLUGIN_OPTIONS_NAME . "[$name]' value='$value' $option_class>" . $option_description;
    }

    private static function renderUrlOption($name, $value) {
        echo "<label>" . self::getOptionAttr($name, 'title') .
            "</label><input type='url' name='" . self::PLUGIN_OPTIONS_NAME . "[$name]' value='" . $value . "'>";
    }

    private static function renderTextareaOption($name, $value) {
        echo "<label>" . self::getOptionAttr($name, 'title') .
            "</label><textarea name='" . self::PLUGIN_OPTIONS_NAME . "[$name]' cols='60' rows='8'
                style='resize: both;'>" . $value . "</textarea>";
    }

    private static function renderRawOption($name, $value) {
        $value = nl2br(self::getOption($name));
        echo self::getOptionAttr($name, 'title');
        echo "<div style='font-size:1.3em;'>$value</div>";
    }

    private static function renderBooleanOption($name, $value) {
        $option_description = "<span>" . self::getOptionAttr($name, 'description') . "</span>";

        echo "<label>" . self::getOptionAttr($name, 'title') . "</label>";
        echo "<input type='hidden' name='" . self::PLUGIN_OPTIONS_NAME . "[$name]' value='0'>
		     <input type='checkbox' name='" . self::PLUGIN_OPTIONS_NAME . "[$name]' value='1' " . checked(1, $value, false) . ">".$option_description;
    }
    private static function renderSelectOption($name, $value = 0) {
        $options = self::getOptionAttr($name, 'options');
        echo "<label>" . self::getOptionAttr($name, 'title') .
            "</label><select name='" . self::PLUGIN_OPTIONS_NAME . "[$name]' value='" .
            $value . "'>";
        foreach ($options as $ind => $val) {
            echo "<option value='$ind' ". selected($val, $value, false).">".$val;
        }
        echo "</select>";
    }

    public static function renderColorOption($name, $value) {
        echo "<label>" . self::getOptionAttr($name, 'title') . "</label>";
        echo "<input type='text' class='small-text dpw-admin-color-picker' name='" . self::PLUGIN_OPTIONS_NAME . "[$name]' value='$value'>";
    }

    private static function renderMultiselectOption($name, $value = []) {
        $options = self::getOptionAttr($name, 'options');
        echo "<div class='option-title-group'>";
        echo "<label>" . self::getOptionAttr($name, 'title') . "</label>";
        echo "<div class=\"toggle-options-btn\" onclick=\"this.closest('.section_content-option').querySelectorAll('fieldset input[type=checkbox]').forEach((e)=>{e.click();})\">Toggle all</div>";
        echo "</div>";
        echo"<fieldset class='options-multiselect'>";
        foreach ($options as $ind => $val) {
            echo "<div class='input-checkbox-group'>";
            $checked = in_array($ind, (array) $value);
            echo "<input type='checkbox' value='$ind' ". checked(1, $checked, false)."  name='" .
                self::PLUGIN_OPTIONS_NAME . "[$name][]' value='" .
                $val . "'><label>".$val . "</label></div>";
        }
        echo "</fieldset>";
    }
    private static function renderNumberOption($name, $value) {
        $atts = self::getOptionAttributes($name);
        echo "<label>" . $atts['title'] . "</label><input type='number' name='" . self::PLUGIN_OPTIONS_NAME .
            "[$name]' value='" . $value . "' min='" . ($atts['min']??0) . "' max='" . ($atts['max']??1) . "' step='" . ($atts['step']??1) . "'>";
    }

    public static function validateOption( $options ) {
        foreach( $options as $name => $value ){
            if ( self::getOptionAttr($name, 'type') !== 'multiselect' ) {
                $filter_rule      = self::getOptionAttr( $name, 'filter_rule' );
                if ($filter_rule) {
                    $options[ $name ] = filter_var( $value, $filter_rule );
                }
            }
        }
        return $options;
    }
}

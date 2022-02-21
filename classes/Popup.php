<?php
namespace Sf\Popup;

class Popup {

    private $popup;

    public function __construct()
    {
        $popups = $this->getPopupPosts();
        foreach ($popups as $popup) {
            $this->popup = $popup;
            if ( $this->isVisible() ) {
                break;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getPopup()
    {
        return $this->popup;
    }

    public function getPopupTitle()
    {
        return $this->popup->post_title;
    }

    public function getPopupDescription()
    {
        return $this->popup->post_content;
    }

    public function getPopupHtml()
    {
        $popup_html = '';

        if ( $this->isVisible() ) {
            $popup_id   = $this->getPopup()->ID;
            $form_id    = get_post_meta($popup_id, 'popup_form_id', true);
            $form_title = $this->getPopupTitle();
            $form_descr = $this->getPopupDescription();
            $popup_html = load_template(dirname(__FILE__) . '/../templates/popup-template.php', true, ['form_id' => $form_id, 'title' => $form_title, 'descr' => $form_descr]);
        }

        return $popup_html;
    }

    private function getPopupPosts(): array
    {
        $p_posts = get_posts([
                    'post_type' => 'sf_popup',
                    'meta_query' => [[
                        'key' => 'popup_active',
                        'value' => 1
                    ]],
                    'meta_key' => 'popup_priority',
                    'orderby'  => 'meta_value_num',
                    'order'    => 'ASC'
                ]);

        return $p_posts;
    }

    public function isVisible(): bool
    {

        return  Options::getOption('popup_global_enable') &&
                $this->isClientLocationAllowed() &&
                $this->isPostTypeAllowed() &&
                $this->isPageAllowed() &&
                $this->isDateAllowed() &&
                $this->isMobileAllowed();
    }

    private function isClientLocationAllowed(): bool
    {
        $allowed_continents = Options::getOption('popup_continents');
        $allowed_countries  = Options::getOption('popup_countries');
        $client_ip          = $_SERVER['REMOTE_ADDR'];
        $gl                 = new Geolocation($client_ip);
        if ( Options::getOption('debug_mode') && $gl->getIpAddr() === false ) {
            $gl = new Geolocation('188.163.123.81'); // For tests: country=UA, continent=EU
        }
        $is_allowed = $gl->isContinentMatch($allowed_continents);

        if ( (!$is_allowed || !count($allowed_continents)) && count($allowed_countries) ) {
            $is_allowed = $gl->isCountryMatch($allowed_countries);
        }

        return $is_allowed;
    }

    private function isCategoryAllowed(): bool
    {
        global $post;

        $cats = get_post_meta($this->popup->ID, 'popup_categories', true);

        if ( !$cats || !count($cats) ) return true;

        $taxes = get_object_taxonomies($post->post_type);
        $terms = wp_get_object_terms($post->ID, $taxes, ['fields' => 'slugs']);
        $result =  array_intersect($cats, $terms);

        return count($result);
    }

    private function isUrlAllowed(): bool
    {
        $is_allowed   = false;
        $url_meta     = get_post_meta($this->getPopup()->ID, 'popup_url_regex', true);
        $url_meta_arr = preg_split('/[,\s]+/', $url_meta);
        $url_meta_arr = array_values(array_filter($url_meta_arr, function($url) { return $url !== ''; }));
        $link         = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
            === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

        if ( count($url_meta_arr) ) {
            foreach ($url_meta_arr as $value) {
                if ( strpos($link, $value) !== false ) {
                    $is_allowed = true;
                    break;
                }
            }
        }

        return empty($url_meta) || $is_allowed;
    }

    private function isPageAllowed(): bool
    {
        global $post;
        $is_allowed = false;

        if ( is_search() && (bool) Options::getOption('popup_enabled_search_page')) {
            $is_allowed = true;
        } elseif ( is_404() && (bool) Options::getOption('popup_enabled_404_page')) {
            $is_allowed = true;
        } elseif ( is_page() || is_single() ) {
            $is_allowed = !get_post_meta($post->ID, 'disable_popup', true);
            if ( $is_allowed ) {
                $relation = get_post_meta($this->getPopup()->ID, 'popup_url_cat_relation', true) ?: 'and';

                if (strtolower($relation) === 'or') {
                    $is_allowed = $this->isUrlAllowed() || $this->isCategoryAllowed();
                } else {
                    $is_allowed = $this->isUrlAllowed() && $this->isCategoryAllowed();
                }
            }
        }

        return $is_allowed;
    }

    private function isMobileAllowed(): bool
    {
        return wp_is_mobile() ? Options::getOption('popup_mobile_enable', false) : true;
    }

    private function isPostTypeAllowed(): bool
    {
        global $post;
        $p_type = get_post_type($post->ID);

         return in_array($p_type, Options::getOption('popup_enabled_post_types', ['page']));
    }

    private function isDateAllowed(): bool
    {
        $is_allowed   = true;
        $p_id         = $this->getPopup()->ID;
        $current_date = time();
        $start_date   = get_post_meta($p_id, 'popup_start_date', true);
        $end_date     = get_post_meta($p_id, 'popup_end_date', true);

        if ( !empty($start_date) ) {
            $is_allowed = ($current_date >= strtotime($start_date));
        }

        if ( $is_allowed && !empty($end_date) ) {
            $is_allowed = ($current_date <= (strtotime($end_date) + DAY_IN_SECONDS - 1));
        }

        return $is_allowed;
    }

    public function __toString(): string
    {
        return $this->getPopupHtml();
    }
}

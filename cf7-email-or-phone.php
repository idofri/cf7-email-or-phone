<?php

/**
 * Plugin Name: CF7 - Require Email OR Phone
 * Version:     1.0.0
 * Author:      Ido Friedlander
 * Author URI:  https://github.com/idofri
 */

class Cf7EmailOrPhone
{
    private $tags = [];

    public static function instance() {
        static $instance;
        return $instance ?? ($instance = new static);
    }

    public function __construct()
    {
        // Remove default validations
        remove_filter('wpcf7_validate_tel*', 'wpcf7_text_validation_filter');
        remove_filter('wpcf7_validate_email*', 'wpcf7_text_validation_filter');

        // Apply custom validations
        add_filter('wpcf7_validate_tel*', [$this, 'skipValidation'], 10, 2);
        add_filter('wpcf7_validate_email*', [$this, 'skipValidation'], 10, 2);

        // Finalize logic
        add_filter('wpcf7_validate', [$this, 'afterValidations']);
    }

    public function afterValidations($result)
    {
        if (count($this->tags) >= 2) {
            foreach ($this->tags as $tag) {
                $result->invalidate($tag, wpcf7_get_message('invalid_required'));
            }
        }
        return $result;
    }

    public function skipValidation($result, $tag)
    {
        $name = $tag->name;
        $value = isset($_POST[$name]) ? trim(wp_unslash(strtr((string) $_POST[$name], "\n", " "))) : '';
        if (empty($value)) {
            $this->tags[] = $tag;
            return $result;
        }
        return wpcf7_text_validation_filter($result, $tag);
    }
}

add_action('wpcf7_init', function() {
    Cf7EmailOrPhone::instance();
});
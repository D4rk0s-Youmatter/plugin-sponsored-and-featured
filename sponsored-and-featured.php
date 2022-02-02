<?php
/**
 * Plugin Name: Featured Companies and Sponsored Content
 * Version: 1.0.0
 * Plugin URI: http://www.jorgepcbraz.com/
 * Description: This is a plugin to save the featured companies and sponsored content of Youmatter.
 * Author: Jorge Braz
 * Author URI: http://www.jorgepcbraz.com/
 * Licence: GPLv2 or later
 *
 * Text Domain: sponsored-and-featured
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Jorge Braz
 * @since 1.0.0
 */

if (! defined('ABSPATH')) {
    die;
}

if (!function_exists('add_action')) {
    exit;
}

class Sponsored_And_Featured
{
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'add_admin_page' ));
    }

    public function add_admin_page()
    {
        add_menu_page(
            __('Featured Companies and Sponsored Content', 'youmatter'),
            __('Companies and Sponsored', 'youmatter'),
            'list_users',
            'choose_content',
            array( $this, 'show_content' ),
            '',
            null
        );
    }

    public function enqueue()
    {
        wp_enqueue_style('sponsored_and_featured_style', plugins_url('/assets/css/mystyle.css', __FILE__));
        wp_enqueue_script('sponsored_and_featured_script', plugins_url('/assets/js/myscript.js', __FILE__));

        wp_localize_script(
            'sponsored_and_featured_script',
            'organisations',
            array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('organisations_nonce'),
            )
        );
    }

    public function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    public function register()
    {
        add_action('admin_enqueue_scripts', array( $this, 'enqueue' ));

        add_action('wp_ajax_addOrganisation', array($this, 'add_organisation'));
        add_action('wp_ajax_nopriv_addOrganisation', array($this, 'add_organisation'));
        add_action('wp_ajax_removeOrganisation', array($this, 'remove_organisation'));
        add_action('wp_ajax_nopriv_removeOrganisation', array($this, 'remove_organisation'));

        add_action('wp_ajax_addContent', array($this, 'add_content'));
        add_action('wp_ajax_nopriv_addContent', array($this, 'add_content'));
        add_action('wp_ajax_removeContent', array($this, 'remove_content'));
        add_action('wp_ajax_nopriv_removeContent', array($this, 'remove_content'));
    }

    public function show_content()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/admin.php';
    }

    public function getSelectedOrganisations()
    {
        return get_option('featured_organisations');
    }

    public function addSelectedOrganisition($id, $name)
    {
        $selectedOrganisations = $this->getSelectedOrganisations();
        $selectedOrganisations[$id] = $name;
        update_option('featured_organisations', $selectedOrganisations);
        return $selectedOrganisations;
    }

    public function removeSelectedOrganisation($id)
    {
        $selectedOrganisations = $this->getSelectedOrganisations();
        unset($selectedOrganisations[$id]);
        update_option('featured_organisations', $selectedOrganisations);
        return $selectedOrganisations;
    }

    public function add_organisation()
    {
        check_ajax_referer('organisations_nonce', 'security');
        $id = $_GET['id'];
        $name = $_GET['name'];
        $selectedOrganisations = $this->addSelectedOrganisition($id, $name);
        wp_send_json_success($selectedOrganisations);
        wp_die();
    }

    public function remove_organisation()
    {
        check_ajax_referer('organisations_nonce', 'security');
        $id = $_GET['id'];
        $selectedOrganisations = $this->removeSelectedOrganisation($id);
        wp_send_json_success($selectedOrganisations);
        wp_die();
    }

    public function getSelectedContent()
    {
        return get_option('featured_content');
    }

    public function addSelectedContent($id, $name)
    {
        $selectedContent = $this->getSelectedContent();
        $selectedContent[$id] = $name;
        update_option('featured_content', $selectedContent);
        return $selectedContent;
    }

    public function removeSelectedContent($id)
    {
        $selectedContent = $this->getSelectedContent();
        unset($selectedContent[$id]);
        update_option('featured_content', $selectedContent);
        return $selectedContent;
    }

    public function add_content()
    {
        check_ajax_referer('organisations_nonce', 'security');
        $id = $_GET['id'];
        $name = $_GET['name'];
        $selectedContent = $this->addSelectedContent($id, $name);
        wp_send_json_success($selectedContent);
        wp_die();
    }

    public function remove_content()
    {
        check_ajax_referer('organisations_nonce', 'security');
        $id = $_GET['id'];
        $selectedContent = $this->removeSelectedContent($id);
        wp_send_json_success($selectedContent);
        wp_die();
    }


    public function activate()
    {
        //echo 'the plugin was activated';
    }

    public function deactivate()
    {
        //echo 'the plugin was deactivated';
    }
}

if (class_exists('Sponsored_And_Featured')) {
    $sponsoredAndFeatured = new Sponsored_And_Featured();
    $sponsoredAndFeatured->register();
}


//activation
register_activation_hook(__FILE__, array($sponsoredAndFeatured,'activate'));
register_deactivation_hook(__FILE__, array($sponsoredAndFeatured,'deactivate'));

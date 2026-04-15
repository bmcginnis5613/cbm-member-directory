<?php
/**
 * BMD_Shortcode — registers [member_directory] shortcode.
 *
 * Usage:
 *   [member_directory]
 *   [member_directory plan="executive-network"]
 *   [member_directory plan="executive-network" columns="3" search="true"]
 *
 * Attributes:
 *   plan     (string)  Membership plan slug. Defaults to the plan saved in plugin settings.
 *   columns  (int)     Cards per row. 2, 3, or 4. Default: 3.
 *   search   (bool)    Show live-search bar. Default: true.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BMD_Shortcode {

    public static function init() {
        add_shortcode( 'member_directory', [ __CLASS__, 'render' ] );
    }

    public static function render( $atts ) {
        // Only render for logged-in users
        if ( ! is_user_logged_in() ) {
            return '<p class="bmd-login-notice">' .
                   wp_kses_post( apply_filters( 'bmd_login_notice',
                       sprintf(
                           __( 'Please <a href="%s">log in</a> to view the member directory.', 'boardroom-member-directory' ),
                           esc_url( wp_login_url( get_permalink() ) )
                       )
                   ) ) .
                   '</p>';
        }

        $options = get_option( 'bmd_settings', [] );

        // Default plans: stored as comma-separated string of IDs
        $default_plans = $options['plans'] ?? '';

        $atts = shortcode_atts( [
            'plan'    => $default_plans,  
            'columns' => $options['columns'] ?? 3,
            'search'  => 'true',
            'avatars' => 'true',
        ], $atts, 'member_directory' );

        if ( empty( $atts['plan'] ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p class="bmd-admin-notice">' .
                       esc_html__( 'Boardroom Member Directory: No membership plan(s) selected. Configure them under Settings → Member Directory.', 'boardroom-member-directory' ) .
                       '</p>';
            }
            return '';
        }

        $members = BMD_Query::get_members( $atts['plan'] );
        $columns = absint( $atts['columns'] );
        $columns = in_array( $columns, [ 2, 3, 4 ], true ) ? $columns : 3;
        $show_search = filter_var( $atts['search'], FILTER_VALIDATE_BOOLEAN );
        $show_avatars = filter_var( $atts['avatars'], FILTER_VALIDATE_BOOLEAN );

        ob_start();
        include BMD_PLUGIN_DIR . 'templates/directory.php';
        return ob_get_clean();
    }
}

<?php
/**
 * BMD_Admin — Settings page under Settings → Member Directory.
 *
 * Stores:
 *   bmd_settings['plan']     — default membership plan slug
 *   bmd_settings['columns']  — default grid columns (2 / 3 / 4)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BMD_Admin {

    public static function init() {
        add_action( 'admin_menu',  [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_init',  [ __CLASS__, 'register_settings' ] );
    }

    public static function add_menu() {
        add_options_page(
            __( 'Member Directory Settings', 'boardroom-member-directory' ),
            __( 'Member Directory', 'boardroom-member-directory' ),
            'manage_options',
            'bmd-settings',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings() {
        register_setting( 'bmd_settings_group', 'bmd_settings', [
            'sanitize_callback' => [ __CLASS__, 'sanitize' ],
        ] );

        add_settings_section(
            'bmd_main_section',
            __( 'Directory Configuration', 'boardroom-member-directory' ),
            '__return_false',
            'bmd-settings'
        );

        add_settings_field(
            'bmd_plan',
            __( 'Membership Plans', 'boardroom-member-directory' ),
            [ __CLASS__, 'render_plan_field' ],
            'bmd-settings',
            'bmd_main_section'
        );

        add_settings_field(
            'bmd_columns',
            __( 'Grid Columns', 'boardroom-member-directory' ),
            [ __CLASS__, 'render_columns_field' ],
            'bmd-settings',
            'bmd_main_section'
        );
    }

    public static function sanitize( $input ) {
        $clean = [];

        // Sanitize comma-separated plan IDs — digits and commas only
        $raw = $input['plans'] ?? '';
        $ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
        $clean['plans'] = implode( ',', $ids );

        $clean['columns'] = in_array( (int) ( $input['columns'] ?? 3 ), [ 2, 3, 4 ] )
                            ? (int) $input['columns']
                            : 3;
        return $clean;
    }

    public static function render_plan_field() {
        $options = get_option( 'bmd_settings', [] );
        $current = $options['plans'] ?? '';

        // Show available plans and their IDs as a reference table
        $plans = BMD_Query::get_all_plans();

        echo '<input
            type="text"
            name="bmd_settings[plans]"
            value="' . esc_attr( $current ) . '"
            class="regular-text"
            placeholder="e.g. 38749, 29135"
        />';
        echo '<p class="description">' . esc_html__( 'Enter one or more membership plan IDs separated by commas. Active members from all listed plans will appear in the directory.', 'boardroom-member-directory' ) . '</p>';

        // Validate entered IDs against known plans and show feedback
        if ( ! empty( $current ) && ! empty( $plans ) ) {
            $entered_ids = array_filter( array_map( 'absint', explode( ',', $current ) ) );
            $known_ids   = array_keys( $plans );
            $valid        = array_intersect( $entered_ids, $known_ids );
            $invalid      = array_diff( $entered_ids, $known_ids );

            if ( ! empty( $valid ) ) {
                $valid_names = array_map( fn( $id ) => $plans[ $id ]['name'] . ' (' . $id . ')', $valid );
                echo '<p style="color:#196f3d;margin-top:6px">✓ ' . esc_html( implode( ', ', $valid_names ) ) . '</p>';
            }
            if ( ! empty( $invalid ) ) {
                echo '<p style="color:#b32d2e;margin-top:6px">✗ ' . esc_html__( 'No plan found for ID(s): ', 'boardroom-member-directory' ) . esc_html( implode( ', ', $invalid ) ) . '</p>';
            }
        }

        if ( ! empty( $plans ) ) {
            echo '<table class="widefat striped" style="max-width:520px;margin-top:12px">';
            echo '<thead><tr><th>' . esc_html__( 'Plan Name', 'boardroom-member-directory' ) . '</th><th>' . esc_html__( 'ID', 'boardroom-member-directory' ) . '</th><th>' . esc_html__( 'Slug', 'boardroom-member-directory' ) . '</th></tr></thead>';
            echo '<tbody>';
            foreach ( $plans as $id => $data ) {
                echo '<tr>';
                echo '<td>' . esc_html( $data['name'] ) . '</td>';
                echo '<td><strong>' . esc_html( $id ) . '</strong></td>';
                echo '<td><code>' . esc_html( $data['slug'] ) . '</code></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } elseif ( ! function_exists( 'wc_memberships_get_membership_plans' ) ) {
            echo '<p class="description" style="color:#b32d2e">' . esc_html__( 'WooCommerce Memberships does not appear to be active.', 'boardroom-member-directory' ) . '</p>';
        }
    }

    public static function render_columns_field() {
        $options = get_option( 'bmd_settings', [] );
        $current = $options['columns'] ?? 3;
        foreach ( [ 2, 3, 4 ] as $n ) {
            printf(
                '<label style="margin-right:16px"><input type="radio" name="bmd_settings[columns]" value="%d" %s /> %d</label>',
                $n,
                checked( (int) $current, $n, false ),
                $n
            );
        }
        echo '<p class="description">' . esc_html__( 'Number of member cards per row. Can be overridden per-shortcode.', 'boardroom-member-directory' ) . '</p>';
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Member Directory Settings', 'boardroom-member-directory' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'bmd_settings_group' );
                do_settings_sections( 'bmd-settings' );
                submit_button();
                ?>
            </form>

            <hr/>
            <h2><?php esc_html_e( 'Usage', 'boardroom-member-directory' ); ?></h2>
            <p><?php esc_html_e( 'Embed the directory on any page or post with the following shortcode:', 'boardroom-member-directory' ); ?></p>
            <code>[member_directory]</code>
            <p><?php esc_html_e( 'Override the plan selection inline (comma-separated slugs):', 'boardroom-member-directory' ); ?></p>
            <code>[member_directory plan="plan-one,plan-two" columns="4" search="true"]</code>

            <hr/>
            <h2><?php esc_html_e( 'Member Profile Fields', 'boardroom-member-directory' ); ?></h2>
            <p>
                <?php esc_html_e( 'The following fields can be filled in per user under Users → Edit User:', 'boardroom-member-directory' ); ?>
            </p>
            <ul style="list-style:disc;padding-left:20px">
                <li><strong>bmd_title</strong> — <?php esc_html_e( 'Board Title/Role', 'boardroom-member-directory' ); ?></li>
                <li><strong>bmd_company</strong> — <?php esc_html_e( 'Board Company/Organization', 'boardroom-member-directory' ); ?></li>
                <li><strong>bmd_location</strong> — <?php esc_html_e( 'Location', 'boardroom-member-directory' ); ?></li>
                <li><strong>bmd_linkedin</strong> — <?php esc_html_e( 'LinkedIn Profile URL', 'boardroom-member-directory' ); ?></li>
            </ul>
            <p><?php esc_html_e( 'Members can also update their own profile fields from the WP front-end if you use a profile editing plugin, or directly from their WP dashboard profile page.', 'boardroom-member-directory' ); ?></p>
        </div>
        <?php
    }
}

<?php
/**
 * BMD_Fields — registers and saves custom user meta fields.
 *
 * Fields added:
 *   bmd_title       — Job Title / Role
 *   bmd_company     — Company / Organization
 *   bmd_industry    — Industry / Sector
 *   bmd_linkedin    — LinkedIn Profile URL
 *
 * These are stored as standard WordPress user meta, so they are
 * compatible with any future integration (REST API, WP-CLI, etc.).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BMD_Fields {

    public static function init() {
        // Show fields on the WP admin user-edit screen
        add_action( 'show_user_profile',     [ __CLASS__, 'render_fields' ] );
        add_action( 'edit_user_profile',     [ __CLASS__, 'render_fields' ] );

        // Save fields from the admin user-edit screen
        add_action( 'personal_options_update',  [ __CLASS__, 'save_fields' ] );
        add_action( 'edit_user_profile_update', [ __CLASS__, 'save_fields' ] );
    }

    /**
     * Returns the field definitions used across the plugin.
     */
    public static function get_field_definitions() {
        return [
            'bmd_title'    => [
                'label'       => __( 'Board Title / Role', 'boardroom-member-directory' ),
                'type'        => 'text',
                'placeholder' => 'e.g. Independent Board Director',
            ],
            'bmd_company'  => [
                'label'       => __( 'Board Company / Organization', 'boardroom-member-directory' ),
                'type'        => 'text',
                'placeholder' => 'e.g. Acme Corp',
            ],
            'bmd_industry' => [
                'label'       => __( 'Location / Sector', 'boardroom-member-directory' ),
                'type'        => 'text',
                'placeholder' => 'e.g. Financial Services',
            ],
            'bmd_linkedin' => [
                'label'       => __( 'LinkedIn Profile URL', 'boardroom-member-directory' ),
                'type'        => 'url',
                'placeholder' => 'https://linkedin.com/in/yourprofile',
            ],
        ];
    }

    /**
     * Render custom fields on the user profile / edit-user page.
     */
    public static function render_fields( $user ) {
        if ( ! current_user_can( 'edit_user', $user->ID ) ) return;
        $fields = self::get_field_definitions();
        ?>
        <h2><?php esc_html_e( 'Member Directory Profile', 'boardroom-member-directory' ); ?></h2>
        <table class="form-table" role="presentation">
        <?php foreach ( $fields as $key => $field ) :
            $value = get_user_meta( $user->ID, $key, true );
        ?>
            <tr>
                <th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
                <td>
                    <input
                        type="<?php echo esc_attr( $field['type'] ); ?>"
                        id="<?php echo esc_attr( $key ); ?>"
                        name="<?php echo esc_attr( $key ); ?>"
                        value="<?php echo esc_attr( $value ); ?>"
                        placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                        class="regular-text"
                    />
                </td>
            </tr>
        <?php endforeach; ?>
        </table>
        <?php
        wp_nonce_field( 'bmd_save_profile_fields', 'bmd_profile_nonce' );
    }

    /**
     * Save custom fields from the user profile page.
     */
    public static function save_fields( $user_id ) {
        if ( ! isset( $_POST['bmd_profile_nonce'] ) ||
             ! wp_verify_nonce( $_POST['bmd_profile_nonce'], 'bmd_save_profile_fields' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_user', $user_id ) ) return;

        foreach ( array_keys( self::get_field_definitions() ) as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = sanitize_text_field( $_POST[ $key ] );
                if ( $key === 'bmd_linkedin' ) {
                    $value = esc_url_raw( $_POST[ $key ] );
                }
                update_user_meta( $user_id, $key, $value );
            }
        }
    }
}

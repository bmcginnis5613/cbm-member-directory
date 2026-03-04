<?php
/**
 * BMD_Profile — lets logged-in members update their own directory fields
 * via the shortcode [member_profile_form].
 *
 * This is optional / additive. Admins can always edit via the WP back-end.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BMD_Profile {

    public static function init() {
        add_shortcode( 'member_profile_form', [ __CLASS__, 'render_form' ] );
        add_action( 'init', [ __CLASS__, 'handle_submission' ] );
    }

    public static function render_form( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in to edit your profile.', 'boardroom-member-directory' ) . '</p>';
        }

        $user_id = get_current_user_id();
        $fields  = BMD_Fields::get_field_definitions();

        ob_start();
        ?>
        <div class="bmd-profile-form-wrap">
            <?php if ( isset( $_GET['bmd_saved'] ) && '1' === $_GET['bmd_saved'] ) : ?>
                <div class="bmd-notice bmd-notice--success">
                    <?php esc_html_e( 'Your profile has been updated.', 'boardroom-member-directory' ); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="bmd-profile-form">
                <?php wp_nonce_field( 'bmd_update_profile', 'bmd_front_nonce' ); ?>
                <input type="hidden" name="bmd_action" value="update_profile" />

                <?php foreach ( $fields as $key => $field ) :
                    $value = get_user_meta( $user_id, $key, true );
                ?>
                    <div class="bmd-field-group">
                        <label for="<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $field['label'] ); ?>
                        </label>
                        <input
                            type="<?php echo esc_attr( $field['type'] ); ?>"
                            id="<?php echo esc_attr( $key ); ?>"
                            name="<?php echo esc_attr( $key ); ?>"
                            value="<?php echo esc_attr( $value ); ?>"
                            placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                        />
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="bmd-btn">
                    <?php esc_html_e( 'Save Profile', 'boardroom-member-directory' ); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function handle_submission() {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['bmd_action'] ) || 'update_profile' !== $_POST['bmd_action'] ) return;
        if ( ! isset( $_POST['bmd_front_nonce'] ) ||
             ! wp_verify_nonce( $_POST['bmd_front_nonce'], 'bmd_update_profile' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'boardroom-member-directory' ) );
        }

        $user_id = get_current_user_id();

        foreach ( array_keys( BMD_Fields::get_field_definitions() ) as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = ( $key === 'bmd_linkedin' )
                    ? esc_url_raw( $_POST[ $key ] )
                    : sanitize_text_field( $_POST[ $key ] );
                update_user_meta( $user_id, $key, $value );
            }
        }

        // Redirect back with success flag
        wp_safe_redirect( add_query_arg( 'bmd_saved', '1', wp_get_referer() ?: get_permalink() ) );
        exit;
    }
}

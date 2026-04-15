<?php
/**
 * Template: Directory grid
 *
 * Variables available (set in BMD_Shortcode::render):
 *   $members      — array of WP_User objects
 *   $columns      — int (2, 3, or 4)
 *   $show_search  — bool
 *   $atts         — full shortcode atts array
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="bmd-directory" data-columns="<?php echo esc_attr( $columns ); ?>">

    <?php if ( $show_search ) : ?>
    <div class="bmd-search-wrap">
        <input
            type="search"
            class="bmd-search"
            autocomplete="off"
            placeholder="<?php esc_attr_e( 'Search by name, company, or location', 'boardroom-member-directory' ); ?>"
            aria-label="<?php esc_attr_e( 'Search members', 'boardroom-member-directory' ); ?>"
        />
        <button class="bmd-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'boardroom-member-directory' ); ?>" hidden>&times;</button>
    </div>
    <?php if ( ! empty( $members ) ) : ?>
    <p class="bmd-count">
        <?php
        printf(
            esc_html( _n( '%d member', '%d members', count( $members ), 'boardroom-member-directory' ) ),
            count( $members )
        );
        ?>
    </p>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ( empty( $members ) ) : ?>
        <p class="bmd-empty"><?php esc_html_e( 'No members found.', 'boardroom-member-directory' ); ?></p>
    <?php else : ?>

    <div class="bmd-grid bmd-grid--<?php echo esc_attr( $columns ); ?>col">
        <?php foreach ( $members as $member ) :
            $title    = get_user_meta( $member->ID, 'bmd_title',    true );
            $company  = get_user_meta( $member->ID, 'bmd_company',  true );
            $location = get_user_meta( $member->ID, 'bmd_location', true );
            $linkedin = get_user_meta( $member->ID, 'bmd_linkedin', true );
            $avatar = $show_avatars ? get_avatar_url( $member->ID, [ 'size' => 160 ] ) : '';
            $first    = get_user_meta( $member->ID, 'first_name', true );
            $last     = get_user_meta( $member->ID, 'last_name',  true );
            $name     = trim( "$first $last" ) ?: $member->display_name;
            $email    = $member->user_email;

            $search_data = strtolower( implode( ' ', array_filter( [ $name, $company, $location, $title ] ) ) );
        ?>
        <div class="bmd-card" data-search="<?php echo esc_attr( $search_data ); ?>">

            <?php if ( $show_avatars ) : ?>
                <div class="bmd-card__avatar-wrap">
                    <img
                        class="bmd-card__avatar"
                        src="<?php echo esc_url( $avatar ); ?>"
                        alt="<?php echo esc_attr( $name ); ?>"
                        width="80"
                        height="80"
                        loading="lazy"
                    />
                </div>
            <?php endif; ?>

            <div class="bmd-card__body">
                <h3 class="bmd-card__name">
                    <?php if ( $linkedin ) : ?>
                        <a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $name ); ?>
                            <span class="bmd-card__linkedin-icon" aria-label="LinkedIn profile">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452H16.89v-5.569c0-1.328-.025-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a1.982 1.982 0 1 1 0-3.963 1.982 1.982 0 0 1 0 3.963zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </span>
                        </a>
                    <?php else : ?>
                        <?php echo esc_html( $name ); ?>
                    <?php endif; ?>
                </h3>

                <?php if ( $title ) : ?>
                    <p class="bmd-card__title"><?php echo esc_html( $title ); ?></p>
                <?php endif; ?>

                <?php if ( $company ) : ?>
                    <p class="bmd-card__company">
                        <span class="bmd-meta-icon" aria-label="<?php esc_attr_e( 'Company', 'boardroom-member-directory' ); ?>">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                        </span><?php echo esc_html( $company ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( $location ) : ?>
                    <p class="bmd-card__location">
                        <span class="bmd-meta-icon" aria-label="<?php esc_attr_e( 'Location', 'boardroom-member-directory' ); ?>">
                            <svg viewBox="0 0 100 100" width="16" height="16" fill="none" stroke="currentColor" stroke-width="8" aria-hidden="true">
                                <path d="M50 92C50 92 85 62.9 85 38C85 18.7 69.3 3 50 3C30.7 3 15 18.7 15 38C15 62.9 50 92 50 92Z" />
                                <circle cx="50" cy="38" r="15" />
                            </svg>
                        </span><?php echo esc_html( $location ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( $email ) : ?>
                    <p class="bmd-card__email">
                        <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                    </p>
                <?php endif; ?>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

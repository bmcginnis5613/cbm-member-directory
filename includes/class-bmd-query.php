<?php
/**
 * BMD_Query — retrieves members belonging to a specific WooCommerce Membership plan.
 *
 * Uses wc_memberships_get_membership_plan() and wc_memberships_get_user_memberships()
 * from the WooCommerce Memberships plugin to pull active members.
 *
 * Falls back gracefully if WooCommerce Memberships is not active.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BMD_Query {

    /**
     * Get active members for one or more plan slugs / IDs.
     *
     * @param  string|array $plans  Single plan slug/ID, comma-separated string, or array.
     * @param  array        $args   Optional WP_User_Query-compatible overrides.
     * @return array                Deduplicated array of WP_User objects (may be empty).
     */
    public static function get_members( $plans, $args = [] ) {
        // Require WooCommerce Memberships
        if ( ! function_exists( 'wc_memberships_get_membership_plan' ) ) {
            return [];
        }

        // Normalise input: accept string, comma-separated string, or array
        if ( ! is_array( $plans ) ) {
            $plans = array_map( 'trim', explode( ',', $plans ) );
        }
        $plans = array_filter( $plans );

        if ( empty( $plans ) ) {
            return [];
        }

        $user_ids = [];

		foreach ( $plans as $plan ) {
			$membership_plan = is_numeric( $plan )
				? wc_memberships_get_membership_plan( absint( $plan ) )
				: wc_memberships_get_membership_plan( $plan );

			if ( ! $membership_plan ) continue;

			// Use WP_Query directly — avoids the null-user/plan-filter bug
			$membership_posts = get_posts([
				'post_type'      => 'wc_user_membership',
				'post_parent'    => $membership_plan->get_id(),
				'post_status'    => [ 'wcm-active', 'wcm-complimentary' ],
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]);

			foreach ( $membership_posts as $post_id ) {
				$user_ids[] = (int) get_post_field( 'post_author', $post_id );
			}
		}

        // Deduplicate — a user could hold memberships in multiple plans
        $user_ids = array_values( array_unique( $user_ids ) );

        if ( empty( $user_ids ) ) {
            return [];
        }

        $defaults = [
            'include' => $user_ids,
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'number'  => -1,
            'fields'  => 'all',
        ];

        $query_args = wp_parse_args( $args, $defaults );
        $user_query = new WP_User_Query( $query_args );
        $results    = $user_query->get_results();

        // Exclude users whose only role is administrator.
        // Done here rather than in WP_User_Query because on multisite
        // role__not_in can behave unexpectedly.
        return array_filter( $results, function( $user ) {
            $roles = (array) $user->roles;
            return ! ( count( $roles ) === 1 && in_array( 'administrator', $roles, true ) );
        } );
    }

    /**
     * Pull all available WooCommerce Membership plans.
     * Returns an array keyed by plan post ID for use in the settings reference table.
     *
     * @return array  [ plan_id => [ 'name' => ..., 'slug' => ... ], ... ]
     */
    public static function get_all_plans() {
        if ( ! function_exists( 'wc_memberships_get_membership_plans' ) ) {
            return [];
        }

        $plans  = wc_memberships_get_membership_plans();
        $output = [];

        foreach ( $plans as $plan ) {
            $output[ $plan->get_id() ] = [
                'name' => $plan->get_name(),
                'slug' => $plan->get_slug(),
            ];
        }

        return $output;
    }
}

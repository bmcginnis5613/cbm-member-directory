/**
 * Boardroom Member Directory — Frontend JS
 *
 * Lightweight, vanilla JS. No jQuery dependency.
 * Handles live client-side search/filter.
 */
( function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        const directories = document.querySelectorAll( '.bmd-directory' );

        directories.forEach( function ( directory ) {
            const searchInput = directory.querySelector( '.bmd-search' );
            if ( ! searchInput ) return;

            const grid    = directory.querySelector( '.bmd-grid' );
            const cards   = directory.querySelectorAll( '.bmd-card' );
            const counter = directory.querySelector( '.bmd-count' );

            // Inject a "no results" row
            const noResults = document.createElement( 'p' );
            noResults.className = 'bmd-no-results';
            noResults.textContent = 'No members match your search.';
            if ( grid ) grid.after( noResults );

            searchInput.addEventListener( 'input', function () {
                const query = this.value.trim().toLowerCase();
                let visible = 0;

                cards.forEach( function ( card ) {
                    const haystack = card.dataset.search || '';
                    const match    = ! query || haystack.includes( query );

                    card.classList.toggle( 'bmd-card--hidden', ! match );
                    if ( match ) visible++;
                } );

                // Update count
                if ( counter ) {
                    if ( query ) {
                        counter.textContent = visible + ' result' + ( visible === 1 ? '' : 's' );
                    } else {
                        counter.textContent = cards.length + ' member' + ( cards.length === 1 ? '' : 's' );
                    }
                }

                // Show/hide no-results message
                noResults.classList.toggle( 'bmd-no-results--visible', visible === 0 && query !== '' );
            } );
        } );
    } );
} )();

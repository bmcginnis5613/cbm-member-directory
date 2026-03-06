( function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        const directories = document.querySelectorAll( '.bmd-directory' );

        directories.forEach( function ( directory ) {
            const searchInput = directory.querySelector( '.bmd-search' );
            if ( ! searchInput ) return;

            searchInput.value = '';

            const grid    = directory.querySelector( '.bmd-grid' );
            const cards   = directory.querySelectorAll( '.bmd-card' );
            const counter = directory.querySelector( '.bmd-count' );
            const clearBtn = directory.querySelector( '.bmd-search-clear' );

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

                // Show/hide clear button
                if ( clearBtn ) clearBtn.hidden = ! query;

                // Show/hide no-results message
                noResults.classList.toggle( 'bmd-no-results--visible', visible === 0 && query !== '' );
            } );

            if ( clearBtn ) {
                clearBtn.addEventListener( 'click', function () {
                    searchInput.value = '';
                    searchInput.dispatchEvent( new Event( 'input' ) );
                    searchInput.focus();
                } );
            }
        } );
    } );
} )();

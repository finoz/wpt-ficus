/**
 * CHILDNAME — main TypeScript
 */

import '../scss/main.scss'

document.addEventListener( 'DOMContentLoaded', () => {
    initNavigation()
} )

function initNavigation(): void {
    const header = document.querySelector( '.wp-block-template-part' ) as HTMLElement | null
    if ( ! header ) return

    const onScroll = (): void => {
        header.classList.toggle( 'is-scrolled', window.scrollY > 20 )
    }

    window.addEventListener( 'scroll', onScroll, { passive: true } )
    onScroll()
}

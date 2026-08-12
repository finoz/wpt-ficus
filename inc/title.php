<?php
/**
 * Ficus — Titolo con markup inline
 *
 * Consente la sintassi markdown nel campo titolo del post per ottenere
 * HTML formattato nel blocco core/post-title.
 *
 * Sintassi supportata:
 *   **testo**  →  <strong>testo</strong>
 *   _testo_    →  <em>testo</em>
 *
 * Il filtro agisce SOLO sull'output HTML del blocco post-title,
 * non sui contesti SEO (<title>), menu, breadcrumbs o admin.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processa la sintassi inline su un frammento HTML.
 * Applica solo all'interno dei tag — non all'attributo class o href.
 *
 * @param string $html
 * @return string
 */
function ficus_render_title_markup( string $html ): string {
	// **testo** → <strong>testo</strong>
	$html = preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $html );
	// _testo_ → <em>testo</em>
	$html = preg_replace( '/(?<!\w)_(.+?)_(?!\w)/u', '<em>$1</em>', $html );
	return $html;
}

// Blocco wp:post-title (template-driven: page-editorial, single, ecc.)
add_filter( 'render_block_core/post-title', 'ficus_render_title_markup' );

// Blocchi wp:heading nel post content (pagine con titolo inline nel content)
add_filter( 'render_block_core/heading', 'ficus_render_title_markup' );

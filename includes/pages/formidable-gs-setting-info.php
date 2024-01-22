<?php
/*
* frmdforms configuration and Intigration page
* @since 1.0
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$active_tab = isset( $_GET['tab'] ) && sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'integration';

$allowed_tags = array(
	'a'      => array(
		'href'  => array(),
		'title' => array(),
	),
	'br'     => array(),
	'em'     => array(),
	'strong' => array(),
);

?>

<div class="wrap">
	<?php

	$tabs = array(
		'integration'   => esc_html__( 'Integration', 'gsheetconnector-for-formidable-forms' ),
		'system_status' => esc_html__( 'System Status', 'gsheetconnector-for-formidable-forms' ),
	);

	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';

	foreach ( $tabs as $tab => $name ) {
		$class = ( $tab == $active_tab ) ? ' nav-tab-active' : '';
		echo '<a class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url(
			add_query_arg(
				array(
					'page' => 'formidable-form-google-sheet-config',
					'tab'  => $tab,
				)
			)
		) . '">' . wp_kses( $name, $allowed_tags ) . '</a>';
	}
	echo '</h2>';

	switch ( $active_tab ) {
		case 'integration':
			include FRMDFORM_GOOGLESHEET_VERSION_PATH . 'includes/class-formidable-forms-integration.php';
			break;
		case 'system_status':
			include FRMDFORM_GOOGLESHEET_VERSION_PATH . 'includes/pages/formidable-forms-integrate-system-info.php';
			break;
	}
	?>
</div>

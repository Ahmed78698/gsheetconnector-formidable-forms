<?php
/**
 * Formidable Forms Google sheet connector Dashboard Widget
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>
<div class="dashboard-content">
	<?php
	$gs_connector_service = new Frm_Gsc_Connector_Utility();
	$forms_list           = $gs_connector_service->get_forms_connected_to_formidable_forms();
	?>
	<div class="main-content">
		<div>
		<h3><?php echo esc_html( __( 'Formidable Forms connected with Google Sheets', 'gsheetconnector-for-formidable-forms' ) ); ?></h3>
		<ul class="contact-form-list">
			<?php
			$forms_list = FrmForm::getAll();
			if ( ! empty( $forms_list ) ) {
				$i = 1;
				foreach ( $forms_list as $form ) {
					?>
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'page'       => 'formidable',
								'frm_action' => 'edit',
								'id'         => $form->id,
							),
							admin_url( 'admin.php' )
						)
					);
					?>
								" target="_blank">
					<li style="list-style: none;"><?php echo 'Formidable Form ' . esc_html( $i ) . ': ' . esc_html( $form->name ); ?></li>
					</a>
					<?php
					++$i;
				}
			} else {
				?>
				<li><span><?php echo esc_html( wp_kses( __( 'No Formidable Forms are connected with Google Sheets.', 'gsheetconnector-for-formidable-forms' ), array() ) ); ?></span></li>
				<?php
			}
			?>
		</ul>
		</div>
	</div> <!-- main-content end -->
</div> <!-- dashboard-content end -->

<?php
/**
 * Init
 *
 * @package HAMWORKS\Outgoing_Webhooks
 */

namespace HAMWORKS\Outgoing_Webhooks;


add_action(
	'init',
	function () {
		register_post_type( 'outgoing_webhooks', [
			'label'           => 'Outgoing Webhooks',
			'rewrite'         => false,
			'query_var'       => false,
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'supports'        => [
				'title',
			],
			'capability_type' => 'page',
		] );
	}
);

add_action( 'edit_form_after_editor', function ( \WP_Post $post ) {
	if ( $post->post_type !== 'outgoing_webhooks' ) {
		return;
	}
	?>
	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th><label for="content">webhook url</label></th>
			<td><input type="url" name="content" id="content" value="<?php echo esc_url( $post->post_content ); ?>"
			           class="regular-text code" /></td>
		</tr>
		</tbody>
	</table>

	<?php
} );

function get_webhooks() {
	return get_posts(
		[
			'post_type' => 'outgoing_webhooks',
			'nopaging' => 1,
			'posts_per_page' => -1
		]
	);
}

add_action( 'save_post', function ( $post_id, \WP_Post $post ) {

	if ( $post->post_type === 'outgoing_webhooks' ) {
		return;
	}

	$container_url = esc_url( get_option( 'home' ) );
	$body          = array(
		"CONTAINER_URL" => $container_url,
	);
	$request       = array(
		'headers' => array(
			'Content-Type' => 'application/json'
		),
		'body'    => json_encode( $body ),
	);
	foreach ( get_webhooks() as $webhook ) {
		$result = wp_remote_post( $webhook->post_content, $request );
	}
}, 10, 2 );

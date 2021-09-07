<div class="wrap">
	<div class="dwspc-inner">
		<h2>
			<?php esc_html_e( 'About Plugin Collections', 'dwspc' ); ?>
		</h2>
		<p>
			<?php esc_html_e( "Plugin collections allows you to easily create saved collections of plugins to activate along with a theme. Once created, these collections can be selected from the plugin admin screen's Bulk Action menu. Once selected and applied the plugins in the selected collection will be activated along with your the collection's chosen theme and all other plugins except this one will be deactivated. Here's a quick walkthrough video...",
			                  'dwspc' ); ?>
		</p>
		<iframe width="853" height="480" src="https://www.youtube.com/embed/HkbIPCUtY0U?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
		<p>
			<?php esc_html_e( "Plugin collections can be useful if there's a need for the following:",
			                  'dwspc' ); ?>
		</p>
		<ol>
			<li>
				<?php esc_html_e( "Debugging a site where you want to deactivate some plugins, but when done easily switch back to those previously activated.", 'dwspc' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Handle support requests and have the need to be able to swap collections of plugins on a regular basis.', 'dwspc' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Other use cases not thought of.', 'dwspc' ); ?>
			</li>
		</ol>
		<h2><?php esc_html_e( 'Support', 'dwspc' ); ?></h2>
		<p>
			<?php esc_html_e( "Although I will do my best to fix bugs within the plugin's code, this is a free plugin and carries with it no promise of support, including, but not limited to responses to emails, GitHub issue requests, etc.",
			                  'dwspc' ); ?>
		</p>
		<p>
			<?php echo wp_kses_post( __( 'Pull requests are welcome here: <a href="https://github.com/damonsharp/plugin-collections">https://github.com/damonsharp/plugin-collections</a>' ) ); ?>
		</p>
	</div>
</div>

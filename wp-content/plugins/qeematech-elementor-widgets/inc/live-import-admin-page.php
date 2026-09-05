<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin screen (Tools → Import Live Content) that drives the portfolio +
 * blog batch importers (import-portfolio-endpoint.php /
 * import-blog-endpoint.php). Each importer processes one REST "page" per
 * AJAX call — this page's JS just calls the batch endpoint in a loop until
 * it reports done, rendering the running log/counters as it goes. Safe to
 * close the tab mid-run: progress is persisted server-side in wp_options,
 * so reopening this page and pressing Start again continues from the next
 * unprocessed page rather than restarting.
 */

function qeema_live_import_admin_menu() {
	add_management_page(
		'Import Live Content',
		'Import Live Content',
		'manage_options',
		'qeema-live-import',
		'qeema_live_import_render_page'
	);
}
add_action( 'admin_menu', 'qeema_live_import_admin_menu' );

function qeema_live_import_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$nonce = wp_create_nonce( 'qeema_live_import' );
	?>
	<div class="wrap">
		<h1>Import Live Content</h1>
		<p>Pulls real content from <code>qeematech.net</code> into this site. Existing content is never overwritten — each importer either fills in missing fields on existing matches or skips items already present. Safe to stop and resume at any time.</p>

		<div class="card" style="max-width:700px;padding:16px;margin-bottom:20px;">
			<h2>Portfolio</h2>
			<p id="qeema-import-portfolio-summary">Not started.</p>
			<progress id="qeema-import-portfolio-progress" value="0" max="1" style="width:100%;"></progress>
			<p>
				<button type="button" class="button button-primary" id="qeema-import-portfolio-start">Start / Resume</button>
				<button type="button" class="button" id="qeema-import-portfolio-reset">Reset progress</button>
			</p>
			<pre id="qeema-import-portfolio-log" style="max-height:200px;overflow:auto;background:#fff;border:1px solid #ccd0d4;padding:8px;"></pre>
		</div>

		<div class="card" style="max-width:700px;padding:16px;margin-bottom:20px;">
			<h2>Blog Posts</h2>
			<p id="qeema-import-blog-summary">Not started.</p>
			<progress id="qeema-import-blog-progress" value="0" max="1" style="width:100%;"></progress>
			<p>
				<button type="button" class="button button-primary" id="qeema-import-blog-start">Start / Resume</button>
				<button type="button" class="button" id="qeema-import-blog-reset">Reset progress</button>
			</p>
			<pre id="qeema-import-blog-log" style="max-height:200px;overflow:auto;background:#fff;border:1px solid #ccd0d4;padding:8px;"></pre>
		</div>

		<div class="card" style="max-width:700px;padding:16px;margin-bottom:20px;">
			<h2>Live Apps</h2>
			<p>Imports all 56 real apps from the old site's app showcase and refreshes the Live App page to display them (replacing the smaller portfolio-sourced set it used while this data was empty).</p>
			<p id="qeema-import-live_apps-summary">Not started.</p>
			<progress id="qeema-import-live_apps-progress" value="0" max="1" style="width:100%;"></progress>
			<p>
				<button type="button" class="button button-primary" id="qeema-import-live_apps-start">Start / Resume</button>
				<button type="button" class="button" id="qeema-import-live_apps-reset">Reset progress</button>
			</p>
			<pre id="qeema-import-live_apps-log" style="max-height:200px;overflow:auto;background:#fff;border:1px solid #ccd0d4;padding:8px;"></pre>
		</div>

		<div class="card" style="max-width:700px;padding:16px;margin-bottom:20px;">
			<h2>Testimonials</h2>
			<p>Reconciles against the old site's 31 real video testimonials — the 30 already here are never touched, only genuinely-missing ones are added.</p>
			<p id="qeema-import-testimonials-summary">Not started.</p>
			<progress id="qeema-import-testimonials-progress" value="0" max="1" style="width:100%;"></progress>
			<p>
				<button type="button" class="button button-primary" id="qeema-import-testimonials-start">Start / Resume</button>
				<button type="button" class="button" id="qeema-import-testimonials-reset">Reset progress</button>
			</p>
			<pre id="qeema-import-testimonials-log" style="max-height:200px;overflow:auto;background:#fff;border:1px solid #ccd0d4;padding:8px;"></pre>
		</div>

		<div class="card" style="max-width:700px;padding:16px;">
			<h2>Our Clients (logos)</h2>
			<p>Imports the 48 client logos from the old site's newer logo gallery into the (currently empty) client logos section.</p>
			<p id="qeema-import-clients-summary">Not started.</p>
			<progress id="qeema-import-clients-progress" value="0" max="1" style="width:100%;"></progress>
			<p>
				<button type="button" class="button button-primary" id="qeema-import-clients-start">Start / Resume</button>
				<button type="button" class="button" id="qeema-import-clients-reset">Reset progress</button>
			</p>
			<pre id="qeema-import-clients-log" style="max-height:200px;overflow:auto;background:#fff;border:1px solid #ccd0d4;padding:8px;"></pre>
		</div>
	</div>
	<script>
	(function () {
		var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var nonce   = <?php echo wp_json_encode( $nonce ); ?>;

		function runImporter( key, batchAction ) {
			var running = false;
			var startBtn   = document.getElementById( 'qeema-import-' + key + '-start' );
			var resetBtn   = document.getElementById( 'qeema-import-' + key + '-reset' );
			var summaryEl  = document.getElementById( 'qeema-import-' + key + '-summary' );
			var progressEl = document.getElementById( 'qeema-import-' + key + '-progress' );
			var logEl      = document.getElementById( 'qeema-import-' + key + '-log' );

			function render( data, isActiveFetch ) {
				var pct = data.total_pages ? data.current_page - 1 : 0;
				progressEl.max = data.total_pages || 1;
				progressEl.value = Math.min( pct, progressEl.max );
				var parts = [];
				if ( data.created !== undefined ) parts.push( 'created: ' + data.created );
				if ( data.gap_filled !== undefined ) parts.push( 'gap-filled: ' + data.gap_filled );
				parts.push( 'skipped: ' + data.skipped );
				parts.push( 'errors: ' + ( data.errors ? data.errors.length : 0 ) );
				parts.push( 'page ' + ( data.current_page || 1 ) + ' / ' + ( data.total_pages || '?' ) );
				var state = data.done ? 'Done. ' : ( isActiveFetch ? 'Running. ' : 'Paused. ' );
				if ( ! data.done && ! isActiveFetch && data.current_page === 1 && ! data.created && ! data.gap_filled && ! data.skipped ) {
					state = 'Not started. ';
				}
				summaryEl.textContent = state + parts.join( ' — ' );
				var lines = ( data.log || [] ).slice( -50 ).concat( ( data.errors || [] ).slice( -20 ).map( function ( e ) { return 'ERROR: ' + e; } ) );
				logEl.textContent = lines.join( '\n' );
				logEl.scrollTop = logEl.scrollHeight;
			}

			function callBatch() {
				fetch( ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'action=' + encodeURIComponent( batchAction ) + '&nonce=' + encodeURIComponent( nonce )
				} )
					.then( function ( r ) { return r.json(); } )
					.then( function ( json ) {
						if ( ! json.success ) {
							summaryEl.textContent = 'Stopped: request failed.';
							running = false;
							startBtn.disabled = false;
							return;
						}
						render( json.data, true );
						if ( json.data.done || ! running ) {
							running = false;
							startBtn.disabled = false;
							return;
						}
						callBatch();
					} )
					.catch( function () {
						summaryEl.textContent = 'Stopped: network error. Press Start to resume.';
						running = false;
						startBtn.disabled = false;
					} );
			}

			startBtn.addEventListener( 'click', function () {
				if ( running ) return;
				running = true;
				startBtn.disabled = true;
				callBatch();
			} );

			resetBtn.addEventListener( 'click', function () {
				if ( ! window.confirm( 'Reset progress counters for this importer? (Already-imported posts are not deleted.)' ) ) return;
				fetch( ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'action=' + encodeURIComponent( batchAction.replace( '_batch', '_reset' ) ) + '&nonce=' + encodeURIComponent( nonce )
				} )
					.then( function ( r ) { return r.json(); } )
					.then( function ( json ) { if ( json.success ) render( json.data ); } );
			} );

			// Load current status on page load without starting a new batch.
			fetch( ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: 'action=' + encodeURIComponent( batchAction.replace( '_batch', '_status' ) ) + '&nonce=' + encodeURIComponent( nonce )
			} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( json ) { if ( json.success ) render( json.data ); } );
		}

		runImporter( 'portfolio', 'qeema_import_portfolio_batch' );
		runImporter( 'blog', 'qeema_import_blog_batch' );
		runImporter( 'live_apps', 'qeema_import_live_apps_batch' );
		runImporter( 'testimonials', 'qeema_import_testimonials_batch' );
		runImporter( 'clients', 'qeema_import_clients_batch' );
	})();
	</script>
	<?php
}

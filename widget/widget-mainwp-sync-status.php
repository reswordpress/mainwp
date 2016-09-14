<?php

class MainWP_Sync_Status {
	public static function getClassName() {
		return __CLASS__;
	}

	public static function init() {

	}

	public static function getName() {
		return '<i class="fa fa-refresh"></i> ' . __( 'Sync status', 'mainwp' );
	}

	public static function render() {
		?>
		<div id="sync_status_list" xmlns="http://www.w3.org/1999/html"><?php MainWP_Sync_Status::renderSites(); ?></div>
		<?php
	}

	public static function renderSites() {
		$globalView = true;
		
		$current_wpid = MainWP_Utility::get_current_wpid();

		if ( $current_wpid ) {
			$sql        = MainWP_DB::Instance()->getSQLWebsiteById( $current_wpid );
			$globalView = false;
		} else {
		$sql = MainWP_DB::Instance()->getSQLWebsitesForCurrentUser();
		}

		$websites = MainWP_DB::Instance()->query( $sql );

		if ( ! $websites ) {
			return;
		}
				
		$top_row = true;
		
		?>

		<div class="clear">
			<div id="wp_syncs">
				<?php
				//Loop 3 times, first we show the conflicts, then we show the down sites, then we show the up sites
				$SYNCERRORS = 0;
				$DOWN       = 1;
				$UP         = 2;

				ob_start();
				for ( $j = 0; $j < 3; $j ++ ) {
				@MainWP_DB::data_seek( $websites, 0 );
				while ( $websites && ( $website = @MainWP_DB::fetch_object( $websites ) ) ) {
						if ( empty( $website ) )
						continue;

						$hasSyncErrors = ( $website->sync_errors != '' );
						$isDown = ( ! $hasSyncErrors && ( $website->offline_check_result == - 1 ) );
						$isUp   = ( ! $hasSyncErrors && ! $isDown );

						if ( ( $j == $SYNCERRORS ) ) {
							if ( ! $hasSyncErrors ) 						
								continue;							
					}
						if ( ( $j == $DOWN ) ) {
							if (! $isDown ) 						
						continue;
					}
						if ( ( $j == $UP ) ) {
							if ( ! $isUp )						
								continue;
						}
												
						if ( time() - $website->dtsSync < 60 * 60 * 24 ) {
							$synced24 = true;
						} else 
							$synced24 = false;
						
					$lastSyncTime = ! empty( $website->dtsSync ) ? MainWP_Utility::formatTimestamp( MainWP_Utility::getTimestamp( $website->dtsSync ) ) : '';
					?>
					<div class="<?php echo $top_row ? 'mainwp-row-top' : 'mainwp-row' ?> mainwp_wp_sync" site_id="<?php echo $website->id; ?>" site_name="<?php echo rawurlencode( $website->name ); ?>">
							<div class="mainwp-left mainwp-cols-3 mainwp-padding-top-10">
								<a href="<?php echo admin_url( 'admin.php?page=managesites&dashboard=' . $website->id ); ?>"><?php echo stripslashes( $website->name ); ?></a><input type="hidden" id="wp_sync<?php echo $website->id; ?>" />
							</div>
							<div class="mainwp-left mainwp-cols-3 mainwp-padding-top-10 wordpressInfo" id="wp_sync_<?php echo $website->id; ?>">
								<span><?php echo $lastSyncTime; ?></span>
							</div>
							<div class="mainwp-right mainwp-cols-4 mainwp-t-align-right mainwp-padding-top-5 wordpressAction">
								<?php
								if ($hasSyncErrors)
								{
									?>
									<div style="position: absolute; padding-top: 5px; padding-right: 10px; right: 50px;"><a href="#" class="mainwp_rightnow_site_reconnect" siteid="<?php echo $website->id; ?>"><?php _e('Reconnect','mainwp'); ?></a></div>
									<span class="fa-stack fa-lg" title="Disconnected">
											<i class="fa fa-circle fa-stack-2x mainwp-red"></i>
											<i class="fa fa-plug fa-stack-1x mainwp-white"></i>
						</span>
									<?php
								} else {
									if ( !$synced24 ) { ?>									
									<a href="javascript:void(0)" onClick="rightnow_wp_sync('<?php echo $website->id; ?>')"><?php _e( 'Sync Now', 'mainwp' ); ?></a>&nbsp;&nbsp;
									<?php } ?>
									<span class="fa-stack fa-lg" title="Site is Online">
										<i class="fa fa-check-circle fa-2x mainwp-green"></i>
									</span>
									<?php
								}								
								?>
								
					</div>
							<div class="mainwp-clear"></div>
						</div>
					<?php
					$top_row = false;
				}
				}
				
				$output = ob_get_clean();
					echo $output;
				?>
			</div>
		</div>
		<?php
		@MainWP_DB::free_result( $websites );
	}
}

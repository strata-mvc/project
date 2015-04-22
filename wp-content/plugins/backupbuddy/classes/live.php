//<?php
/* Class pb_backupbuddy_live
 *
 * Live backup of files to Stash servers.
 *
 * @author Dustin Bolton < http://dustinbolton.com >
 * @date Nov 26, 2012
 *
 * Usage:
 *		Generate DB dump before generating queue to easily backup db.
 *		Call generate_queue() periodicially (ie 2x daily?)
 *		Hook into media library to auto-add uploaded files into queue.
 */
class pb_backupbuddy_live {
	
	
	/* generate_queue()
	 *
	 * Determine what files have changed since this function was last run.
	 * Generate a list of said files and append to any existing queue list file.
	 * process_queue() will be scheduled to run shortly after function completes.
	 *
	 * @return		null
	 */
	public function generate_queue( $root = '', $generate_sha1 = true ) {
		
		if ( $root == '' ) {
			$root = backupbuddy_core::getLogDirectory();
		}
		
		echo 'mem:' . memory_get_usage(true) . '<br>';
		$files = (array) pb_backupbuddy::$filesystem->deepglob( $root );
		
		echo 'mem:' . memory_get_usage(true) . '<br>';
		$root_len = strlen( $root );
		$new_files = array();
		foreach( $files as $file_id => &$file ) {
			$stat = stat( $file );
			
			if ( FALSE === $stat ) {
				pb_backupbuddy::status( 'error', 'Unable to read file `' . $file . '` stat.' );
			}
			$new_file = substr( $file, $root_len );
			
			$sha1 = '';
			if ( ( true === $generate_sha1 ) && ( $stat['size'] < 1073741824 ) ) { // < 100mb
				$sha1 = sha1_file( $file );
			}
			
			$new_files[$new_file] = array(
				'scanned'	=>	time(),
				'size'		=> $stat['size'],
				'modified'	=> $stat['mtime'],
				'sha1'		=> $sha1,
				
				
				// TODO: don't render sha1 here? do it in a subsequent step(s) with cron to allow for more time? update fileoptions file every x number of tiles and a count attempts without proceeding to assume failure? max_overall attempts?
				
				
			);
			unset( $files[$file_id] ); // Better to free memory or leave out for performance?
			
		}
		unset( $files );
		echo 'mem:' . memory_get_usage(true) . '<br>';
		
		
		function pb_queuearray_size_compare($a, $b) {
			return ($a['size'] > $b['size']);
		}

		uasort( $new_files, 'pb_queuearray_size_compare' );
		
		echo '<pre>';
		print_r( $new_files );
		echo '</pre>';
		
		
		// fileoptions file live_signatures.txt
		
		//backupbuddy_core::st_stable_options( 'xxx', 'test', 5 );
		
			// get file listing of site: glob and store in an array
			// open previously generated master list (master file listing since last queue generation).
			// loop through and compare file specs to specs in master list. ( anything changed AND not yet in queue AND not maxed out send attempts ) gets added into $queue_files[];
			// add master file to end of list so it will be backed up as soon files are finished sending. to keep it up to date.
		
		
		// sort list smallest to largest
		// store in $queue_files[] in format:
		/*
			array(
				'size'		=>	434344,
				'attempts'	=>	0,
				
			);
		*/
		
		// open current queue file (if exists)
		// combine new files into queue
		// serialize $queue_files
		// base64 encode
		// write to queue file
		pb_backupbuddy::status( 'details', '12 new or modified files added into Stash queue.' );
		
		// Schedule process_queue() to run in 30 seconds from now _IF_ not already scheduled to run.
		
	} // End generate_queue().
	
	
	
	/* enqueue_file()
	 *
	 * Manually add a file into the transfer queue to be transferred soon(ish).
	 *
	 * @param	string		$file		Full path to the file to transfer.
	 * @param	boolean					True if enqueued, else false (file does not exist).
	 */
	public function enqueue_file( $file ) {
		
		if ( file_exists( $file ) ) {
			// open current queue file (if exists)
			// combine new file into queue
			// serialize
			// base64
			// write
		} else {
			return false;
		}
		
	} // End enqueue_file().
	
	
	
	/* process_queue()
	 *
	 * description
	 *
	 */
	public function process_queue() {
		
		// open queue file.
		
		$max_session_size = '50'; // Size (MB) that is the max size sum of all files sent per instance. TODO: On timeout failure detect and scale back some to help with timeouts.
		$max_session_time = '30'; // Currently only used to determine if we should auto-reduce the max session size if we are getting close to going over our time limit (help automatically avoid timeouts).
		
		$send_now_files = array(); // Files that will be queued up to be sent this PHP instance.
		$send_now_size = 0; // Running sum of the size of all files queued up to be send this PHP instance.
		$need_save = false; // Whether or not we have updated something in the queue that needs saving.
		$unsent_files = false;
		foreach( $files as &$file ) { // Loop through files in queue that need sent to Live.
			
			if ( ( $send_now_size + $file['size'] ) <= $max_session_size ) { // There is room to add this file.
				pb_backupbuddy::status( 'details', 'Added file `file.png` into queue.', 'live' );
				if ( $file['attempts'] >= 3 ) {
					// send error email notifying that its not going to make it. give suggestions. chunking?
					pb_backupbuddy::status( 'error', 'Large 94 MB file `file.png` has timed out X times and has is on hold pending user intervention.', 'live' );
				} else {
					$send_now_files .= $file;
					$file['attempts']++;
					$need_save = true;
				}
			} else { // There is not room for this file.
				if ( ( count( $send_now_files ) == 0 ) && ( $file['size'] > $max_session_size ) ) { // If no files are queued in this send now list yet then we will try to send just this one big file on its own.
					pb_backupbuddy::status( 'details', 'Large 94 MB file `file.png` exceeds max session size so it will be sent by itself to improve transfer success.', 'live' );
					$send_now_files .= $file;
					$file['attempts']++;
					$need_save = true;
					$unsent_files = true;
					break; // We have maxed out the size with a single file so no need to keep going.
				}
				$unsent_files = true;
				break; // No more room for any other files if we made it here so stop looping.
			}
			
		} // end foreach.
		if ( $need_save === true ) {
			pb_backupbuddy::status( 'details', 'Saving queue file.', 'live' );
			// Code to save the updated data structure to file.
			// After saving add this file itself to the send queue so it (the queue file) gets backed up soon?
		}
		
		// Call Stash to send these files.
		require_once( pb_backupbuddy::plugin_path() . '/destinations/bootstrap.php' );
		$send_result = pb_backupbuddy_destinations::send( $destination_settings, $send_now_files );
		
		pb_backupbuddy::status( 'message', '4 MB file `file.png` Stashed in 12 seconds.', 'live' );
		pb_backupbuddy::status( 'message', '4 MB file `file.png` did not complete after 60 seconds. Stashing it will be re-attempted in 30 seconds.', 'live' );
		
		// remove all succesful transfers from the queue file and re-save it. be quick as we may be running out of time.
		// 
		
		$this->kick_db(); // Kick the database to make sure it didn't go away, preventing options saving.
		
		if ( $unsent_files === true ) {
			// schedule next queue_process() call.
		}
		
		// make note in data structure the last time the queue was processed & status (sent X mb in Y seconds. all files succeeded[4/5 files succeded])
		
	} // End process_queue().
	
	
	
} // End class.



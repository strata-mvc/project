<?php
class backupbuddy_constants {
	
	// Cleanup
	const TIME_BEFORE_CONSIDERED_TIMEOUT = 259200; // If a backup OR remote send is has not made any progress in terms of a function finishing after X seconds (stored in updated_time in the backup fileoptions) then the backup will be considered a likely timeout.
	const MAX_SECONDS_TO_KEEP_ORPHANED_FILEOPTIONS_FILES = 2592000; // 30 days - Once this time has passed then the housekeeping cleanup function will be given the go-ahead to delete fileoptions files that have no local backup zip file that matches their serial. We keep these for a while so the Recent Backups page will keep them in its list.
	const CLEANUP_MAX_IMPORTBUDDY_AGE = 10800; // 3 hours - Max age, in seconds, importbuddy files can be there before cleaning up periodically (delay useful if just imported and testing out site).
	const CLEANUP_MAX_STATUS_LOG_AGE = 172800; // 48 hours - Max age in seconds to keep old logs before cleaning up periodically.
	const CLEANUP_MAX_AGE_TO_NOTIFY_TIMEOUT = 604800; // 7 days - Max age in seconds to send timeout emails for. Prevents very old backups being detected as timed out from sending an error notification email.
	
	// Deployment
	const DEPLOYMENT_REMOTE_API_DEFAULT_TIMEOUT = 30; // Default timeout (in seconds) for remote API calls (over HTTP) to timeout after if an overriding timeout is not passed in. Actual timeout used will be this value minus 2 seconds of wiggle room.
	
	// Backup
	const BACKUP_STATUS_BOX_LIMIT_OPTION_LINES = 100; // If limiting the status box is enabled, number of lines to limit to.
	
} // end class.
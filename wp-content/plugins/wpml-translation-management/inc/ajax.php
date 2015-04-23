<?php
function icl_get_job_original_field_content() {
	global $iclTranslationManagement;

	$error_msg = false;
	$job_id    = false;

    $nonce = filter_input( INPUT_POST, 'tm_editor_copy_nonce' );
    if ( !wp_verify_nonce( $nonce, 'icl_copy_from_original_nonce' ) ) {
        die( 'Wrong Nonce' );
    }

	$request_post_tm_editor_job_id = filter_input(INPUT_POST, 'tm_editor_job_id', FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
	$request_post_tm_editor_field_id = filter_input(INPUT_POST, 'tm_editor_job_field');
	if ( $request_post_tm_editor_job_id ) {
		$job_id = $request_post_tm_editor_job_id;
	} else {
		$error_msg = "No job id provided.";
	}

	if ( ! $error_msg && $request_post_tm_editor_job_id ) {
		$field = $request_post_tm_editor_field_id;
	} else {
		$error_msg = "No field provided.";
	}

	if ( ! $error_msg && $job_id && isset($field) ) {
		$job = $iclTranslationManagement->get_translation_job( $job_id );

		if(isset($job->elements)) {
			foreach ( $job->elements as $element ) {
				if ( sanitize_title( $element->field_type ) === $field ) {
					// if we find a field by that name we need to decode its contents according to its format
					$field_contents = TranslationManagement::decode_field_data( $element->field_data, $element->field_format );
					wp_send_json_success( $field_contents );
				}
			}
		} elseif(!$job) {
			$error_msg = __("No translation job found: it might have been just cancelled.", 'wpml-translation-management');
		} else {
			$error_msg = __("No fields found in this translation job.", 'wpml-translation-management');
		}
	}
	if ( ! $error_msg ) {
		$error_msg = __("No such field found in the job.", 'wpml-translation-management');
	}

	wp_send_json_error( $error_msg );
}

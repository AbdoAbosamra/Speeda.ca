<?php

return [
    'not_found' => 'The requested resource was not found.',
    'forbidden' => 'You do not have permission to access this resource.',
    'unauthorized' => 'You must be logged in to access this resource.',
    'csrf_token_expired' => 'Your session has expired. Please refresh the page and try again.',
    'csrf_token_expired_message' => 'Your session has expired for security reasons. Please refresh the page and try again.',
    'server_error' => 'An unexpected error occurred. Please try again later.',
    'generic_error' => 'An error occurred while processing your request. Please try again.',
    'duplicate_entry' => 'This information is already in use. Please use different details.',
    'foreign_key_constraint' => 'This record cannot be modified because it is referenced by other data.',
    'database_error' => 'A database error occurred. Please contact support if this persists.',
    'file_upload_error' => 'There was an error uploading your file. Please try again.',
    'validation_failed' => 'Please check your input and try again.',

    // Error pages (resources/views/errors)
    'page_403_title' => 'Access denied',
    'page_403_desc' => 'You do not have permission to open this page. If you think this is a mistake, sign in with the right account or contact us.',
    'page_419_title' => 'Your session expired',
    'page_419_desc' => 'For your security, the page was open too long. Go back, refresh the page, and try again.',
    'page_429_title' => 'Too many requests',
    'page_429_desc' => 'You tried this a few too many times. Please wait a moment and try again.',
    'page_429_retry' => 'You can try again in :seconds seconds.',
    'page_500_title' => 'Something went wrong',
    'page_500_desc' => 'An unexpected error happened on our side and it has been logged for our team. Please try again in a few minutes.',
    'go_back' => 'Go back',
    'try_again' => 'Try again',
    'contact_support' => 'Contact support',
];

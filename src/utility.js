const { __ } = wp.i18n;
import { dispatch } from '@wordpress/data';

export const TEXT_DOMAIN = 'koalaforms';
export const PREFIX = 'kf';
export const FORM_POST_TYPE = window.koalaformsConfig?.formPostType ?? 'koalaforms-forms';

// Function to capitalize the first letter of each word
export const capitalizeWords = (str) => {
    if (!str) return '';

    return str
      .split(' ') // Split the string into an array of words
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()) // Capitalize the first letter and make the rest lowercase
      .join(' '); // Join the words back into a single string
}

// Labels object should be extended easily with new label messages
export const LABELS = {
    // Existing labels
    nameErr: __('The name field cannot be empty. Please provide a valid name.', TEXT_DOMAIN),
    duplicateNameErr: __('Duplicate name detected! Please use a unique name.', TEXT_DOMAIN),
    nameHelp: __('The unique identifier for the element. It cannot contain spaces and must be unique in the form.', TEXT_DOMAIN),
    labelHelp: __('Display label for the control.', TEXT_DOMAIN),
    placeholderHelp: __('Display placeholder text in empty fields. Placeholder text is not applicable if masking is enabled.', TEXT_DOMAIN),
    minTextHelp: __('The minimum number of characters user must enter.', TEXT_DOMAIN),
    maxTextHelp: __('The maximum number of characters user can enter.', TEXT_DOMAIN),
    patternHelp: __('The pattern enforces data validation using standard HTML rules. Use (\\\\) to represent a single backslash (\\).', TEXT_DOMAIN),
    patternErrHelp: __('Message displayed if the input does not match the pattern.', TEXT_DOMAIN),
    reqErrHelp: __('Specifies whether the user must enter a value in order to submit the form.', TEXT_DOMAIN),
    reqErrMessageHelp: __('Message displayed if the input is not provided.', TEXT_DOMAIN),
    defaultValueHelp: __('Sets a default value for the element. If the element has been prefilled with a value, it will not be overwritten.', TEXT_DOMAIN),
    readOnlyHelp: __('If true, the user cannot modify the value in the field.', TEXT_DOMAIN),
    maskHelp: __("Defines the allowed input format: use A for letters and # for numbers.Example: US phone number mask - (###) ###-####.", TEXT_DOMAIN),
    minDateHelp: __('The earliest date the user can select from the date picker. The format must be the same as in the date FORMAT field.', TEXT_DOMAIN),
    maxDateHelp: __('The latest date user can select from the date picker. The format must be the same as in the date FORMAT field.', TEXT_DOMAIN),
    dateFormatHelp: __('Specifies the format to display the date. For example, if the format is MM-DD-YYYY, and the user selects January 3, 2020 from the date picker, the date displays as 01-03-2020.', TEXT_DOMAIN),
    cbDefaultHelp: __('Select to enable the checkbox by default.', TEXT_DOMAIN),
    radioHDHelp: __('Display options next to each other.', TEXT_DOMAIN),
    radioHVHelp: __('Display options stacked.', TEXT_DOMAIN),
    logtextRowHelp: __('Specifies the number of visible text lines.', TEXT_DOMAIN),
    checkLabelHelp: __('The text that appears next to the checkbox.', TEXT_DOMAIN),
    disclosureMessageHelp: __('The disclosure statement text.', TEXT_DOMAIN),
    descHelp: __('Description for internal reference.', TEXT_DOMAIN),
  
    // New labels for form settings
    descriptionHelp: __('Provide a brief description of the form', TEXT_DOMAIN),
    isActiveHelp: __('Toggle to mark the form as active or inactive', TEXT_DOMAIN),
    inactiveMessageHelp: __('Enter a message to display when the form is inactive (max 500 characters)', TEXT_DOMAIN),
    activeDateHelp: __('Set a date when the form will become active', TEXT_DOMAIN),
    inactiveDateHelp: __('Set a date when the form will be deactivated', TEXT_DOMAIN),
    submissionHandlingHelp: __('Choose how form submissions are handled: Save to Database or Webhook Integration', TEXT_DOMAIN),
    submissionLimitPerUserHelp: __('Set the maximum number of submissions a user can make', TEXT_DOMAIN),
    totalSubmissionLimitHelp: __('Set the total number of submissions allowed for the form', TEXT_DOMAIN),
    loggedInUserRestrictionHelp: __('Allow only logged-in users to submit the form', TEXT_DOMAIN),
    emailNotificationsHelp: __('Enter email addresses separated by commas to receive notifications when form is submitted.', TEXT_DOMAIN),
    captchaHelp: __('Choose for spam protection', TEXT_DOMAIN),
    redirectionHelp: __('Enter URL to redirect users after successful form submission. Leave empty to stay on same page.', TEXT_DOMAIN),
    autoResponderHelp: __('Turns on auto responder email for the form. After successful submission a customizable email is sent to the user.',TEXT_DOMAIN ),
    arSubjectHelp: __('Subject of the mail sent to the user.', TEXT_DOMAIN),
    arBodyHelp: __('Content of the email to be sent to the user.', TEXT_DOMAIN),
    primaryFieldHelp: __('Selected Email field value will be used as primary email for notifications.', TEXT_DOMAIN),
    ageHelp: __('When enabled, this date will be used to calculate the user\'s age.', TEXT_DOMAIN),
    maxNumberHelp:__('Maximum accepted value for the field.',TEXT_DOMAIN),
    minNumberHelp: __('Minimum accepted value for the field.',TEXT_DOMAIN),
    hiddenHelp: __('Field won\'t be visible for edit. Stores data to send with the submission for backend use.', TEXT_DOMAIN),
    uniqueHelp: __('No two submissions can have same value for this field', TEXT_DOMAIN),
    uniqueErrHelp: __('Error message to be shown if this field has same value for other submissions.', TEXT_DOMAIN),
    formTypeHelp: __('Registration form registers a user in the system.', TEXT_DOMAIN),
    usernameFieldHelp: __('Selected field will be treated as Username for the user registration.',TEXT_DOMAIN),
    usermetaHelp: __('Meta key to map with User account in Wordpress.', TEXT_DOMAIN),
    uniqueIdHelp: __('Generates Unique token for each submission.',TEXT_DOMAIN),
    adminEmailBodyHelp: __('Email content to be sent to the user. Create personalized message using Fields dropwdown to place submission values. Use {{REGISTRATION_DATA}} to dynamically place values of all the submission fields.', TEXT_DOMAIN)
  };

// Show a customizable error toast
export const showErrorToast = (message, noticeType = 'info') => {
    dispatch('core/notices').createNotice(
        noticeType, // Type can be passed in for flexibility (e.g., 'error', 'warning', 'info')
        __(message, PREFIX),
        {
            id: 'noticeId',
            type: 'snackbar',
            isDismissible: false,
            contentClassName: 'custom-error-toast',
        }
    );
};

export const generateUniqueHash = (clientId) => {
    // Ensure the clientId is a string
    const input = String(clientId);

    // Generate a hash using the clientId
    const hash = crypto.subtle.digest('SHA-256', new TextEncoder().encode(input))
        .then(buffer => {
            // Convert ArrayBuffer to hex string
            const hexArray = Array.from(new Uint8Array(buffer));
            const hexString = hexArray.map(byte => byte.toString(16).padStart(2, '0')).join('');

            // Return the first 10 characters of the hash
            return hexString.slice(0, 10);
        });

    return hash;
}


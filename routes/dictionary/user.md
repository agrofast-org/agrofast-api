# User Controller Response Dictionary

## General Responses

- `user_not_found`: The requested user was not found.
- `user_not_authenticated`: The user is not authenticated.
- `not_implemented`: The requested functionality is not implemented.
- `failed_to_upload_image`: Failed to upload the image.
- `failed_to_save_image_record`: Failed to save the image record.
- `no_images_found`: No images found for the user.
- `error_creating_user`: There was an error creating the user.
- `user_already_exists`: The user already exists.
- `invalid_authentication_code`: The provided authentication code is invalid.
- `failed_to_resend_code`: Failed to resend the authentication code.
- `invalid_login_credentials`: The provided login credentials are invalid.
- `wrong_password`: The provided password is incorrect.
- `failed_to_send_authentication_code`: Failed to send the authentication code.

## Success Responses

- `user_created_successfully`: The user was created successfully.
- `image_uploaded_successfully`: The image was uploaded successfully.
- `user_updated_successfully`: The user was updated successfully.
- `user_authenticated_successfully`: The user was authenticated successfully.
- `authentication_code_resent`: The authentication code was resent successfully.
- `login_successful_authentication_code_sent`: Login was successful and the authentication code was sent.

## Validation Responses

- `user_name_required_message`: The user's name is required.
- `user_surname_required_message`: The user's surname is required.
- `user_number_required_message`: The user's number is required.
- `user_invalid_number_message`: The provided number is invalid.
- `user_email_required_message`: The user's email is required.
- `user_invalid_email_message`: The provided email is invalid.
- `user_password_required_message`: The user's password is required.
- `user_password_length_message`: The password must be at least 8 characters long.
- `user_password_character_message`: The password must contain both letters and numbers.
- `password_not_coincide_message`: The password and password confirmation do not match.

## Data Responses

- `user_found`: The user was found.
- `data`: Contains the user data.
- `file`: Contains the file data.
- `token`: Contains the JWT token.
- `errors`: Contains the validation error errors.
- `message`: Contains the response message.
- `error`: Contains the error message.
- `user`: Contains the user information.
- `id`: The user's ID.
- `name`: The user's name.
- `surname`: The user's surname.
- `number`: The user's number.
- `email`: The user's email.
- `profile_picture`: The user's profile picture.

=== GSheetConnector for Formidable Forms ===

Contributors: westerndeal  
Author URI: [https://www.gsheetconnector.com/](https://www.gsheetconnector.com/)  
Tags: Formidable Forms, Google Sheet Integration, Formidable Forms Addon, Google Sheets API, WordPress Plugin  
Requires at least: 5.6  
Tested up to: 6.4.2  
Requires PHP: 7.4  
Stable tag: 1.0.0  
License: GPLv3
License URI: [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)

## Description

The GSheetConnector for Formidable Forms is a robust WordPress plugin designed to seamlessly integrate Formidable Forms with Google Sheets. Elevate your data management and workflow automation by effortlessly transmitting Formidable Forms data directly to your designated Google Sheet.

## Features

- **Effortless Integration:** Seamlessly connect Formidable Forms with Google Sheets.
- **Workflow Optimization:** Enhance your workflow by automating the transfer of form data to Google Sheets.
- **User-Friendly Interface:** Streamlined setup and configuration for a hassle-free experience.

## Installation

### Option 1: Install from WordPress Plugin Repository (Recommended)

1. Go to the Plugins -> 'Add New' page in your WP admin area.
2. Search for 'GSheetConnector for Formidable Forms.'
3. Click the 'Install Now' button, then 'Activate.'
4. Go to the newly added 'Formidable' menu and locate 'Google Sheet.'

### Option 2: Install from your Website or via Upload

1. **Download:**
   - Acquire the plugin ZIP file from [WordPress.org](https://wordpress.org/plugins/gsheetconnector-for-formidable-forms/).
   
2. **Upload and Activate:**
   - Install the plugin by uploading the ZIP file via the WordPress admin or by extracting it into the plugin's directory.

3. **Configuration:**
   - Activate the plugin through the 'Plugins' menu in WordPress.
   - Configure the settings in the Formidable Forms integration section.

**Note:** For a streamlined experience, we recommend using Option 1 by installing directly from the WordPress Plugin Repository. However, if you prefer to download the plugin manually, you can choose Option 2 and follow the steps above.

## Getting Started

1. **Integration :**
   - In the Formidable Forms menu, locate and click on "Google Sheets."
   - Click the "Sign In with Google" button below.
   - Select your Google account and grant the necessary permissions.
   - The plugin will automatically retrieve and paste the authentication code to the input box.
   - Click on the "Save and Authenticate" button, which will finally authenticate you and display the "Currently Active" text in the input box. 
   - You will also see the authenticated account Email ID below the input box. ( Refer to screenshots )
  
2. **Connect Form with Google Sheet:**
   - Inside the Formidable Form settings:
     - Navigate to the `Settings` -> `Actions & Notifications` tab.
     - Find the `GSheetConnector` menu.
     - Configure sheet details by adding the Sheet Name,  Sheet ID, Tab Name and Tab ID
     - Save the settings to connect with the Google Sheet.

3. **Add connected Google Sheet Headers:**
    - As per the above-configured sheet details, open the Google Sheet and manually add the Form field label names as header names to the Google Sheet. ( Refer Screenshots )

Hurray!! You're ready to seamlessly transfer form-submitted data to the Google Sheet.

## Database Storage

GSheetConnector stores Google Sheets details in the WordPress database itself. These include the authentication details that is the auth code and the token details like access token, scopes, authentication created, and expiration timestamp.

## Third-Party Service Usage

GSheetConnector relies on the following third-party services for specific functionalities. Please be aware of the following external services being utilized:

### Google APIs

GSheetConnector utilizes Google APIs for integrating with Google Sheets. This involves communication with the following domains:

- **Base URL for Google APIs:**
  - [https://www.googleapis.com](https://www.googleapis.com)

Please review the terms of use and privacy policy for Google APIs:

- [Google APIs Terms of Service](https://developers.google.com/terms)
- [Google Privacy & Terms](https://policies.google.com/privacy)

### OAuth 2.0 Authorization Server

To handle authentication, GSheetConnector interacts with the OAuth 2.0 Authorization Server provided by Google. Relevant URLs include:

- **Token URL:**
  - [https://oauth2.googleapis.com/token](https://oauth2.googleapis.com/token)
- **Revoke URL:**
  - [https://oauth2.googleapis.com/revoke](https://oauth2.googleapis.com/revoke)

Please review the terms of use and privacy policy for Google OAuth 2.0:

- [Google API Services User Data Policy](https://developers.google.com/terms/api-services-user-data-policy)
- [Google Privacy & Terms](https://policies.google.com/privacy)

## Library Used

- **Google API Client Library:**
  - [GitHub Repository](https://github.com/google/google-api-php-client)

The Google API Client Library is a set of client libraries that simplifies accessing Google services. Here we are using Google Drive using PHP programming language. These libraries provide a convenient way to interact with Google APIs and handle authentication, request formatting, and other common tasks.

## Scopes Used

- **https://www.googleapis.com/auth/drive.metadata.readonly**
The scope is related to the Google Drive API and is used to request permission to access read-only metadata about a user's Google Drive files. Specifically, this scope grants your application the ability to view metadata such as file and folder names, IDs, modification dates, and other information without the ability to modify or delete the actual content of the files.

- **https://www.googleapis.com/auth/spreadsheets**
The scope is associated with the Google Sheets API and is used to request permission to manage Google Sheets on behalf of the user. This scope grants your application the ability to read, write, and manage user's Google Sheets.

## Services and URLs Used

- **https://www.googleapis.com/auth/plus.login**
It is used to request access to a user's Google+ profile information. This scope allowed applications to authenticate users with their Google+ accounts and retrieve basic profile information. We here get the Email ID to let users know with which account they are connected.

- **https://oauth2.googleapis.com/token**
 It is used in the OAuth 2.0 authorization flow to obtain access tokens from Google's authorization server. This endpoint is part of the OAuth 2.0 protocol, which is commonly used for secure and authorized access to protected resources, such as user data on Google APIs.

- **https://www.googleapis.com/auth/moderator**
It allows the application to request access to the user's Google Moderator data with the specified permissions.

- **https://www.googleapis.com/oauth2/v3/certs**
 It is used to retrieve the JSON Web Key (JWK) set associated with Google's OAuth 2.0 authentication service. This JWK set contains the public keys that can be used to verify the signature of JSON Web Tokens (JWTs) issued by Google during the authentication process.

Please review the terms of use and privacy policy for each service.

It is essential to understand and agree to the terms of service and privacy policies of these third-party services before using GSheetConnector. This documentation is provided for transparency and legal compliance.


== Screenshots ==

1. Google Sheet Integration without authentication  
2. Permission page if user is already logged-in to there account. 
3. Permission popup-1 after logged-in to your account.
4. Permission popup-2 after logged-in to your account.
5. After successful integration - Displays "Currently Active".
6. Google Sheet settings page with input box Sheet Name, Sheet Id, Tab Name, Tab Id.
7. Get Sheet and Tab Id from the URL.

## Frequently Asked Questions

### Where can I get support for this plugin?

For dedicated support and assistance, visit our official support forum on the [GSheetConnector](https://www.gsheetconnector.com/) website.

## Changelog

### 1.0.0

- Initial public release
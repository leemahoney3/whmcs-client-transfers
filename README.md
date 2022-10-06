# WHMCS Client Transfers

Allow clients to transfer services and domains internally, a badly needed feature for WHMCS.

### !! Warning: This is a work in progress, please do not download yet for production. !!

----

This module will likely take some time to finish, your more than welcome to contribute and I've created a list of what's been done, and what needs to be done below.

### Completed:

- Clientarea dashboard
- Pending and previous transfers
- Incoming and past requests
- Start a transfer
- Custom pagination
- Error handling in the client area
- Email sending for transfers
- Ability to accept or deny transfers, ability to cancel transfers
- Full automation of the transfer process including service switching, invoice handling and welcome email sending
- Logging
- Admin area dashboard showing an overview
- Admin area filtering of transfers based on status, type, losing client and gaining client
- Custom pagination in the admin area
- Custom routing system in both the client area and admin area
- Transition from Capsule queries to Eloquent models
- Switch to bootstrap to tie in with WHMCS styling and make all areas of the module responsive


### Still To Do:

- Create a workaround for generating invoices on new client account (see note in app/Handlers/Invoice.php)
- Better routing in the client area, perhaps session based flash messages to help with this
- Finish configuration options (only some are currently working, the rest are not yet implemented within the module)
- Deny users on a clients account access to the module (only the account holder should be able to interact with this module. Perhaps a config option might be suitable)
- Move settings to the database and make accessible through a settings page on the module rather than in the addon module interface in WHMCS
- Implement the ability to change service passwords as part of the transfer process (if possible)
- Implement the logs page in the admin dashboard
- Implement the ability to start a transfer from the admin area dashboard
- Perhaps implement some sort of admin permissions checks for certain functions in the dashboard (in the admin area)
- Implement a way to update the module in future (e.g. database tables) without disruption to past data
- Implement the ability to purge data (if the config option is enabled)
- Implement a block list for clients to prevent persistent spamming of transfer requests from another client

Theres probably a lot more features that can be added to this. I'll update the list above as I implement them.

If you have any ideas, feel free to pass them on at lee@leemahoney.dev and I'll add them to the list.
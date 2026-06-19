<?php

/* HTTP RESPONSE CODE*/
defined('HTTP_PROCESSING')                  || define('HTTP_PROCESSING', 102);
defined('HTTP_OK')                          || define('HTTP_OK', 200);
defined('HTTP_SUCCESS')                     || define('HTTP_SUCCESS', 201);
defined('HTTP_NOT_COMPLETED')               || define('HTTP_NOT_COMPLETED', 202);
defined('HTTP_NONE_AUTHORITATIVE')          || define('HTTP_NONE_AUTHORITATIVE', 203);
defined('HTTP_SUCCESS_NO_CONTENT')          || define('HTTP_SUCCESS_NO_CONTENT', 204);
defined('HTTP_CONFLICT')                    || define('HTTP_CONFLICT', 409);
defined('HTTP_UNAUTHORIZED')                || define('HTTP_UNAUTHORIZED', 401);
defined('HTTP_UNPROCESSABLE')               || define('HTTP_UNPROCESSABLE', 422);
defined('HTTP_LOCKED')                      || define('HTTP_LOCKED', 423);
defined('HTTP_FAILED')                      || define('HTTP_FAILED', 424);
defined('HTTP_FORBIDDEN')                   || define('HTTP_FORBIDDEN', 403);
defined('HTTP_NOT_FOUND')                   || define('HTTP_NOT_FOUND', 404);
defined('HTTP_UNKNOWN')                     || define('HTTP_UNKNOWN', 419);
defined('HTTP_INTERNAL_SERVER_ERROR')       || define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_BAD_REQUEST', 400);

/* SESSION TIMEOUT CONSTANTS */
defined('WEB_SESSION_TIMEOUT')              || define('WEB_SESSION_TIMEOUT', 600);
defined('API_SESSION_TIMEOUT')              || define('API_SESSION_TIMEOUT', 600);

/* GENERAL STATUSES */
defined('PENDING')      || define('PENDING', 'pending');
defined('ACTIVE')       || define('ACTIVE', 'active');
defined('INACTIVE')     || define('INACTIVE', 'inactive');
defined('REDEEMED')     || define('REDEEMED', 'redeemed');
defined('FAILED')       || define('FAILED', 'failed');
defined('SUCCESS')      || define('SUCCESS', 'success');
defined('OFFLINE')      || define('OFFLINE', 'offline');
defined('ONLINE')       || define('ONLINE', 'online');
defined('ENABLED')      || define('ENABLED', 'enabled');
defined('DISABLED')     || define('DISABLED', 'disabled');
defined('YES')          || define('YES', 'yes');
defined('NO')           || define('NO', 'no');
defined('EXPIRED')      || define('EXPIRED', 'expired');
defined('VERIFIED')     || define('VERIFIED', 'verified');
defined('CANCELED')     || define('CANCELED', 'canceled');
defined('SENT')         || define('SENT', 'sent');
defined('NOT_SENT')         || define('NOT_SENT', 'not sent');
defined('DELIVERED')         || define('DELIVERED', 'delivered');
defined('NOT_DELIVERED')         || define('NOT_DELIVERED', 'not delivered');

/* CUSTOM */
defined('PAYMENT_API_BASE_URL')             || define('PAYMENT_API_BASE_URL', 'https://imosys.mw');
defined('PAYMENT_API_BASE_PATH')            || define('PAYMENT_API_BASE_PATH', '/thirdparty/api/');
defined('SMS_SERVER_KEY')                   || define('SMS_SERVER_KEY', 'jZ8DGd64');
defined('FIREBASE_SERVER_KEY')              || define('FIREBASE_SERVER_KEY', 'AAAAicNW570:APA91bEbN62uwiYXosDgz-_z0Sa18kVJ3JBX1a0lg7l-I4ISA3uyJvapa3WhgvnLxgMhXPLrJ0IMjLPelQUeSaphl97VAosaoIRFeU7qq7XxvruXST-LWUZgp5KY1WvN0ug6ZK5faEB6');


defined('TENANT')                   || define('TENANT', 'tenant');
defined('OWNER')                    || define('OWNER', 'owner');

/* user roles*/
defined('ADMIN')                   || define('ADMIN', 'Super Admin');
defined('SP_ADMIN')                || define('SP_ADMIN', 'Service Provider Admin');
defined('HOUSEHOLD_ADMIN')         || define('HOUSEHOLD_ADMIN', 'Household Admin');
defined('HOUSEHOLD_MEMBER')        || define('HOUSEHOLD_MEMBER', 'Household Member');
defined('ALERT_RECIPIENT')         || define('ALERT_RECIPIENT', 'Alert Recipient');
defined('WATCH_MEMBER')            || define('WATCH_MEMBER', 'Watch Member');


defined('PLATFORM')      || define('PLATFORM', "Alonda");
defined('PREFIX_PAYMENT_PATH')      || define('PREFIX_PAYMENT_PATH', 'thirdparty/api/');


defined('USSD_CON')      || define('USSD_CON', "CON ");
defined('USSD_END')      || define('USSD_END', "END ");


defined('EXCEPTION') || define('EXCEPTION', "exception");
defined('NEW_RECIPIENT_CATEGORY') || define('NEW_RECIPIENT_CATEGORY', "new_recipient_emergency_category");
defined('NEW_RECIPIENT_PHONE') || define('NEW_RECIPIENT_PHONE', "new_recipient_phone");
defined('DELETE_RECIPIENT_CATEGORY') || define('DELETE_RECIPIENT_CATEGORY', "delete_recipient_category");
defined('DELETE_RECIPIENT_PHONE') || define('DELETE_RECIPIENT_PHONE', "delete_recipient_phone");
defined('RECIPIENTS_LIST_OFFSET') || define('RECIPIENTS_LIST_OFFSET', "recipients_list_offset");
defined('RECIPIENTS_LIST_LIMIT') || define('RECIPIENTS_LIST_LIMIT', "recipients_list_limit");
defined('PREVIOUS_INPUT') || define('PREVIOUS_INPUT', "previous_input");
defined('CURRENT_INPUT') || define('CURRENT_INPUT', "current_input");
defined('INPUT') || define('INPUT', "input");
defined('RECIPIENTS_LIST_OFFSET') || define('RECIPIENTS_LIST_OFFSET', "recipients_list_offset");
defined('RECIPIENTS_LIST_LIMIT') || define('RECIPIENTS_LIST_LIMIT', "recipients_list_limit");
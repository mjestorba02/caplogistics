# 🔍 OTP Debug Guide

## Quick Start

1. **Open Debug Logs Page**
   ```
   http://localhost/newcaplog1/pages/debug_logs.php
   ```

2. **Open Login Page in Another Tab**
   ```
   http://localhost/newcaplog1/index.php
   ```

3. **Open Browser DevTools (F12)**
   - Go to Console tab
   - Keep it visible while logging in

4. **Attempt Login**
   - Enter valid email and password
   - Watch the Console for `[DEBUG]` messages
   - Watch the Debug Logs page - it updates every 2 seconds

---

## What to Look For

### In Browser Console:
```
[DEBUG] Response status: otp_required
[DEBUG] Redirect URL: pages/otp_verify.php
[DEBUG] OTP required, redirecting to: pages/otp_verify.php
[DEBUG] Final redirect URL: pages/otp_verify.php
```

### In Debug Logs Page (/pages/debug_logs.php):
- **Auth Log** should show:
  ```
  [TIMESTAMP] [IP] AUTH_REQUEST_RECEIVED
  [TIMESTAMP] [IP] INPUT_PARSED | {"action":"login",...}
  [TIMESTAMP] [IP] OTP_GENERATED | {"user_id":X,"otp":"XXXXXX",...}
  [TIMESTAMP] [IP] SESSION_CREATED | {"temp_user_id":X}
  [TIMESTAMP] [IP] OTP_RESPONSE_SENT | {"redirect_url":"pages/otp_verify.php"}
  ```

- **Mail Log** should show:
  ```
  [TIMESTAMP] [SUCCESS] PHPMailer sent to email@example.com
  ```
  or
  ```
  [TIMESTAMP] [ERROR] PHPMailer failed: SMTP error message
  ```

---

## Common Issues & Solutions

### Issue: Console shows "otp_required" but doesn't redirect
- Check if `pages/otp_verify.php` exists
- Check console for any JavaScript errors
- Check the exact `redirect_url` value in the response

### Issue: OTP_RESPONSE_SENT doesn't appear in logs
- API might be crashing after OTP generation
- Check PHP error logs in XAMPP
- The debug logging might not be catching the error

### Issue: AUTH_REQUEST_RECEIVED doesn't appear
- API file might not be loading `debug_logger.php`
- Check if the include statement is correct

### Issue: Mail Log is empty
- Mail might be silently failing
- Check `email_config.php` for correct SMTP settings
- Check if PHPMailer is installed: `composer list` should show phpmailer

---

## How to View Actual Errors

If redirect isn't working, add this to `index.php` login code:

```javascript
if (data.status === 'otp_required') {
  // Add this before redirect:
  console.log('[FULL RESPONSE]:', JSON.stringify(data, null, 2));
  
  // Check if API returned any errors
  if (data.error) {
    console.error('[API_ERROR]:', data.error);
  }
}
```

---

## Files Created

1. `api/debug_logger.php` - Logging utility
2. `api/get_logs.php` - API to read logs
3. `pages/debug_logs.php` - Debug UI dashboard
4. `logs/` (auto-created) - Directory for log files
   - `logs/auth.log` - Auth flow logs
   - `logs/mail.log` - Email delivery logs

---

## Disable After Testing

Remove these lines from `api/auth.php` when done debugging:

```php
include 'debug_logger.php';
debugLog('AUTH_REQUEST_RECEIVED', ...);
debugLog('INPUT_PARSED', ...);
// etc...
```

Or set a debug flag:
```php
define('DEBUG_AUTH', false); // Change to true to enable
if (DEBUG_AUTH) {
  debugLog(...);
}
```

---

## View Logs Manually

SSH/Terminal into server:
```bash
tail -f logs/auth.log
tail -f logs/mail.log
```

Or via FTP, download and view:
- `/logs/auth.log`
- `/logs/mail.log`

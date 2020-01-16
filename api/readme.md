
## What CARDHEMP

```bash
- ?
```

## Overview

```bash
- This is an API Documentation for CARD HEMP
```

## Authentication

```bash
- Basic Auth (username, password) and API-KEY (token)
- The default format of the response

- 'array' (For GET Request Only) - Array data structure
- 'csv' (text/csv) - Comma separated file
- 'json' (application/json) - Uses json_encode(). Note: If a GET query string called 'callback' is passed, then jsonp will be returned
- 'html' (text/html) - HTML using the table library in CodeIgniter
- 'php' (For GET Request Only) - Uses var_export()
- 'serialized' (For GET Request Only) - Uses serialize()
- 'xml' (application/xml) - Uses simplexml_load_string()
```

## Server Requirements

```bash
PHP version 5.6 or newer is recommended.

It should work on 5.3.7 as well, but we strongly advise you NOT to run
such old versions of PHP, because of potential security and performance
issues, as well as missing features.
```

## Error Codes and Status Codes

```bash
- 200 = HTTP_OK (The request has succeeded).
- 102 = HTTP_PROCESSING
- 302 = HTTP_FOUND
- 400 = HTTP_BAD_REQUEST (The request cannot be fulfilled due to multiple errors).
- 401 = HTTP_UNAUTHORIZED (The user is unauthorized to access the requested resource).
- 403 = HTTP_FORBIDDEN (The requested resource is unavailable at this present time).
- 404 = HTTP_NOT_FOUND (This is sometimes used to mask if there was an UNAUTHORIZED (401) or FORBIDDEN (403) error, for security reasons).
- 405 = HTTP_METHOD_NOT_ALLOWED (The request method is not supported by the following resource).
- 408 = HTTP_REQUEST_TIMEOUT
```

## Documentation

```bash
- ?
```

## Author

```bash
- Robert Ram Bolista
```
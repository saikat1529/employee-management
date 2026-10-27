<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documents password
    |--------------------------------------------------------------------------
    |
    | One shared password that protects the HTML documents under /docs.
    | Set DOCS_PASSWORD in the .env file (never commit the real value).
    | If it is empty, the documents are refused completely.
    |
    */

    'password' => env('DOCS_PASSWORD', ''),

];

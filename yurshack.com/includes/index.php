<?php
// Deny direct web access if rewrite rules are unavailable.
http_response_code(403);
exit('Forbidden');

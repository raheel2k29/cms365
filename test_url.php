<?php
$url = 'postgresql://user:pass@ep-frosty-meadow-atz4g45c.pooler.c-9.us-east-1.aws.neon.tech/db?sslmode=require';
$parsed = parse_url($url);
print_r($parsed);

<?php

$payload = Array('status' => 'success', 'message' => 'The email was successfuly queued');

echo json_encode(Array('payload' => $payload));

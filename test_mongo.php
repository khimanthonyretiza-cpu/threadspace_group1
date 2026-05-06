<?php
require 'vendor/autoload.php';

if (class_exists('MongoDB\BSON\ObjectId')) {
    echo "✅ MongoDB\\BSON\\ObjectId exists";
} else {
    echo "❌ Class not found";
}
?>
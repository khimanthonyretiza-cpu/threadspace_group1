<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $mongoDb = $mongoClient->ecom_store;
    $productsCollection = $mongoDb->products;
} catch (Exception $e) {
    die("MongoDB connection failed: " . $e->getMessage());
}
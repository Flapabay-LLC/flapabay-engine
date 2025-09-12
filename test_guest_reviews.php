<?php

// Simple test to verify Guest Review implementation
echo "=== Guest Review Implementation Test ===\n\n";

// Test 1: Check if controller file exists
echo "1. Checking GuestReviewController...\n";
$controllerPath = __DIR__ . '/app/Http/Controllers/GuestReviewController.php';
if (file_exists($controllerPath)) {
    echo "  ✓ GuestReviewController.php exists\n";
    $content = file_get_contents($controllerPath);
    if (strpos($content, 'class GuestReviewController') !== false) {
        echo "  ✓ Controller class defined\n";
    }
    if (strpos($content, 'public function index') !== false) {
        echo "  ✓ index method exists\n";
    }
    if (strpos($content, 'public function store') !== false) {
        echo "  ✓ store method exists\n";
    }
    if (strpos($content, 'public function update') !== false) {
        echo "  ✓ update method exists\n";
    }
} else {
    echo "  ✗ GuestReviewController.php not found\n";
}

// Test 2: Check request validation classes
echo "\n2. Checking request validation classes...\n";
$storeRequestPath = __DIR__ . '/app/Http/Requests/StoreGuestReviewRequest.php';
$updateRequestPath = __DIR__ . '/app/Http/Requests/UpdateGuestReviewRequest.php';

if (file_exists($storeRequestPath)) {
    echo "  ✓ StoreGuestReviewRequest.php exists\n";
} else {
    echo "  ✗ StoreGuestReviewRequest.php not found\n";
}

if (file_exists($updateRequestPath)) {
    echo "  ✓ UpdateGuestReviewRequest.php exists\n";
} else {
    echo "  ✗ UpdateGuestReviewRequest.php not found\n";
}

// Test 3: Check UserReview model updates
echo "\n3. Checking UserReview model...\n";
$modelPath = __DIR__ . '/app/Models/UserReview.php';
if (file_exists($modelPath)) {
    echo "  ✓ UserReview.php exists\n";
    $content = file_get_contents($modelPath);
    if (strpos($content, 'trip_id') !== false) {
        echo "  ✓ trip_id field added to fillable\n";
    }
    if (strpos($content, 'public function booking') !== false) {
        echo "  ✓ booking relationship method exists\n";
    }
    if (strpos($content, 'public function hasHostResponse') !== false) {
        echo "  ✓ hasHostResponse method exists\n";
    }
} else {
    echo "  ✗ UserReview.php not found\n";
}

// Test 4: Check migration file
echo "\n4. Checking migration file...\n";
$migrationDir = __DIR__ . '/database/migrations';
if (is_dir($migrationDir)) {
    $files = scandir($migrationDir);
    $guestReviewMigration = null;
    foreach ($files as $file) {
        if (strpos($file, 'add_guest_review_fields_to_user_reviews_table') !== false) {
            $guestReviewMigration = $file;
            break;
        }
    }
    
    if ($guestReviewMigration) {
        echo "  ✓ Guest review migration exists: $guestReviewMigration\n";
    } else {
        echo "  ✗ Guest review migration not found\n";
    }
} else {
    echo "  ✗ Migrations directory not found\n";
}

// Test 5: Check routes file
echo "\n5. Checking API routes...\n";
$routesPath = __DIR__ . '/routes/api.php';
if (file_exists($routesPath)) {
    echo "  ✓ api.php exists\n";
    $content = file_get_contents($routesPath);
    if (strpos($content, 'GuestReviewController') !== false) {
        echo "  ✓ GuestReviewController imported\n";
    }
    if (strpos($content, 'reviews/guest') !== false) {
        echo "  ✓ Guest review routes defined\n";
    }
} else {
    echo "  ✗ api.php not found\n";
}

echo "\n=== Test Complete ===\n";
echo "\nTo test the API endpoints manually:\n";
echo "1. Ensure the server is running: php artisan serve\n";
echo "2. Get a JWT token by logging in\n";
echo "3. Test endpoints with curl or Postman:\n";
echo "   - GET /api/v1/reviews/guest (get user's reviews)\n";
echo "   - GET /api/v1/reviews/pending (get pending reviews)\n";
echo "   - POST /api/v1/reviews (create new review)\n";
echo "   - PUT /api/v1/reviews/{id} (update review)\n";
echo "   - DELETE /api/v1/reviews/{id} (delete review)\n";
echo "   - PATCH /api/v1/reviews/{id}/publish (publish review)\n";
<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

// Function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number format
function isValidPhone($phone) {
    return preg_match("/^\d{10}$/", $phone);
}

// Function to check if email is unique
function isUniqueEmail($db, $email, $id = null) {
    $query = "SELECT COUNT(*) FROM customers WHERE email = '$email'";
    if ($id !== null) {
        $query .= " AND id <> $id";
    }

    $result = mysqli_query($db, $query);
    $count = mysqli_fetch_row($result)[0];

    return $count == 0;
}

// Function to check if phone number is unique
function isUniquePhone($db, $phone, $id = null) {
    $query = "SELECT COUNT(*) FROM customers WHERE phone = '$phone'";
    if ($id !== null) {
        $query .= " AND id <> $id";
    }

    $result = mysqli_query($db, $query);
    $count = mysqli_fetch_row($result)[0];

    return $count == 0;
}

// get all customers
$app->get('/api/customers', function (Request $request, Response $response) {
    $sql = "SELECT * FROM customers";
    
    try {
        $db = connectDB();

        if (!$db) {
            echo '{"error":{"text":"Database connection error"}}';
            return;
        }

        $result = mysqli_query($db, $sql);
        $customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        mysqli_close($db);

        echo json_encode($customers);
    } catch (\Exception $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
});

// get single customers
$app->get('/api/customer/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM customers where id= $id";

    try {
        $db = connectDB();

        if (!$db) {
            echo '{"error":{"text":"Database connection error"}}';
            return;
        }

        $result = mysqli_query($db, $sql);
        $customer = mysqli_fetch_assoc($result);
        
        mysqli_close($db);

        echo json_encode($customer);
    } catch (\Exception $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
});

// Add customers
$app->post('/api/customer/add', function (Request $request, Response $response) {
    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name'); 
    $email = $request->getParam('email'); 
    $phone = $request->getParam('phone'); 

    // Validate fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        echo '{"error":{"text":"All fields are required"}}';
        return;
    }

    if (!isValidEmail($email)) {
        echo '{"error":{"text":"Invalid email format"}}';
        return;
    }

    if (!isValidPhone($phone)) {
        echo '{"error":{"text":"Invalid phone number format"}}';
        return;
    }

    // Check if email is unique
    $db = connectDB();
    if (!$db) {
        echo '{"error":{"text":"Database connection error"}}';
        return;
    }

    if (!isUniqueEmail($db, $email)) {
        echo '{"error":{"text":"Email address already exists"}}';
        mysqli_close($db);
        return;
    }

    // Check if phone number is unique
    if (!isUniquePhone($db, $phone)) {
        echo '{"error":{"text":"Phone number already exists"}}';
        mysqli_close($db);
        return;
    }

    $sql = "INSERT INTO `customers`(`first_name`, `last_name`, `email`, `phone`) VALUES ('$first_name','$last_name','$email','$phone')";
    
    try {
        mysqli_query($db, $sql);
        mysqli_close($db);

        echo '{"Notice":{"text":"Customer Add"}}';
    } catch (\Exception $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
});

// Update customers
$app->put('/api/customer/update/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');

    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name'); 
    $email = $request->getParam('email'); 
    $phone = $request->getParam('phone'); 

    // Validate fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        echo '{"error":{"text":"All fields are required"}}';
        return;
    }

    if (!isValidEmail($email)) {
        echo '{"error":{"text":"Invalid email format"}}';
        return;
    }

    if (!isValidPhone($phone)) {
        echo '{"error":{"text":"Invalid phone number format"}}';
        return;
    }

    // Check if email is unique
    $db = connectDB();
    if (!$db) {
        echo '{"error":{"text":"Database connection error"}}';
        return;
    }

    if (!isUniqueEmail($db, $email, $id)) {
        echo '{"error":{"text":"Email address already exists"}}';
        mysqli_close($db);
        return;
    }

    // Check if phone number is unique
    if (!isUniquePhone($db, $phone, $id)) {
        echo '{"error":{"text":"Phone number already exists"}}';
        mysqli_close($db);
        return;
    }

    $sql = "UPDATE `customers` SET first_name='$first_name',last_name='$last_name',email='$email',phone='$phone' WHERE id=$id";

    try {
        mysqli_query($db, $sql);
        mysqli_close($db);

        echo '{"Notice":{"text":"Customer Updated"}}';
    } catch (\Exception $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
});

// Delete customers
$app->delete('/api/customer/delete/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "DELETE FROM customers WHERE id=$id";

    try {
        $db = connectDB();

        if (!$db) {
            echo '{"error":{"text":"Database connection error"}}';
            return;
        }

        mysqli_query($db, $sql);
        mysqli_close($db);

        echo '{"Notice":{"text":"Customer Deleted"}}';
    } catch (\Exception $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
});

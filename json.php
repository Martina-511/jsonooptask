<?php

function loadContacts() {
    if (file_exists('contacts.json')) {
        $json = file_get_contents('contacts.json');
        $contacts = json_decode($json, true);
        
        if (!is_array($contacts)) {
            $contacts = [];
        }
        return $contacts;
    }
    return [];
}
function saveContacts($contacts) {
    $json = json_encode($contacts, JSON_PRETTY_PRINT);
    file_put_contents('contacts.json', $json);
}

function validateData($name, $phone, $email) {
    if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        return "Invalid name format. Please use letters and spaces only.";
    }
    if (!preg_match("/^\d{11}$/", $phone)) {
        return "Invalid phone number format. Please enter 11 digits.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }
    return true; 
}

$contacts = loadContacts();

if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $validationResult = validateData($name, $phone, $email);
    if ($validationResult === true) {
        $contacts[] = ['name' => $name, 'phone' => $phone, 'email' => $email];
        saveContacts($contacts);
        echo "Contact added successfully!";
    } else {
        echo $validationResult;
    }
}

if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
    $filteredContacts = [];

    foreach ($contacts as $contact) {
        if (preg_match("/$searchTerm/i", $contact['name'])) {
            $filteredContacts[] = $contact; 
        }
    }
    $contacts = $filteredContacts; }

?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Management</title>
</head>
<body>

<h1>Add New Contact</h1>
<form method="post">
    Name: <input type="text" name="name" required><br>
    Phone: <input type="text" name="phone" required><br>
    Email: <input type="email" name="email" required><br>
    <button type="submit" name="add">Add</button>
</form>

<h1>Search Contacts</h1>
<form method="post">
    Search: <input type="text" name="search">
    <button type="submit">Search</button>
</form>

<h2>Contacts</h2>
<ul>
    <?php foreach ($contacts as $contact): ?>
        <li>
            Name: <?= htmlspecialchars($contact['name']) ?>, 
            Phone: <?= htmlspecialchars($contact['phone']) ?>, 
            Email: <?= htmlspecialchars($contact['email']) ?>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>

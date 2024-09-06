<?php
session_start(); // Pastikan session dimulai di awal

// Database connection parameters
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$dbname = "lucky_draw";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM winner WHERE id = $id AND id_user = $user_id");
    header("Location: winner-participants.php");
    exit();
}

// Handle delete all action
if (isset($_GET['delete_all'])) {
    $conn->query("DELETE FROM winner WHERE id_user = $user_id");
    header("Location: winner-participants.php");
    exit();
}

$winnerQuery = "SELECT w.id, w.npk, w.nama, c.name AS category_name 
                FROM winner w
                JOIN categories c ON w.category_id = c.id
                WHERE w.id_user = $user_id";

if ($category_id) {
    $winnerQuery .= " AND w.category_id = $category_id";
}

$winnerResult = $conn->query($winnerQuery);

// Fetch categories from the database
$categoriesQuery = "SELECT * FROM categories WHERE id_user = $user_id";
$categoriesResult = $conn->query($categoriesQuery);

$selectedCategoryName = 'Pilih Kategori';
if ($category_id) {
    $categoryQuery = $conn->query("SELECT name FROM categories WHERE id = $category_id");
    if ($categoryRow = $categoryQuery->fetch_assoc()) {
        $selectedCategoryName = htmlspecialchars($categoryRow['name']);
    }
}

$hasWinner = $winnerResult->num_rows > 0;

$sql = "SELECT username FROM users WHERE id_user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = htmlspecialchars($row['username']);
} else {
    $username = "Tidak ditemukan";
}

$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pemenang</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap');
        *{
            font-family: "Rubik", sans-serif;
        }
        /* Basic styling for the popup */
        .popup {
            display: none; /* Hide the popup by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
        }
        .popup.active {
            display: flex; /* Show the popup when active */
        }

        /* Dropdown container */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        /* Dropdown content (hidden by default) */
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }

        /* Links inside the dropdown */
        .dropdown-content button {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        /* Change the background color of the button when the dropdown content is shown */
        .dropdown:hover .dropdown-button {
            background-color: #3e8e41;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <div class="heading_dashboard sticky top-0 py-3 pt-4 bg-gray-100">
            <div class="flex justify-between">
                <h1 class="text-2xl font-bold mb-4">Daftar Pemenang</h1>
                <p>Hallo, <?php echo $username; ?></p>
            </div>
            <div class="flex justify-between">
                <div class="flex gap-2">
                    <a href="index.php" target="blank_"><button class="bg-green-400 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Mulai Undian <iconify-icon icon="mdi:rocket"></iconify-icon></button></a>
                    <a href="dashboard.php" target="blank_"><button class="bg-blue-400 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Dashboard <iconify-icon icon="ic:round-dashboard"></iconify-icon></button></a>
                </div>
                <div class="flex gap-2">
                    <div class="dropdown">
                        <button id="dropdownButton" class="dropdown-button bg-green-500 text-white px-4 py-2 rounded-md mb-4">
                            <?php echo $selectedCategoryName ?: 'All Categories'; ?> 
                            <iconify-icon icon="ion:chevron-down"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <button onclick="filterByCategory('all', 'All Categories')">All Categories</button>
                            <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                                <div class="flex items-center justify-between">
                                    <button onclick="filterByCategory(<?php echo htmlspecialchars($category['id']); ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </button>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <!-- Button to trigger logout -->
                    <form action="logout.php" method="post" style="display: inline;">
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md  flex items-center gap-1">Logout <iconify-icon icon="ri:logout-circle-r-fill"></iconify-icon></button>
                    </form>
                </div>
            </div>
        </div>

        <table class="min-w-full bg-white border border-gray-300 rounded-md">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">#</th>
                    <th class="py-2 px-4 border-b">NPK</th>
                    <th class="py-2 px-4 border-b">Nama Pemenang</th>
                    <th class="py-2 px-4 border-b">Kategori</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1; // Initialize the counter variable
                while ($row = $winnerResult->fetch_assoc()): ?>
                    <tr>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($counter++); ?></td> <!-- Display the counter value -->
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['npk']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td class="py-2 px-4 border-b text-center">
                            <a href="winner-participants.php?delete=<?php echo htmlspecialchars($row['id']); ?>" class="text-red-500 ml-4" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if (!$hasWinner): ?>
            <div id="createCategoryContainer">
                <a href="index.php" target="blank_"><button class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4">Mulai Undian</button></a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelector('.dropdown-button').addEventListener('click', function () {
            var dropdownContent = document.querySelector('.dropdown-content');
            dropdownContent.style.display = (dropdownContent.style.display === 'block') ? 'none' : 'block';
        });

        // Optional: Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-button')) {
                var dropdowns = document.getElementsByClassName('dropdown-content');
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === 'block') {
                        openDropdown.style.display = 'none';
                    }
                }
            }
        }

        function confirmDeleteAll() {
            if (confirm('Are you sure you want to delete all participants? This action cannot be undone.')) {
                window.location.href = 'winner-participants.php?delete_all=1';
            }
        }

        function filterByCategory(categoryId, categoryName) {
            // Set the button text to the selected category name
            document.getElementById('dropdownButton').textContent = categoryName;

            // Reload the page with the selected category filter
            if (categoryId === 'all') {
                window.location.href = 'winner-participants.php'; // No filter applied
            } else {
                window.location.href = 'winner-participants.php?category_id=' + categoryId;
            }
        }

    </script>
</body>
</html>

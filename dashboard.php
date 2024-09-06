<?php
session_start();
// Database connection parameters
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "lucky_draw";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pastikan pengguna sudah login, jika tidak redirect ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM participants WHERE id = $id AND id_user = $user_id");
    header("Location: dashboard.php");
    exit();
}

// Handle delete all action
if (isset($_GET['delete_all'])) {
    $conn->query("DELETE FROM participants WHERE id_user = $user_id");
    header("Location: dashboard.php");
    exit();
}

$participantsQuery = "SELECT * FROM participants WHERE id_user = $user_id";
if ($category_id) {
    $participantsQuery .= " AND category_id = $category_id";
}
$participantsResult = $conn->query($participantsQuery);

// Fetch categories from the database
$categoriesQuery = "SELECT * FROM categories WHERE id_user = $user_id";
$categoriesResult = $conn->query($categoriesQuery);
// Ambil kategori dari database berdasarkan id_user
$categoriesResult1 = $conn->query("SELECT * FROM categories WHERE id_user = $user_id");
$categoriesResult2 = $conn->query("SELECT * FROM categories WHERE id_user = $user_id");

// Get the category name for the selected category if applicable
$selectedCategoryName = 'Pilih Kategori';
if ($category_id) {
    $categoryQuery = $conn->query("SELECT name FROM categories WHERE id = $category_id");
    if ($categoryRow = $categoryQuery->fetch_assoc()) {
        $selectedCategoryName = htmlspecialchars($categoryRow['name']);
    }
}

$hasCategories = $categoriesResult->num_rows > 0;

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
    <title>Dashboard</title>
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
    <script>
        // Cetak user_id di console.log
        var userId = <?php echo json_encode($user_id); ?>;
        console.log('User ID:', userId);
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-5  px-0">
        <div class="heading_dashboard sticky top-0 py-3 pt-4 bg-gray-100">
            <div class="flex justify-between">
                <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
                <p>Hallo, <?php echo $username; ?></p>
            </div>
            <div class="flex justify-between">
                <div class="flex gap-2">
                    <a href="index.php" target="blank_"><button class="bg-green-400 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Mulai Undian <iconify-icon icon="mdi:rocket"></iconify-icon></button></a>
                    <button onclick="openAddPopup()" class="bg-blue-500 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Tambah Peserta <iconify-icon icon="octicon:plus-16"></iconify-icon></button>
                    <button onclick="openAddFilePopup()" class="bg-blue-500 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Upload XLSX <iconify-icon icon="octicon:plus-16"></iconify-icon></button>
                    <a href="winner-participants.php"><button class="bg-yellow-500 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Lihat Pemenang <iconify-icon icon="ion:trophy"></iconify-icon></button></a>
                </div>
                <div class="flex gap-2">
                    <div class="dropdown">
                        <button id="dropdownButton" class="dropdown-button bg-green-500 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">
                            <?php echo $selectedCategoryName ?: 'All Categories'; ?> 
                            <iconify-icon icon="ion:chevron-down"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <button onclick="filterByCategory('all', 'All Categories')">All Categories</button>
                            <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                                <div class="flex items-center justify-start">
                                    <button onclick="filterByCategory(<?php echo htmlspecialchars($category['id']); ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </button>
                                    <iconify-icon icon="ion:close" onclick="deleteCategory(<?php echo htmlspecialchars($category['id']); ?>)" class="cursor-pointer text-red-500"></iconify-icon>
                                </div>
                            <?php endwhile; ?>
                            <button onclick="openCreateCategoryPopup()">Tambah Kategori</button>
                        </div>
                    </div>
                    <button onclick="openCreateCategoryPopup()" class="bg-green-500 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Buat Kategori <iconify-icon icon="octicon:plus-16"></iconify-icon></button>
                    <button onclick="confirmDeleteAll()" class="bg-red-500 text-white px-4 py-2 rounded-md mb-4 flex items-center gap-1">Hapus semua peserta <iconify-icon icon="ion:trash"></iconify-icon></button>
                    <!-- Button to trigger logout -->
                    <form action="logout.php" method="post" style="display: inline;">
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md  flex items-center gap-1">Logout <iconify-icon icon="ri:logout-circle-r-fill"></iconify-icon></button>
                    </form>
                </div>
            </div>
        </div>

        <table class="min-w-full bg-white border border-gray-300 rounded-md">
            <thead >
                <tr>
                    <th class="py-2 px-4 border-b">#</th>
                    <th class="py-2 px-4 border-b">NPK</th>
                    <th class="py-2 px-4 border-b">Nama Peserta</th>
                    <th class="py-2 px-4 border-b">Unit Kerja</th>
                    <th class="py-2 px-4 border-b">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1; // Initialize the counter variable
                while ($row = $participantsResult->fetch_assoc()): ?>
                    <tr>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($counter++); ?></td> <!-- Display the counter value -->
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($row['npk']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['unit_kerja']); ?></td>
                        <td class="py-2 px-4 border-b flex justify-center">
                            <button onclick="openEditPopup(<?php echo htmlspecialchars($row['id']); ?>, '<?php echo htmlspecialchars($row['nama']); ?>')" class="text-blue-500">Edit</button>
                            <a href="dashboard.php?delete=<?php echo htmlspecialchars($row['id']); ?>" class="text-red-500 ml-4" onclick="return confirm('Are you sure you want to delete this user?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Button for creating categories -->
        <?php if (!$hasCategories): ?>
            <div id="createCategoryContainer" class="flex items-center justify-center" style="height:13vw;">
                <button onclick="openCreateCategoryPopup()" class="bg-green-500 text-white px-4 py-2 rounded-md mt-10 text-center">Buat Kategori Dulu!</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Popup -->
    <div id="editPopup" class="popup">
        <div class="popup-content">
            <h2>Edit User</h2>
            <form id="editForm" action="edit-user.php" method="post">
                <input type="hidden" name="id" id="userId">
                <label for="userName">Name:</label>
                <input type="text" name="name" id="userName" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                <button type="submit" style="background-color: blue; color: white; padding: 10px; border: none; border-radius: 4px; margin-right: 10px;">Save</button>
                <button type="button" onclick="closeEditPopup()" style="background-color: red; color: white; padding: 10px; border: none; border-radius: 4px;">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Popup for Create Category -->
    <div id="createCategoryPopup" class="popup">
        <div class="popup-content">
            <form action="create-category.php" method="post">
                <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                <label for="categoryName">Category Name:</label>
                <input type="text" name="name" id="categoryName" required class="w-full border border-gray-300 p-2 mb-4" placeholder="Category Name">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md">Create</button>
                <button type="button" onclick="closeCreateCategoryPopup()" class="bg-red-500 text-white px-4 py-2 rounded-md">Cancel</button>
            </form>
        </div>
    </div>


    <div id="addPopup" class="popup">
        <div class="popup-content">
            <form action="save-participants.php" method="post">
                <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>" />

                <label for="npk">NPK:</label>
                <input type="text" name="npks[]" id="npk" required class="w-full border border-gray-300 p-2 mb-2" placeholder="NPK">
                
                <label for="name">Name:</label>
                <input type="text" name="names[]" id="name" required class="w-full border border-gray-300 p-2 mb-4" placeholder="Nama">

                <label for="unit_kerja">Unit Kerja:</label>
                <input type="text" name="unit_kerja[]" id="unit_kerja" required class="w-full border border-gray-300 p-2 mb-4" placeholder="Unit Kerja">

                <label for="fileCategory">Category:</label>
                <select name="category_id" id="fileCategory" required class="w-full border border-gray-300 p-2 mb-4">
                    <option value="" disabled selected>Pilih dulu kategori</option>
                    <?php while ($category = $categoriesResult1->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md mr-2">Save</button>
                <button type="button" onclick="closeAddPopup()" class="bg-red-500 text-white px-4 py-2 rounded-md">Cancel</button>
            </form>
        </div>
    </div>

    <div id="addFilePopup" class="popup">
        <div class="popup-content">
            <form action="upload-participants.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>" />
                <label for="file">Upload Excel File:</label>
                <input type="file" name="file" id="file" accept=".xlsx" required class="w-full border border-gray-300 p-2 mb-4">
                <label for="fileCategory">Category:</label>
                <select name="category_id" id="fileCategory" required class="w-full border border-gray-300 p-2 mb-4">
                    <option value="" disabled selected>Pilih dulu kategori</option>
                    <?php
                    while ($category = $categoriesResult2->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md mr-2">Upload</button>
                <button type="button" onclick="closeAddFilePopup()" class="bg-red-500 text-white px-4 py-2 rounded-md">Cancel</button>
            </form>
        </div>
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
                window.location.href = 'dashboard.php?delete_all=1';
            }
        }

        function openCreateCategoryPopup() {
            document.getElementById('createCategoryPopup').classList.add('active');
        }

        function closeCreateCategoryPopup() {
            document.getElementById('createCategoryPopup').classList.remove('active');
        }

        function openAddFilePopup() {
            document.getElementById('addFilePopup').classList.add('active');
        }

        function closeAddFilePopup() {
            document.getElementById('addFilePopup').classList.remove('active');
        }

        function openAddPopup() {
            document.getElementById('addPopup').classList.add('active');
        }

        function closeAddPopup() {
            document.getElementById('addPopup').classList.remove('active');
        }

        function openEditPopup(id, name) {
            document.getElementById('userId').value = id;
            document.getElementById('userName').value = name;
            document.getElementById('editPopup').classList.add('active');
        }

        function closeEditPopup() {
            document.getElementById('editPopup').classList.remove('active');
        }

        function filterByCategory(categoryId, categoryName) {
            // Set the button text to the selected category name
            document.getElementById('dropdownButton').textContent = categoryName;

            // Reload the page with the selected category filter
            if (categoryId === 'all') {
                window.location.href = 'dashboard.php'; // No filter applied
            } else {
                window.location.href = 'dashboard.php?category_id=' + categoryId;
            }
        }

        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                // Make an AJAX request to delete the category
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete-category.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Reload the page to reflect the changes
                        location.reload();
                    } else {
                        alert('Failed to delete category. Please try again.');
                    }
                };
                xhr.send('category_id=' + categoryId);
            }
        }


    </script>
</body>
</html>

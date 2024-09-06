<?php
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lucky_draw";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil id_user dari sesi
$id_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($id_user === null) {
    // Menyimpan HTML dalam buffer
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Dulu</title>
        <link rel="stylesheet" href="css/style.css"> <!-- Ganti dengan path CSS sesuai kebutuhan -->
    </head>
    <body>
        <div class="container_home">
            <img src="images/bg_home.png" class="bg_home" style="width:100%;" alt="">
            <div class="layout_home">
                <img src="images/logo_gebyar.png" class="logo_home" alt="">
                <img src="images/belum_login.png" class="belum_login" alt="">
                <div class="layout_winner">
                    <p class="winner_name">Untuk mengakses halaman ini, <br/>silakan login terlebih dahulu.</p>
                </div>
                <div class="btn_draw_layout">
                    <a href="login.php"><button class="btn_draw btn_draw_start">Login</button></a>
                    <a href="register.php"><button class="btn_draw btn_draw_stop">Register</button></a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    // Menyimpan buffer ke output
    ob_end_flush();
    die(); // Menghentikan eksekusi script
}

// Fetch participants from the database
$participantsQuery = "SELECT npk, nama, category_id FROM participants WHERE id_user = $id_user";
$participantsResult = $conn->query($participantsQuery);

if ($participantsResult === false) {
    die("Error fetching participants: " . $conn->error);
}

// Fetch participants and their categories
$participantsArray = [];
while ($row = $participantsResult->fetch_assoc()) {
    $participantsArray[] = [
        'npk' => $row['npk'],
        'nama' => $row['nama'],
        'category_id' => $row['category_id']
    ];
}

// Fetch categories from the database
$categoriesQuery = "SELECT id, name FROM categories WHERE id_user = $id_user";
$categoriesResult = $conn->query($categoriesQuery);

if ($categoriesResult === false) {
    die("Error fetching categories: " . $conn->error);
}

$categoriesArray = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categoriesArray[] = [
        'id' => $row['id'],
        'name' => $row['name']
    ];
}

// Count participants for each category
$categoryCounts = [];
foreach ($participantsArray as $participant) {
    $categoryId = $participant['category_id'];
    if (!isset($categoryCounts[$categoryId])) {
        $categoryCounts[$categoryId] = 0;
    }
    $categoryCounts[$categoryId]++;
}

$totalParticipants = array_sum(array_values($categoryCounts));

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FF0000"> <!-- Sesuaikan warna tema -->
    <link rel="icon" type="image/png" href="images/favicon.png"> <!-- Ganti dengan favicon sesuai kebutuhan -->
    <title>Gebyar Korsa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="./fontawesome-free-6.5.1-web/css/all.min.css">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
</head>

<body class="flex items-center justify-center bg-blue-500 h-screen">
    <div class="container_home">
        <img src="images/bg_home.png" class="bg_home" alt="">
        <div class="layout_home">
            <img src="images/logo_gebyar.png" class="logo_home" alt="">
            <img src="images/heading.png" class="heading_image" alt="">

            <!-- Dropdown untuk kategori jika ada lebih dari satu kategori -->
            <?php if (count($categoriesArray) > 1): ?>
                <div class="select_category_home">
                    <div class="select_layout">
                        <select id="categorySelect">
                            <?php foreach ($categoriesArray as $index => $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php if ($index === 0): ?>
                                        selected
                                    <?php endif; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <iconify-icon icon="ion:chevron-down"></iconify-icon>
                    </div>
                </div>
            <?php elseif (count($categoriesArray) === 1): ?>
                <input type="hidden" id="categorySelect" value="<?php echo $categoriesArray[0]['id']; ?>">
            <?php endif; ?>



            <!-- Tabel Peserta -->
            <div id="participantsTable" class="mb-4">
                <table>
                    <thead>
                        <tr>
                            <th class="border border-gray-400 px-4 py-2">NPK</th>
                            <th class="border border-gray-400 px-4 py-2">Nama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participantsArray as $participant): ?>
                        <tr>
                            <td class="border border-gray-400 px-4 py-2"><?php echo $participant['npk']; ?></td>
                            <td class="border border-gray-400 px-4 py-2"><?php echo $participant['nama']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="winner" class="layout_winner"></div>
             <!-- <div class="layout_winner">
                <p class="winner_name winner_name_results">Pemenang: <br/>Bachtiar Yusuf Nasruddin Amanullah <br/> NPK: 4244778</p>
             </div> -->
            
            <?php if (count($categoriesArray) > 1): ?>
                <h4 class="jumlah_peserta"><?php echo $totalParticipants; ?> PESERTA TERDAFTAR</h4>
            <?php endif; ?>

            <!-- <div id="winnerModal" class="popup_winner hidden">
                <div class="modal_content">
                    <span class="close-button" onclick="closeModal()">&times;</span>
                    <h2>Pemenang</h2>
                    <p id="winnerName"></p>
                </div>
            </div> -->

            

            <audio id="winnerSound" src="winner-sound.mp3"></audio>
            <audio id="drawingSound" src="draw-sound.mp3"></audio>

            <div class="btn_draw_layout">
                <button id="startButton" class="btn_draw btn_draw_start" onclick="startDraw()">Mulai</button>
                <button id="stopButton" class="btn_draw btn_draw_stop" onclick="stopDraw()">Stop</button>
            </div>

            <div class="btn_action_draw">
                <button id="refreshButton" class="fullscreen_btn">
                    <img src="images/refresh_icon.svg" alt="">
                </button>
                <button onclick="toggleFullscreen()" id="fullscreenButton" class="fullscreen_btn">
                    <img src="images/fullscreen_icon.svg" alt="">
                </button>
            </div>
        </div>
    </div>

    <div id="winnerModal" class="popup_winner">
        <div class="overlay_popup"></div>
        <div class="modal_content">
            <span class="close_button" onclick="closeModal()"><img src="images/close_btn.png" alt=""></span>
            <img src="images/pemenang_img.png" alt="" class="pemenang_heading">
            <div id="winnerName" class="popup_winner_content">
            </div>
            <img src="images/throphy_img.png" class="throphy_img" alt="">
        </div>
    </div>


    <script>
        let participants = [];
        let drawInterval;
        let isDrawing = false;
        let selectedCategoryId = '';
        var idUser = <?php echo json_encode($id_user); ?>;

        const categorySelectElement = document.getElementById('categorySelect');

        document.getElementById("refreshButton").addEventListener("click", function() {
            location.reload();
        });

        function startDraw() {
            if (isDrawing) return; // Jika undian sedang berlangsung, abaikan
            isDrawing = true;

            drawingSound.currentTime = 2;
            drawingSound.play();
            drawingSound.loop = true;

            winnerSound.currentTime = 0;
            winnerSound.pause(); // Play winner sound
            participantsTable.style.display = 'none';

            // Ubah tampilan tombol
            document.getElementById("startButton").classList.add("opacity");
            document.getElementById("stopButton").classList.remove("opacity");

            // Mulai proses undian (misalnya dengan interval untuk mengganti nama secara cepat)
            drawInterval = setInterval(() => {
                const randomIndex = Math.floor(Math.random() * participants.length);
                const currentParticipant = participants[randomIndex];

                // Tampilkan nama peserta yang sedang diacak di elemen winnerContainer
                document.getElementById("winner").innerHTML = `<p class="winner_name">${currentParticipant.nama}</p>`;
            }, 100); // Ganti nama setiap 100ms
        }


        function stopDraw() {
            if (!isDrawing) return; // Jika undian belum dimulai, abaikan
            isDrawing = false;

            // Hentikan interval undian
            clearInterval(drawInterval);
            drawingSound.pause();
            drawingSound.currentTime = 0;
            drawingSound.loop = false;

            winnerSound.currentTime = 1;
            winnerSound.play(); // Play winner sound

            // Pilih pemenang
            const winnerIndex = Math.floor(Math.random() * participants.length);
            const winner = participants[winnerIndex];

            // Tampilkan nama pemenang di modal popup
            showWinner(winner.nama, winner.npk);

            // Tampilkan pemenang di elemen winnerContainer
            document.getElementById("winner").innerHTML = `<p class="winner_name winner_name_results">FUNtastEAST Indonesia</p>`;

            // Hapus peserta pemenang dari daftar
            participants.splice(winnerIndex, 1);

            // Ubah tombol kembali
            document.getElementById("startButton").classList.remove("opacity");
            document.getElementById("stopButton").classList.add("opacity");

            // Panggil fungsi untuk menyimpan pemenang ke database
            saveWinnerToDatabase(winner.npk, winner.nama, selectedCategoryId);

        }


        function fetchParticipants(categoryId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `fetch_participants.php?category_id=${categoryId}`, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    participants = JSON.parse(xhr.responseText);

                    // Tampilkan peserta dalam tabel
                    const participantsTableBody = document.querySelector('#participantsTable tbody');
                    participantsTableBody.innerHTML = ''; // Kosongkan tabel terlebih dahulu

                    participants.forEach(participant => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${participant.npk}</td>
                            <td>${participant.nama}</td>
                        `;
                        participantsTableBody.appendChild(row);
                    });
                }
            };
            xhr.send();
        }

        // Function to show the modal
        function showWinner(winnerName, winnerNPK) {
            document.getElementById("winnerName").innerHTML = `<p>${winnerName}</p>  <p>NPK: ${winnerNPK}</p>`; // Set winner name dan NPK
            document.getElementById("winnerModal").classList.add("active_winner");

            setTimeout(() => {
                startConfetti();
            }, 500);
        }


        // Function to close the modal
        function closeModal() {
            document.getElementById("winnerModal").classList.remove("active_winner"); // Hide modal
        }


        function fetchCategories() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_categories.php', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const categories = JSON.parse(xhr.responseText);
                    const categorySelectElement = document.getElementById('categorySelect');

                    if (categorySelectElement) {
                        categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            categorySelectElement.appendChild(option);
                        });

                        // Ensure selected category ID is fetched after categories are loaded
                        const categoryId = categorySelectElement.value || categorySelectElement.getAttribute('value');
                        if (categoryId) {
                            fetchParticipants(categoryId);
                        }

                        // Update participants when the category changes
                        categorySelectElement.addEventListener('change', function() {
                            const selectedCategoryId = this.value;
                            fetchParticipants(selectedCategoryId);
                        });
                    }
                }
            };
            xhr.send();
        }

        fetchCategories();

        document.addEventListener('DOMContentLoaded', (event) => {
            const categorySelectElement = document.getElementById('categorySelect');
            selectedCategoryId = categorySelectElement ? categorySelectElement.value : categorySelectElement.getAttribute('value');
            if (selectedCategoryId) {
                fetchParticipants(selectedCategoryId);
            }
        });

        categorySelectElement?.addEventListener('change', function() {
            selectedCategoryId = this.value;
            fetchParticipants(selectedCategoryId);
        });

        function saveWinnerToDatabase(npk, nama, categoryId) {
            console.log(`Saving winner: npk=${npk}, nama=${nama}, categoryId=${categoryId}, idUser=${idUser}`);
            
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "save_winner.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log("Winner saved successfully!");
                    console.log("Response:", xhr.responseText); // Log the response for debugging
                } else {
                    console.error("Error saving winner: " + xhr.statusText);
                }
            };

            xhr.send(`npk=${encodeURIComponent(npk)}&nama=${encodeURIComponent(nama)}&category_id=${encodeURIComponent(categoryId)}&id_user=${encodeURIComponent(idUser)}`);
        }



        function startConfetti() {
            var end = Date.now() + (15 * 1000);
            var schoolPrideColors = ['#F12C8F', '#FF7D4C'];

            (function frame() {
                confetti({
                    particleCount: 2,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0 },
                    colors: schoolPrideColors
                });

                confetti({
                    particleCount: 2,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1 },
                    colors: schoolPrideColors
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            }());
        }

        function animateRandomName(container) {
            if (participants.length === 0) return;

            const randomParticipant = participants[Math.floor(Math.random() * participants.length)];
            const scrollingNameElement = document.createElement('p');
            scrollingNameElement.className = 'text-blue-500 font-bold scrolling-names';
            scrollingNameElement.textContent = `Drawing: ${randomParticipant.nama} (NPK: ${randomParticipant.npk})`;

            container.innerHTML = '';
            container.appendChild(scrollingNameElement);
        }

        function toggleFullscreen() {
            const fullscreenButton = document.getElementById('fullscreenButton');
            const element = document.documentElement;

            if (!document.fullscreenElement) {
                if (element.requestFullscreen) {
                    element.requestFullscreen();
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }

                fullscreenButton.innerHTML = '<img src="images/fullscreen_icon.svg" alt="">';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }

                fullscreenButton.innerHTML = '<img src="images/fullscreen_icon.svg" alt="">';
            }
        }
    </script>
</body>

</html>

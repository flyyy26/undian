<?php
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

// Fetch participants from the database
$participantsResult = $conn->query("SELECT npk, nama FROM participants");

$participantsArray = [];

while ($row = $participantsResult->fetch_assoc()) {
    $participantsArray[] = [
        'npk' => $row['npk'],
        'nama' => $row['nama']
    ]; // Ensure the field names are correct
}

$categoriesResult = $conn->query("SELECT id, name FROM categories");

$categoriesArray = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categoriesArray[] = [
        'id' => $row['id'],
        'name' => $row['name']
    ];
}

$conn->close();

$singleCategory = count($categoriesArray) === 1 ? $categoriesArray[0]['id'] : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FF0000"> <!-- Sesuaikan warna tema -->
    <link rel="icon" type="image/png" href="./winner-logo.svg"> <!-- Ganti dengan favicon sesuai kebutuhan -->
    <title>Lucky Draw</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./fontawesome-free-6.5.1-web/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <style>
        @keyframes highlightWinner {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes scrollAnimation {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(-100%);
            }
        }

        .scrolling-names {
            animation: scrollAnimation 5s linear infinite;
            /* Sesuaikan durasi animasi yang diinginkan */
            display: inline-block;
        }
    </style>
</head>

<body class="flex items-center justify-center bg-blue-500 h-screen">
    <div class="bg-gray-100 p-8 rounded-md shadow-md sm:w-96 md:w-1/2 lg:w-1/3 xl:w-1/4 text-center relative">
        <h1 class="text-2xl font-bold mb-4">Lucky Draw</h1>

        <?php if (count($categoriesArray) > 1): ?>
                <div class="mb-4">
                    <label for="categorySelect" class="block text-sm font-medium text-gray-700">Select Category</label>
                    <select id="categorySelect" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <!-- Opsi default -->
                        <option value="" disabled selected>Pilih di sini</option>

                        <?php foreach ($categoriesArray as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
        <?php else: ?>
                <input type="hidden" id="categorySelect" value="<?php echo $singleCategory; ?>">
        <?php endif; ?>

        <div id="winner" class="mb-4"></div>

        <!-- Tambahkan elemen audio untuk suara -->
        <audio id="winnerSound" src="./winner-sound.mp3"></audio>
        <audio id="drawingSound" src="./draw-sound.mp3"></audio>

        <button onclick="toggleDraw()" id="drawButton" class="bg-yellow-400 text-gray-100 px-4 py-2 rounded-md">Draw Winner</button>

        <!-- Tombol Fullscreen -->
        <button onclick="toggleFullscreen()" id="fullscreenButton" class="absolute top-0 right-0 md:right-10 m-2 p-2 bg-red-300 rounded-md">
            <i class="fas fa-expand"></i>
        </button>
    </div>

    <script>
        let participants = [];
        let isDrawing = false;
        let drawInterval;

        // Fetch participants based on selected category
        function fetchParticipants(categoryId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `fetch_participants.php?category_id=${categoryId}`, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    participants = JSON.parse(xhr.responseText);
                }
            };
            xhr.send();
        }

        const categorySelectElement = document.getElementById('categorySelect');
        const categoryId = categorySelectElement ? categorySelectElement.value : categorySelectElement.getAttribute('value');
        if (categoryId) {
            fetchParticipants(categoryId);
        }

        categorySelectElement?.addEventListener('change', function() {
            const selectedCategoryId = this.value;
            fetchParticipants(selectedCategoryId);
        });

        function toggleDraw() {
            const drawButton = document.getElementById('drawButton');
            const winnerContainer = document.getElementById('winner');
            const drawingSound = document.getElementById('drawingSound');
            const winnerSound = document.getElementById('winnerSound');

            if (participants.length === 0) {
                alert("No participants available. Please add participants.");
                return;
            }

            if (!isDrawing) {
                drawButton.textContent = 'Stop';
                isDrawing = true;

                drawingSound.play();
                drawingSound.loop = true;

                drawInterval = setInterval(() => {
                    animateRandomName(winnerContainer);
                }, 30);
            } else {
                drawButton.textContent = 'Draw Winner';
                isDrawing = false;

                clearInterval(drawInterval);

                drawingSound.pause();
                drawingSound.currentTime = 0;
                drawingSound.loop = false;

                winnerSound.play();

                const winnerIndex = Math.floor(Math.random() * participants.length);
                const winner = participants[winnerIndex];

                winnerContainer.innerHTML = `<p class="text-green-500 font-bold animate-pulse">Winner: ${winner.nama} (NPK: ${winner.npk})</p>`;

                participants.splice(winnerIndex, 1);

                startConfetti();
            }
        }

        function startConfetti() {
            var end = Date.now() + (15 * 1000);
            var schoolPrideColors = ['#00DE00', '#ffffff'];

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

                fullscreenButton.innerHTML = '<i class="fas fa-compress"></i>';
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

                fullscreenButton.innerHTML = '<i class="fas fa-expand"></i>';
            }
        }
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safe Place to Ask!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/gemini_style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .chat-container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .sidebar h4 {
            margin-bottom: 20px;
        }
        .session-list {
            list-style: none;
            padding: 0;
            overflow-y: auto;
            flex-grow: 1;
        }
        .session-list li {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .session-list li:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .session-list .active {
            background: #007bff;
        }
        .chat-box {
            flex-grow: 1;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .loading {
            font-style: italic;
            color: gray;
        }
        .prompt-list {
            display: flex;
            flex-wrap: wrap; /* Agar item-item dapat berjejer */
            gap: 10px; /* Jarak antar prompt */
            list-style: none;
            padding: 0;
        }
        .prompt-list li {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            max-width: 200px; /* Batas ukuran lebar */
            flex: 1 1 200px; /* Setiap item memiliki lebar minimum 200px */
            overflow: hidden; /* Agar tidak meluap */
        }
        .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Sidebar untuk memilih sesi -->
        <div class="sidebar">
            <h4>Chat Sessions</h4>
            <ul class="session-list" id="sessionList">
                <li class="active" onclick="changeSession(1)">Session 1</li>
            </ul>
            <button class="btn btn-primary mt-3" onclick="newSession()">New Session</button>
        </div>

        <!-- Chat utama -->
        <div class="container mt-5">
            <h2 class="text-center mb-4">Ask me anything!</h2>
            <div class="chat-box shadow p-4">
                <div class="mb-3">
                    <label for="prompt" class="form-label">Ask me a question:</label>
                    <input type="text" id="prompt" class="form-control" placeholder="Ask AI something..." />
                </div>
                <button class="btn btn-primary w-100" onclick="generateGemini()">Send</button>

                <div class="mt-4">
                    <h5>Prompt / Question:</h5>
                    <div id="question" class="question-box">
                        <div class="loading" id="loading-message">No Question Yet</div>
                    </div>
                </div>

                <div class="mt-4">
                    <h5>Results (Gemini AI API):</h5>
                    <div id="response" class="response-box">
                        <div class="loading" id="loading-message">No Respond Yet.</div>
                    </div>
                </div>

                <!-- Daftar prompt sebelumnya -->
                <div class="mt-4">
                    <h5>Previous Prompts:</h5>
                    <ul id="previousPrompts" class="prompt-list">
                        <!-- Daftar prompt sebelumnya akan ditampilkan di sini -->
                    </ul>
                </div>

                <button class="btn btn-secondary w-100 mt-3" onclick="resetChat()">Clear</button>
                <button class="btn btn-success w-100 mt-2" onclick="saveChat()">Save Response</button>
                <!-- Button untuk membuka modal -->
                <button class="btn btn-info w-100 mt-2" onclick="openModal()">View All Prompts</button>
            </div>
        </div>
    </div>

    <!-- Modal untuk melihat semua prompt dan jawaban -->
    <div class="modal fade" id="promptsModal" tabindex="-1" aria-labelledby="promptsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promptsModalLabel">All Prompts & Responses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="allPrompts" class="list-group">
                        <!-- Kumpulan prompt dan jawaban akan ditampilkan di sini -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        let sessionCount = 1;
        let activeSessionPrompts = [];  // Menyimpan prompt untuk sesi aktif
        let activeSessionResponses = []; // Menyimpan respons untuk sesi aktif

        function newSession() {
            sessionCount++;
            let sessionList = document.getElementById("sessionList");
            let newSession = document.createElement("li");
            newSession.textContent = "Session " + sessionCount;
            newSession.setAttribute("onclick", "changeSession(" + sessionCount + ")");
            sessionList.appendChild(newSession);
            changeSession(sessionCount);
        }

        function changeSession(sessionId) {
            let sessionItems = document.querySelectorAll(".session-list li");
            sessionItems.forEach(item => item.classList.remove("active"));
            sessionItems[sessionId - 1].classList.add("active");

            // Reset pertanyaan dan respons
            document.getElementById("question").innerHTML = "<div class='loading'>No Question Yet</div>";
            document.getElementById("response").innerHTML = "<div class='loading'>No Respond Yet.</div>";

            // Reset daftar prompt dan simpan prompt sesi sebelumnya
            activeSessionPrompts = [];
            activeSessionResponses = [];
            updatePreviousPrompts();
        }

        function generateGemini() {
            let question = document.getElementById("prompt").value;
            if (!question) {
                Swal.fire("Oops!", "Please enter a question!", "warning");
                return;
            }

            let response = "Example AI Response for: " + question;

            // Simpan pertanyaan dan respons ke sesi aktif
            activeSessionPrompts.push(question);
            activeSessionResponses.push(response);

            updatePreviousPrompts();

            document.getElementById("question").innerHTML = `<p>${question}</p>`;
            document.getElementById("response").innerHTML = "<div class='loading'>Generating response...</div>";

            setTimeout(() => {
                document.getElementById("response").innerHTML = `<p>${response}</p>`;
            }, 1500);
        }

        function resetChat() {
            document.getElementById("question").innerHTML = "<div class='loading'>No Question Yet</div>";
            document.getElementById("response").innerHTML = "<div class='loading'>No Respond Yet.</div>";
        }

        function saveChat() {
            Swal.fire("Saved!", "Your chat has been saved.", "success");
        }

        // Fungsi untuk memperbarui daftar prompt sebelumnya
        function updatePreviousPrompts() {
            const previousPromptsList = document.getElementById("previousPrompts");
            previousPromptsList.innerHTML = '';  // Clear current list

            activeSessionPrompts.forEach((prompt, index) => {
                let listItem = document.createElement("li");
                listItem.textContent = prompt;
                previousPromptsList.appendChild(listItem);
            });
        }

        // Fungsi untuk membuka modal dan menampilkan semua prompt
        function openModal() {
            const allPromptsList = document.getElementById("allPrompts");
            allPromptsList.innerHTML = '';  // Clear current list

            activeSessionPrompts.forEach((prompt, index) => {
                let listItem = document.createElement("li");
                listItem.classList.add("list-group-item");
                listItem.innerHTML = `<strong>Prompt:</strong> ${prompt} <br> <strong>Response:</strong> ${activeSessionResponses[index]}`;
                allPromptsList.appendChild(listItem);
            });

            // Menampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('promptsModal'));
            modal.show();
        }
    </script>

    <!-- Tambahkan bootstrap JS untuk modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

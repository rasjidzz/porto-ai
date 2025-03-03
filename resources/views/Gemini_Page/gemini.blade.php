<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safe Place to Ask !</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/gemini_style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h2 class="text-center mb-4">Ask Me Anything!</h2>
        <div class="card">
            <div class="mt-4">
                <h5 style="color: azure">Results:</h5>
                <div id="response" class="response-box" style="color: azure">
                    <div class="loading-message">Waiting....</div>
                </div>
            </div>
            <br>
            <br>
            <div class="mb-3">
                <label for="prompt" class="form-label" style="color: azure">Your Question:</label>
                <input type="text" id="prompt" class="form-control" placeholder="Type your question...">
            </div>
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary me-2" onclick="generateGemini()">Send</button>
                <button class="btn btn-secondary" onclick="resetChat()">Clear</button>
            </div>
        </div>
    </div>


    <script>

particlesJS("particles-js", {
    "particles": {
        "number": {
            "value": 80,
            "density": { "enable": true, "value_area": 800 }
        },
        "color": { "value": "#ffffff" },
        "shape": {
            "type": "circle",
            "stroke": { "width": 0, "color": "#000000" }
        },
        "opacity": {
            "value": 0.5,
            "random": false,
            "anim": { "enable": false }
        },
        "size": {
            "value": 3,
            "random": true,
            "anim": { "enable": false }
        },
        "line_linked": {
            "enable": true,
            "distance": 150,
            "color": "#ffffff",
            "opacity": 0.4,
            "width": 1
        },
        "move": {
            "enable": true,
            "speed": 3,
            "direction": "none",
            "random": false,
            "straight": false,
            "out_mode": "out"
        }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": { "enable": true, "mode": "repulse" },
            "onclick": { "enable": true, "mode": "push" }
        },
        "modes": {
            "repulse": { "distance": 100, "duration": 0.4 },
            "push": { "particles_nb": 4 }
        }
    },
    "retina_detect": true
});


        // new with history
        let conversationHistory = [];
        let chat_session = null;

        function generateGemini() {
            const prompt = document.getElementById("prompt").value;
            const responseDiv = document.getElementById("response");
            const loadingMessage = document.getElementById("loading-message");

            if (!prompt.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'No Question Added!',
                text: 'Ask me a question first!'
            });
            return;
            }

            // Jika belum ada session, buat session baru dengan timestamp unik (NEW)
            if (!chat_session) {
            chat_session = "session_" + new Date().getTime();
            }
            console.log(chat_session);
            // Jika belum ada session, buat session baru dengan timestamp unik (NEW)

            const loadingModal = Swal.fire({
            title: 'Processings...',
            text: 'Hold Up...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false, // Agar tidak bisa menutup modal dengan mengklik luar
            showConfirmButton: false, // Menyembunyikan tombol konfirmasi
            didClose: () => {} // Tidak ada tindakan tambahan ketika modal ditutup
            });

            responseDiv.innerHTML = '';
            responseDiv.classList.remove("error-message");

            // Gabungkan riwayat percakapan sebelumnya dengan prompt baru
            let combinedPrompt = "";
            for (let i = 0; i < conversationHistory.length; i++) {
            combinedPrompt += "Q: " + conversationHistory[i].userQuestion + "\n" +
            "A: " + conversationHistory[i].aiResponse + "\n";
            }
            combinedPrompt += "Q: " + prompt;
            console.log(combinedPrompt);

            axios.post('/generate-gemini', { prompt: combinedPrompt })
            .then(response => {
                const data = response.data;
                const reply = data.candidates?.[0]?.content?.parts?.[0]?.text || "Terjadi kesalahan dalam memproses respon.";
                const formattedReply = convertMarkdownToHtml(reply);

                responseDiv.classList.remove("loading");
                responseDiv.innerHTML = formattedReply;

                // Simpan percakapan baru dalam riwayat
                conversationHistory.push({ userQuestion: prompt, aiResponse: reply });

                loadingModal.close();

                // Simpan ke database
                axios.post('/save-prompt', {
                user_question: prompt,
                ai_response: reply,
                chat_session: chat_session
                }).then(res => console.log("Prompt saved:", res.data))
                .catch(err => console.error("Error saving prompt:", err));
            })
            .catch(error => {
                loadingModal.close();

                responseDiv.classList.add("error-message");
                responseDiv.innerHTML = "Failed to get response from Gemini AI. Try Again Later !";

                Swal.fire({
                icon: 'error',
                title: 'Problem Occured',
                text: 'Failed to get response from Gemini AI. Try Again Later !'
                });
            });
        }

        function resetChat() {
            const responseDiv = document.getElementById("response");
            responseDiv.classList.remove("error-message");
            responseDiv.innerHTML = "Belum ada respon.";

            const inputField = document.getElementById("prompt");
            inputField.value = '';

            // Reset riwayat percakapan
            conversationHistory = [];
            chat_session = null; // Reset chat session
        }

        function saveChat(){
            console.log('saving chat to db');
        }

        function convertMarkdownToHtml(text) {
            return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')  // **bold**
            .replace(/\*(.*?)\*/g, '<em>$1</em>')  // *italic*
            .replace(/\n/g, '<br>');  // Convert line breaks to <br> for proper formatting
        }
    </script>
</body>
</html>

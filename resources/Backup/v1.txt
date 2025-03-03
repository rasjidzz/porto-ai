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
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-5">Ask me anything !</h2>
        <div class="card shadow p-4">
            <div class="mb-3">
                <label for="prompt" class="form-label">Ask me question :</label>
                <input type="text" id="prompt" class="form-control" placeholder="Ask AI something..." />
            </div>
            <button class="btn btn-primary w-100" onclick="generateGemini()">Send</button>
            <div class="mt-4">
                <h5>Results (Gemini AI API):</h5>
                <div id="response" class="response-box">
                    <div class="loading" id="loading-message">No Respond Yet.</div>
                </div>
            </div>
            <button class="btn btn-secondary w-100 reset-button" onclick="resetChat()">Clear</button>
            <button class="btn btn-secondary w-100 save-button" onclick="saveChat()">Save Response</button>
        </div>
    </div>

    <script>
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

                    document.getElementById("prompt").value = '';

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


        // old without history
        // function generateGemini() {
        //     const prompt = document.getElementById("prompt").value;
        //     const responseDiv = document.getElementById("response");
        //     const loadingMessage = document.getElementById("loading-message");

        //     if (!prompt.trim()) {
        //         Swal.fire({
        //             icon: 'warning',
        //             title: 'No Question Added!',
        //             text: 'Ask me a question first!'
        //         });
        //         return;
        //     }
        //     const loadingModal = Swal.fire({
        //         title: 'Processings...',
        //         text: 'Hold Up...',
        //         didOpen: () => {
        //             Swal.showLoading();
        //         },
        //         allowOutsideClick: false, // Agar tidak bisa menutup modal dengan mengklik luar
        //         showConfirmButton: false, // Menyembunyikan tombol konfirmasi
        //         didClose: () => {} // Tidak ada tindakan tambahan ketika modal ditutup
        //     });

        //     responseDiv.innerHTML = '';
        //     responseDiv.classList.remove("error-message");

        //     axios.post('/generate-gemini', { prompt })
        //         .then(response => {
        //             const data = response.data;
        //             const reply = data.candidates?.[0]?.content?.parts?.[0]?.text || "Terjadi kesalahan dalam memproses respon.";
        //             const formattedReply = convertMarkdownToHtml(reply);

        //             responseDiv.classList.remove("loading");
        //             responseDiv.innerHTML = formattedReply;

        //             loadingModal.close();

        //             document.getElementById("prompt").value = '';
        //         })
        //         .catch(error => {
        //             loadingModal.close();

        //             responseDiv.classList.add("error-message");
        //             responseDiv.innerHTML = "Failed to get response from Gemini AI. Try Again Later !";

        //             Swal.fire({
        //                 icon: 'error',
        //                 title: 'Problem Occured',
        //                 text: 'Failed to get response from Gemini AI. Try Again Later !'
        //             });
        //         });
        // }


        // function resetChat() {
        //     const responseDiv = document.getElementById("response");
        //     responseDiv.classList.remove("error-message");
        //     responseDiv.innerHTML = "Belum ada respon.";

        //     const inputField = document.getElementById("prompt");
        //     inputField.value = '';
        // }

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
